function checkDuplicate(el, field, value, table){
    let ajaxPath = "https://wiki.verplant-durch-aventurien.de/assets/ajax/ajax.php";
    $.post(ajaxPath,
        {
            'errorType': 'duplicate',
            'field': field,
            'value': value,
            'table': table
        },
        function(data){
        let result = JSON.parse(data);
        if(result.duplicate === true){
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
            let max = $(this).text()
            $(this).text( i + "/" + max)
            el.keyup(function (){
                lengthSpan.text(el.val().length + "/" + max);
            });
        }
    });
}

export {checkDuplicate, checkMinLength, showMaxLength};