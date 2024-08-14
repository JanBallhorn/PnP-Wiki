import {checkDuplicate} from "./formCheck.js";
$(function () {
    let ajaxPath = "https://wiki.verplant-durch-aventurien.de/assets/ajax/ajax.php";
    let elName = $("input[name='name']");
    elName.blur(function (){
        checkDuplicate($(this), 'name', $(this).val(), 'projects')
    });
    checkDuplicate(elName, 'name', elName.val(), 'projects')
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        let i = input.val().length
        $(this).siblings().each(function (){
            if($(this).hasClass('maxLength')){
                let lengthSpan = $(this);
                let max = $(this).text()
                $(this).text( i + "/" + max)
                input.keyup(function (){
                    lengthSpan.text(input.val().length + "/" + max);
                });
            }
        });
    });
});