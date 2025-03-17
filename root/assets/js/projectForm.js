import {checkDuplicate, showMaxLength, searchSelect} from "./formCheck.js";
$(function () {
    searchSelect();
    let elName = $("input[name='name']");
    let edit = false;
    let origName = $("h1").text().split(' ')[0];
    let path = $(location).attr('pathname');
    let pathArray = path.slice(1).split("/");
    if(pathArray[1] === 'edit'){
        edit = true;
    }
    elName.blur(function (){
        if(!edit){
            checkDuplicate($(this), 'name', $(this).val(), 'projects');
        }
        else{
            checkDuplicate($(this), 'name', $(this).val(), 'projects', origName);
        }
        });
    if(!edit){
        checkDuplicate($(this), 'name', $(this).val(), 'projects');
    }
    else{
        checkDuplicate($(this), 'name', $(this).val(), 'projects', origName);
    }
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
});