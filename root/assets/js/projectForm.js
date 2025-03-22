import {checkDuplicate, showMaxLength, searchSelect, getProjectAuthorized} from "./formCheck.js";
$(function () {
    searchSelect();
    getProjectAuthorized($("input[name='parentProject']"));
    let elName = $("input[name='name']");
    let elPrivate = $("input[name='private']");
    let elAutorize = $("fieldset.authorize");
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
        if(elPrivate.prop("checked")){
            elAutorize.toggleClass('hide');
        }
    }
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
    elPrivate.on("change", function (){
        elAutorize.toggleClass('hide');
    });
});