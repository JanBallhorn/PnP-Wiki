import {checkDuplicate, showMaxLength, checkFileType, checkFileSize} from "./formCheck.js";
$(function (){
    let elName = $("input[name='name']");
    let elFile = $("input[name='fileUpload']");
    elName.blur(function (){
        checkDuplicate(elName, 'name', elName.val(), 'categories');
    })
    checkDuplicate(elName, 'name', elName.val(), 'categories');
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
    elFile.change(function (){
        $(".selected-file").text(this.files[0]['name']);
        checkFileType($(this), 'image/svg+xml');
        checkFileSize($(this), 20000);
    })
});