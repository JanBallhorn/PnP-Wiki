// Link handling for the fields that store a small amount of HTML - the
// Steckbrief rows and the image captions. Those fields keep their <a> markup
// in a plain input/textarea, so the raw tag used to sit visible in the field.
//
// Fields marked with .withModal can be upgraded to a "rich" field: the
// original input/textarea stays in the DOM (hidden, same name, so nothing on
// the PHP side changes) and a contenteditable mirror next to it shows the
// content with the links RENDERED. Everything the user types or inserts is
// serialised back into the hidden field.
//
// Fields that are not upgraded keep the old behaviour (the modal splices the
// <a> tag into the value as text) - the paragraph editor's caption fields
// rely on it, because its move logic re-creates their markup by hand.

const ajaxPath = "../../src/Ajax.php";

// The only tags that survive a round trip through the mirror. <a> is what the
// modal inserts; the inline formatting tags are listed so markup that predates
// this editor is not silently stripped the next time an old row is saved.
const keepTags = {
    a: ['href', 'target', 'rel', 'title', 'class'],
    b: [], strong: [], i: [], em: [], u: [], s: [], strike: [], sub: [], sup: [],
    span: ['class']
};
// Wrappers browsers create while editing (Enter inserts a <div> in Chrome).
// They are dropped, but they do mark a line break.
const blockTags = ['div', 'p', 'li', 'tr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote'];

let modalBound = false;
// what the currently open modal is editing
let field = null;        // the mirror element, or null for a plain field
let plainInput = null;   // the input/textarea for a non-upgraded field
let plainSel = null;     // {start, end} inside that plain field
let savedRange = null;   // caret/selection inside the mirror
let editingLink = null;  // the <a> being edited, if the modal was opened on one
let chosenTitle = '';    // headline of an article picked from the search

/**
 * Upgrades every .withModal field inside the given scope to a rich field and
 * makes sure the link modal is wired up. Safe to call repeatedly (the forms
 * poll on an interval) and safe for markup that arrives later via Ajax.
 */
function initLinks(scope){
    bindModal();
    $(scope).find(".withModal, .richSource").each(function (){
        let source = $(this);
        if(source.siblings(".richField").length !== 0){
            return; // already mirrored - or a clone that brought its mirror along
        }
        let multiline = this.tagName.toLowerCase() === 'textarea';
        let mirror = $('<div class="richField" contenteditable="true"></div>');
        mirror.attr("data-multiline", multiline ? "1" : "0");
        if(source.attr("maxlength")){
            mirror.attr("data-max", source.attr("maxlength"));
        }
        mirror.html(source.val());
        // .withModal is dropped so the modal can tell the two kinds apart and
        // so this loop does not process the field a second time
        source.removeClass("withModal").addClass("richSource").before(mirror);
        writeCounter(mirror[0]);
    });
}

/**
 * Current value of a field, whether it is upgraded or not. Use this instead of
 * .val()/.text() - a mirrored field's hidden source is only up to date after a
 * sync, and .text() on a textarea never reflects what was typed at all.
 */
function readField(source){
    let mirror = $(source).siblings(".richField");
    if(mirror.length !== 0){
        return serialize(mirror[0]);
    }
    return $(source).val();
}

/**
 * Writes a value into a field, updating the mirror and the DOM so that
 * markup-copying moves (.html()) carry the value along.
 */
function writeField(source, value){
    let el = $(source)[0];
    $(source).val(value);
    if(el.tagName.toLowerCase() === 'textarea'){
        el.textContent = value;
    }
    else{
        el.setAttribute("value", value);
    }
    let mirror = $(source).siblings(".richField");
    if(mirror.length !== 0){
        mirror.html(value);
        writeCounter(mirror[0]);
    }
}

/**
 * Pushes every mirror inside the scope into its hidden field AND into the DOM.
 * Call this before moving elements around by copying their markup: a copy only
 * carries what is in the DOM, never the live value of a field that was typed in.
 */
function commitFields(scope){
    $(scope).find(".richField").each(function (){
        let source = $(this).siblings(".richSource");
        if(source.length !== 0){
            let value = serialize(this);
            let el = source[0];
            $(source).val(value);
            if(el.tagName.toLowerCase() === 'textarea'){
                el.textContent = value;
            }
            else{
                el.setAttribute("value", value);
            }
            writeCounter(this);
        }
    });
}

// -------------------------------------------------------------- serialisation

function serialize(node){
    let out = '';
    node.childNodes.forEach(function (child){
        if(child.nodeType === 3){
            out += escapeText(child.nodeValue);
            return;
        }
        if(child.nodeType !== 1){
            return;
        }
        let tag = child.tagName.toLowerCase();
        if(tag === 'br'){
            out += "\n";
            return;
        }
        let inner = serialize(child);
        let allowed = keepTags[tag];
        // a <span> without a class carries nothing worth keeping
        if(allowed === undefined || (tag === 'span' && !child.getAttribute("class"))){
            if(blockTags.includes(tag) && out !== '' && !out.endsWith("\n")){
                out += "\n";
            }
            out += inner;
            return;
        }
        out += '<' + tag + attributes(child, allowed) + '>' + inner + '</' + tag + '>';
    });
    return out;
}

function attributes(el, allowed){
    let out = '';
    for(const name of allowed){
        let value = el.getAttribute(name);
        if(value !== null && value !== ''){
            out += ' ' + name + '="' + escapeText(value).replace(/"/g, '&quot;') + '"';
        }
    }
    return out;
}

function escapeText(text){
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ------------------------------------------------------------- editing a field

function syncField(mirror){
    let source = $(mirror).siblings(".richSource");
    if(source.length !== 0){
        source.val(serialize(mirror));
    }
    writeCounter(mirror);
}

// The stored columns are varchar(300)/varchar(1000) and the markup counts
// towards that - a single internal link easily eats 70 characters. maxlength
// does not apply to a contenteditable, so the limit is enforced here; without
// it MySQL would silently cut the value off.
function writeCounter(mirror){
    let counter = $(mirror).siblings("span.maxLength");
    if(counter.length === 0){
        return;
    }
    let max = Number(mirror.dataset.max || 0);
    let length = serialize(mirror).length;
    counter.text(length + "/" + max);
    counter.toggleClass("limitHit", max !== 0 && length >= max);
}

function room(mirror){
    let max = Number(mirror.dataset.max || 0);
    if(max === 0){
        return Number.MAX_SAFE_INTEGER;
    }
    let selection = window.getSelection();
    let replaced = 0;
    if(selection !== null && selection.rangeCount !== 0 && !selection.isCollapsed
        && mirror.contains(selection.getRangeAt(0).commonAncestorContainer)){
        replaced = String(selection).length;
    }
    return max - (serialize(mirror).length - replaced);
}

function flashLimit(mirror){
    let counter = $(mirror).siblings("span.maxLength");
    counter.addClass("limitHit");
    setTimeout(function (){
        writeCounter(mirror);
    }, 800);
}

function bindFields(){
    // Delegated on purpose: the forms move rows around by copying markup, and a
    // copy would lose handlers bound to the original element.
    $(document).on("input", ".richField", function (){
        syncField(this);
    });
    $(document).on("beforeinput", ".richField", function (event){
        let original = event.originalEvent;
        let type = original.inputType || '';
        if(type.startsWith("delete") || type.startsWith("history")){
            return;
        }
        let added = 0;
        if(original.data){
            added = original.data.length;
        }
        else if(type === 'insertParagraph' || type === 'insertLineBreak'){
            added = 1;
        }
        if(added > room(this)){
            event.preventDefault();
            flashLimit(this);
        }
    });
    // Paste as plain text: the field is meant to hold text plus links, not
    // whatever markup came off the clipboard.
    $(document).on("paste", ".richField", function (event){
        event.preventDefault();
        let clipboard = event.originalEvent.clipboardData || window.clipboardData;
        let text = clipboard ? clipboard.getData("text") : '';
        if(text === ''){
            return;
        }
        if(this.dataset.multiline === '1'){
            text = text.replace(/\r\n?/g, "\n");
        }
        else{
            text = text.replace(/[\r\n]+/g, ' ');
        }
        let free = room(this);
        if(free <= 0){
            flashLimit(this);
            return;
        }
        if(text.length > free){
            text = text.substring(0, free);
        }
        // execCommand keeps the browser's own undo stack intact
        document.execCommand("insertText", false, text);
    });
    $(document).on("keydown", ".richField", function (event){
        if(event.key === 'Enter' && this.dataset.multiline !== '1'){
            event.preventDefault();
        }
    });
    // Clicking a link inside the field edits it instead of following it.
    $(document).on("click", ".richField a", function (event){
        event.preventDefault();
        let mirror = $(this).closest(".richField")[0];
        openModal(mirror, this);
    });
}

// --------------------------------------------------------------------- modal

function bindModal(){
    if(modalBound){
        return;
    }
    modalBound = true;
    bindFields();
    let modal = $(".linkModal");

    $(document).on("click", ".openModal", function (){
        let mirror = $(this).siblings(".richField");
        if(mirror.length !== 0){
            openModal(mirror[0], null);
            return;
        }
        openModal(null, null, $(this).siblings(".withModal"));
    });
    modal.find("button[name='save']").on("click", saveModal);
    modal.find("button[name='remove']").on("click", function (){
        if(editingLink !== null && field !== null){
            let text = document.createTextNode(editingLink.textContent);
            editingLink.parentNode.replaceChild(text, editingLink);
            syncField(field);
        }
        closeModal();
    });
    modal.find(".closeModal, button[name='cancel']").on("click", closeModal);
    $(window).on("click", function (event){
        if(event.target === modal[0]){
            closeModal();
        }
    });
    // Wiki-article search: same endpoint the search bar and the paragraph
    // editor's article-link dialog use, so a link gets the article's id and
    // its headline as title (hovering it reveals the target).
    let timer = null;
    modal.find("input[name='articleSearch']").on("input", function (){
        let query = $(this).val().trim();
        clearTimeout(timer);
        if(query.length < 2){
            modal.find(".articleResults").html('');
            return;
        }
        timer = setTimeout(function (){
            $.post(ajaxPath, {'type': 'suggest', 'query': query}, function (data){
                let results;
                try{
                    results = typeof data === 'string' ? JSON.parse(data) : data;
                }
                catch(e){
                    results = [];
                }
                showResults(modal, results || []);
            });
        }, 250);
    });
    modal.find(".articleResults").on("click", "button", function (){
        modal.find(".articleResults button").removeClass("chosen");
        $(this).addClass("chosen");
        chosenTitle = $(this).attr("data-headline");
        modal.find("input[name='link']").val("/article?id=" + $(this).attr("data-id"));
        if(modal.find("input[name='text']").val().trim() === ''){
            modal.find("input[name='text']").val(chosenTitle);
        }
    });
    // A hand-typed URL is not an article link any more
    modal.find("input[name='link']").on("input", function (){
        chosenTitle = '';
        modal.find(".articleResults button").removeClass("chosen");
    });
}

function showResults(modal, results){
    let container = modal.find(".articleResults");
    container.html('');
    if(results.length === 0){
        container.append($('<p class="noResults">Kein Artikel gefunden.</p>'));
        return;
    }
    for(const result of results){
        let label = result.alias ? (result.alias + ' → ' + result.headline) : result.headline;
        let button = $('<button type="button"></button>');
        button.attr("data-id", result.id);
        button.attr("data-headline", result.headline);
        button.text(label);
        container.append(button);
    }
}

function openModal(mirror, link, plain){
    let modal = $(".linkModal");
    field = mirror;
    plainInput = null;
    plainSel = null;
    savedRange = null;
    editingLink = link || null;
    chosenTitle = '';
    let text = '';
    if(mirror !== null){
        if(editingLink !== null){
            modal.find("input[name='link']").val(editingLink.getAttribute("href") || '');
            modal.find("input[name='target']").prop("checked", editingLink.getAttribute("target") === '_blank');
            text = editingLink.textContent;
        }
        else{
            // The caret is lost as soon as the modal's inputs take focus, so
            // remember where the link has to go.
            let selection = window.getSelection();
            if(selection !== null && selection.rangeCount !== 0
                && mirror.contains(selection.getRangeAt(0).commonAncestorContainer)){
                savedRange = selection.getRangeAt(0).cloneRange();
                text = String(selection);
            }
        }
    }
    else{
        plainInput = plain;
        plainSel = {start: plain[0].selectionStart, end: plain[0].selectionEnd};
        text = String(plain.val()).substring(plainSel.start, plainSel.end);
    }
    modal.find("input[name='text']").val(text);
    modal.find("button[name='remove']").toggleClass("hide", editingLink === null);
    modal.find("h3").text(editingLink === null ? 'Link einfügen' : 'Link bearbeiten');
    modal.css({"display": "block"});
}

function saveModal(){
    let modal = $(".linkModal");
    let href = modal.find("input[name='link']").val().trim();
    let text = modal.find("input[name='text']").val().trim();
    let blank = modal.find("input[name='target']").prop("checked");
    modal.find(".error.noLink").addClass("hide");
    modal.find(".error.tooLong").addClass("hide");
    if(href === ''){
        modal.find(".error.noLink").removeClass("hide");
        return;
    }
    if(text === ''){
        text = href;
    }
    if(field === null){
        insertIntoPlainInput(href, text, blank);
        closeModal();
        return;
    }
    let link = editingLink !== null ? editingLink : document.createElement("a");
    link.setAttribute("href", href);
    if(blank){
        link.setAttribute("target", "_blank");
        link.setAttribute("rel", "noopener");
    }
    else{
        link.removeAttribute("target");
        link.removeAttribute("rel");
    }
    if(chosenTitle !== ''){
        link.setAttribute("title", chosenTitle);
    }
    link.textContent = text;
    if(editingLink === null){
        // + 2 for the spaces the link is padded with when nothing is selected
        if(link.outerHTML.length + 2 > room(field)){
            modal.find(".error.tooLong").removeClass("hide");
            return;
        }
        if(savedRange !== null){
            let selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(savedRange);
            savedRange.deleteContents();
            savedRange.insertNode(link);
        }
        else{
            field.appendChild(link);
        }
        // keep the link from gluing itself to the neighbouring word
        if(link.previousSibling === null || !/[\s(]$/.test(link.previousSibling.textContent || '')){
            link.parentNode.insertBefore(document.createTextNode(' '), link);
        }
        if(link.nextSibling === null || !/^[\s.,;:!?)]/.test(link.nextSibling.textContent || '')){
            link.parentNode.insertBefore(document.createTextNode(' '), link.nextSibling);
        }
    }
    else if(serialize(field).length > Number(field.dataset.max || 0) && field.dataset.max){
        modal.find(".error.tooLong").removeClass("hide");
        return;
    }
    syncField(field);
    closeModal();
}

// Old behaviour for the fields that were not upgraded: the tag goes into the
// value as text.
function insertIntoPlainInput(href, text, blank){
    let value = String(plainInput.val());
    let tag = blank
        ? '<a href="' + href + '" target="_blank" rel="noopener">' + text + '</a>'
        : '<a href="' + href + '">' + text + '</a>';
    if(plainSel.start !== plainSel.end){
        value = value.substring(0, plainSel.start) + tag + value.substring(plainSel.end);
    }
    else{
        value = (value.substring(0, plainSel.start).trimEnd() + ' ' + tag + ' '
            + value.substring(plainSel.start).trimStart()).trim();
    }
    plainInput.val(value);
}

function closeModal(){
    let modal = $(".linkModal");
    modal.css({"display": "none"});
    modal.find("input").each(function (){
        $(this).val('');
        $(this).prop("checked", false);
    });
    modal.find(".articleResults").html('');
    modal.find(".error").addClass("hide");
    field = null;
    plainInput = null;
    plainSel = null;
    savedRange = null;
    editingLink = null;
    chosenTitle = '';
}

export {initLinks, readField, writeField, commitFields};
