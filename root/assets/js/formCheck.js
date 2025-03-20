function checkDuplicate(el, field, value, table, orig = ''){
    let ajaxPath = "../../src/Ajax.php";
    $.post(ajaxPath,
        {
            'errorType': 'duplicate',
            'field': field,
            'value': value,
            'table': table
        },
        function(data){
        let result = JSON.parse(data);
        if(result.duplicate === true && value !== orig){
            el.parent().find(".error.exists").removeClass("hide");
        }
        else {
            el.parent().find(".error.exists").addClass("hide");
        }
    });
}

function checkMinLength(el, string, minLength){
    if(string.length >= minLength){
        el.parent().find(".error.length").addClass("hide");
    }
    else{
        el.parent().find(".error.length").removeClass("hide");
    }
}

function showMaxLength(el){
    let i = el.val().length
    el.siblings().each(function (){
        if($(this).hasClass('maxLength')){
            let lengthSpan = $(this);
            let max = $(el).attr('maxlength');
            $(this).text( i + "/" + max);
            el.off("keyup");
            el.keyup(function (){
                lengthSpan.text(el.val().length + "/" + max);
            });
        }
    });
}
function checkFileType(el, allowedFileTypes){
    let error = false;
    let imgName = "";
    for (const i of el[0].files){
        if(!allowedFileTypes.includes(i['type'])){
            error = true;
            imgName = i['name'];
            break;
        }
    }
    if(!error){
        el.parent().find(".error.fileType").addClass("hide");
    }
    else{
        el.parent().find(".error.fileType").text(el.parent().find(".error.fileType").text() + " (" + imgName + ")");
        el.parent().find(".error.fileType").removeClass("hide");
    }
}
function checkFileSize(el, allowedFileSize){
    let error = false;
    let imgName = [];
    for (const i of el[0].files){
        if(i['size'] > allowedFileSize){
            error = true;
            imgName.push(i['name']);
        }
    }
    if(!error){
        el.parent().find(".error.fileSize").addClass("hide");
    }
    else{
        el.parent().find(".error.fileSize").text(el.parent().find(".error.fileSize").text() + " (" + imgName.toString() + ")");
        el.parent().find(".error.fileSize").removeClass("hide");
    }
}
function checkCheckboxCollectionChecked(el){
    let checked = false;
    let inputs = el.children().children();
    inputs.each(function (){
        if($(this).is(":checked")){
            checked = true;
            return false;
        }
    })
    if (checked){
        el.find(".error.notChecked").addClass("hide");
    }
    else{
        el.find(".error.notChecked").removeClass("hide");
    }
}

function getTemplate(el, template, data = [], append = false){
    let ajaxPath = "../../src/Ajax.php";
    $.post(ajaxPath,
        {
            'type': 'render',
            'template': template,
            'data': data
        },
        function(data) {
            let result = JSON.parse(data);
            let finalResult = result.render;
            if(append === true){
                el.append($(finalResult));
            }
            else{
                $(finalResult).insertBefore(el);
            }
        }
    );
}

function searchSelect(){
    let searchSelect = $(".searchSelect");
    let options = $(".searchSelect + datalist option");
    searchSelect.off("click");
    searchSelect.off("keyup")
    searchSelect.off("blur");
    options.off("click");
    searchSelect.on("click", function (){
        if($(this).siblings("datalist").is(":visible")){
            $(this).siblings("i").css("transform", "translateY(-50%) rotate(180deg)");
        }
        else{
            $(this).siblings("i").css("transform", "translateY(-50%) rotate(0)");
        }
    });
    searchSelect.on("keyup", function (){
        let input = $(this).val();
        $(this).siblings("datalist").children("option").each(function (){
            if(!$(this).val().toLowerCase().includes(input.toLowerCase())){
                $(this).addClass("hide");
            }
            else {
                $(this).removeClass("hide");
            }
        });
    });
    searchSelect.on("blur", function (){
        let exists = false;
        let input = $(this).val();
        let datalist = $(this).siblings("datalist");
        datalist.children("option").each(function (){
            if($(this).val().toLowerCase() === input.toLowerCase()){
                exists = true;
                input = $(this).val();
            }
        });
        if(exists === false){
            $(this).siblings(".error.notfound").removeClass("hide");
        }
        else{
            $(this).siblings(".error.notfound").addClass("hide");
        }
        if(datalist.is(":hidden")){
            $(this).siblings("i").css("transform", "translateY(-50%) rotate(0)");
        }
    });
    options.on("click", function (){
        if($(this).parent().is(":visible")){
            $(this).parent().siblings("i").css("transform", "translateY(-50%) rotate(180deg)");
        }
        $(this).parent().siblings(".searchSelect").val($(this).val());
    });
}

function linkModal(){
    let modal = $(".linkModal");
    let modalBtn = $(".openModal");
    let closeModal = $(".closeModal");
    let cancelModal = $(".linkModal button[name='cancel']");
    let saveModal = $(".linkModal button[name='save']");
    let marked = "";
    modalBtn.off("click");
    closeModal.off("click");
    $(window).off("click");
    cancelModal.off("click");
    modalBtn.on("click", function (){
        let input = $(this).siblings(".withModal");
        let text = input.val();
        let selStart = input[0].selectionStart;
        let selEnd = input[0].selectionEnd;
        modal.css({"display": "block"});
        marked = input.val().substring(selStart, selEnd);
        modal.find("input[name='text']").val(marked);
        saveModal.off("click");
        saveModal.on("click", function (){
            let link = $(".linkModal input[name='link']").val();
            let linkText = $(".linkModal input[name='text']").val().trim();
            let targetBlank = false;
            if($(".linkModal input[name='target']").prop("checked")){
                targetBlank = true;
            }
            let aTag;
            if(targetBlank){
                aTag = "<a href='" + link + "' target='_blank'>" + linkText + "</a>";
            }
            else{
                aTag = "<a href='" + link + "'>" + linkText + "</a>";
            }
            if(marked !== ""){
                text = text.replace(marked, aTag);
            }
            else if(selStart === selEnd){
                let substr1 = text.substring(0, selStart).trimEnd();
                let substr2 = text.substring(selStart, text.length - 1).trimStart();
                text = substr1 + " " + aTag + " " + substr2;
            }
            input.val(text);
            modal.css({"display": "none"});
            emptyModal(modal);
        });
    });
    closeModal.on("click", function (){
        modal.css({"display": "none"});
        emptyModal(modal);
    });
    $(window).on("click", function (event){
        if(event.target === modal[0]){
            modal.css({"display": "none"});
            emptyModal(modal);
        }
    });
    cancelModal.on("click", function (){
        modal.css({"display": "none"});
        emptyModal(modal);
    });
}

function emptyModal(modal){
    let inputs = modal.find("input");
    inputs.each(function (){
        $(this).val("");
        $(this).prop("checked", false);
    })
}

export {checkDuplicate, checkMinLength, showMaxLength, checkFileType, checkFileSize, checkCheckboxCollectionChecked, getTemplate, searchSelect, linkModal};