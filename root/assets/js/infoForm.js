import {checkFileSize, checkFileType, showMaxLength, getTemplate} from "./formCheck.js";
import {initLinks, readField, writeField, commitFields} from "./linkField.js";

const images = [];

$(function (){
    getImages();
    newTable();
    newRow();
    ImgUpload();
    // Safety net: every edit syncs its field on the spot, this catches anything
    // that changed the field without an input event firing.
    $("#editInfo").on("submit", function (){
        commitFields(this);
    });
    // right away, so the fields never show their raw markup
    initLinks("#editInfo");
    setInterval(function (){
        controlButtons();
        // Scoped: the fields of this form show their links rendered instead of
        // the raw <a> markup (see linkField.js).
        initLinks("#editInfo");
        // .richSource fields are hidden and count their own characters
        $("main form input:not(.richSource), main form textarea:not(.richSource)").each(function (){
            let input = $(this);
            showMaxLength(input);
        });
        parseImages();
    }, 500);
});

function getImages(){
    $(".contentImage").each(function (){
        let data = $(this).find("img").attr("src");
        images.push(data);
    });
}

function parseImages(){
    $("input[name='images']").val(JSON.stringify(images));
}

function ImgUpload(){
    $(".galleryUpload").on("change", function (){
        checkFileType($(this), "image/gif image/jpeg image/png image/svg+xml image/webp");
        checkFileSize($(this), 2000000);
        let uploadEl = $(".uploads");
        for(const i of $(this)[0].files){
            let galleryReader = new FileReader();
            galleryReader.readAsDataURL(i);
            galleryReader.onload = function (){
                setTimeout(function (){
                    images.push(galleryReader.result);
                    getTemplate(uploadEl, 'newImg.twig', [galleryReader.result, '', '', i['name']], true);
                }, 200);
            }
        }
    });
}

function newTable() {
    $(".newTable").on("click", function () {
        let tableEl = $(".tables");
        let pos = tableEl.children().length + 1;
        getTemplate(tableEl, "newTable.twig", [pos], true);
        $(".newRow").off("click");
        setTimeout(function () {
            newRow();
        }, 100);
    });
}

function newRow(){
    $(".newRow").on("click", function (){
        let rowEl = $(this).parent().siblings(".rows");
        let tablePos = $(this).closest(".infoTable").attr("data-position");
        let amount = $(this).closest(".infoTable").find("input[type='number']").val();
        for (let i = 0; i < amount; i++){
            getTemplate(rowEl, "newRow.twig", [tablePos], true);
        }
    });
}

function controlButtons(){
    let minimizeEl = $(".minimize");
    let deleteEl = $(".delete");
    let mUpEl = $(".moveUp");
    let mDownEl = $(".moveDown");
    minimizeEl.off("click");
    deleteEl.off("click");
    mUpEl.off("click");
    mDownEl.off("click");
    minimizeEl.on("click", function (){
        $(this).toggleClass("minimized");
    });
    deleteEl.on("click", function (){
        let elToDel = $(this).parent().parent();
        if(elToDel.hasClass("infoTableRow")){
            elToDel.remove();
        }
        else if(elToDel.hasClass("infoTable")){
            let alert = confirm('Bist du sicher, dass du dieses Element löschen möchtest?\r\nDann klicke auf OK!');
            if(alert){
                let following = elToDel.nextAll(".infoTable");
                elToDel.remove();
                renumberTables(following);
            }
        }
        else if(elToDel.hasClass("contentImage")){
            let curPos = elToDel.prevAll().length;
            images.splice(curPos, 1);
            elToDel.remove();
        }
    });
    mUpEl.on("click", function (){
        let elToMove = $(this).parent().parent();
        if(elToMove.hasClass("infoTable")){
            let prevEl = elToMove.prev(".infoTable");
            if(prevEl.length !== 0){
                let curPos = elToMove.prevAll().length + 1;
                swapTables(prevEl, elToMove, curPos - 1, curPos);
            }
        }
        else if(elToMove.hasClass("contentImage")){
            let curPos = elToMove.prevAll().length;
            if(curPos !== 0){
                swapImages(elToMove.prev(), elToMove, curPos - 1, curPos);
            }
        }
        else if(elToMove.hasClass("infoTableRow")){
            let prevEl = elToMove.prev(".infoTableRow");
            if(prevEl.length !== 0){
                swapFields(prevEl, elToMove);
            }
        }
    });
    mDownEl.on("click", function (){
        let elToMove = $(this).parent().parent();
        if(elToMove.hasClass("infoTable")){
            let nextEl = elToMove.next(".infoTable");
            if(nextEl.length !== 0){
                let curPos = elToMove.prevAll().length + 1;
                swapTables(elToMove, nextEl, curPos, curPos + 1);
            }
        }
        else if(elToMove.hasClass("contentImage")){
            let nextEl = elToMove.next(".contentImage");
            if(nextEl.length !== 0){
                let curPos = elToMove.prevAll().length;
                swapImages(elToMove, nextEl, curPos, curPos + 1);
            }
        }
        else if(elToMove.hasClass("infoTableRow")){
            let nextEl = elToMove.next(".infoTableRow");
            if(nextEl.length !== 0){
                swapFields(elToMove, nextEl);
            }
        }
    });
}

/**
 * Swaps the contents of two neighbouring table sections. The sections
 * themselves stay put, so their position - and with it the rowTopicN/rowInfoN
 * names their rows have to carry - never changes.
 */
function swapTables(first, second, firstPos, secondPos){
    // The rows are moved by copying their markup, and a copy only carries what
    // is in the DOM - never the live value of a field that was typed into.
    commitFields(first);
    commitFields(second);
    let firstHeadline = first.find("input[name='tableHeadline[]']").val();
    first.find("input[name='tableHeadline[]']").val(second.find("input[name='tableHeadline[]']").val());
    second.find("input[name='tableHeadline[]']").val(firstHeadline);
    let firstRows = first.find(".rows").html();
    first.find(".rows").html(second.find(".rows").html());
    second.find(".rows").html(firstRows);
    renameRowFields(first, firstPos);
    renameRowFields(second, secondPos);
}

function swapImages(first, second, firstPos, secondPos){
    let firstImg = images[firstPos];
    images[firstPos] = images[secondPos];
    images[secondPos] = firstImg;
    let firstSrc = first.find("img").attr("src");
    let firstAlt = first.find("img").attr("alt");
    first.find("img").attr("src", second.find("img").attr("src"));
    first.find("img").attr("alt", second.find("img").attr("alt"));
    second.find("img").attr("src", firstSrc);
    second.find("img").attr("alt", firstAlt);
    swapFields(first, second);
}

/**
 * Swaps the link-carrying fields (topic, info, caption) of two elements of the
 * same kind. Goes through readField/writeField because those fields keep their
 * value in a hidden input next to a contenteditable mirror - reading .val() or
 * .text() directly would miss the mirror, and .text() on a textarea never
 * reflects what was typed into it in the first place.
 */
function swapFields(first, second){
    let firstFields = first.find(".richSource, .withModal");
    let secondFields = second.find(".richSource, .withModal");
    firstFields.each(function (index){
        let counterpart = secondFields.eq(index);
        if(counterpart.length !== 0){
            let value = readField($(this));
            writeField($(this), readField(counterpart));
            writeField(counterpart, value);
        }
    });
}

/**
 * saveInfo() pairs the rows with their section by index (rowTopic1 belongs to
 * the first tableHeadline), so after a section is removed every following
 * section has to be renumbered - otherwise its rows land on the wrong section.
 */
function renumberTables(tables){
    tables.each(function (){
        let pos = $(this).prevAll(".infoTable").length + 1;
        $(this).attr("data-position", pos);
        renameRowFields($(this), pos);
    });
}

function renameRowFields(table, pos){
    table.find("[name^='rowTopic']").attr("name", "rowTopic" + pos + "[]");
    table.find("[name^='rowInfo']").attr("name", "rowInfo" + pos + "[]");
}
