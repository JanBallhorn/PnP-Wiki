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
            let max = $(el).attr('maxlength')
            $(this).text( i + "/" + max)
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

export {checkDuplicate, checkMinLength, showMaxLength, checkFileType, checkFileSize, checkCheckboxCollectionChecked};