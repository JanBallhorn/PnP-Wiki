import {checkDuplicate, showMaxLength} from "./formCheck.js";
$(function () {
    let elName = $("input[name='name']");
    elName.blur(function (){
        checkDuplicate($(this), 'name', $(this).val(), 'projects')
    });
    checkDuplicate(elName, 'name', elName.val(), 'projects')
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
});