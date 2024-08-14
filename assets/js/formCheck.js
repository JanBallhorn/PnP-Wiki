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

function checkLength(el, string, minLength){
    let ajaxPath = "https://wiki.verplant-durch-aventurien.de/assets/ajax/ajax.php";
    $.post(ajaxPath,
        {
            'errorType': 'length',
            'string': string,
            'minLength': minLength
        },
        function (data){
        let result = JSON.parse(data);
        if(result.length === false){
            el.parent().find(".error.length").removeClass("hide");
        }
        else {
            el.parent().find(".error.length").addClass("hide");
        }
        });
}

export {checkDuplicate, checkLength};