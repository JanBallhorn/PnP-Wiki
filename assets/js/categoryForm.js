import {checkDuplicate, showMaxLength, checkFileType, checkFileSize} from "./formCheck.js";
$(function (){
    let elName = $("input[name='name']");
    let elFile = $("input[name='fileUpload']");
    let edit = false;
    let origName = $("h1").text().split(' ')[0];
    let path = $(location).attr('pathname');
    let pathArray = path.slice(1).split("/");
    if(pathArray[1] === 'edit'){
        edit = true;
    }
    elName.blur(function (){
        if(!edit){
            checkDuplicate(elName, 'name', elName.val(), 'categories');
        }
        else{
            checkDuplicate(elName, 'name', elName.val(), 'categories', origName);
        }
    })
    if(!edit){
        checkDuplicate(elName, 'name', elName.val(), 'categories');
    }
    else{
        checkDuplicate(elName, 'name', elName.val(), 'categories', origName);
    }
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