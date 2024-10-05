import {checkDuplicate, showMaxLength, checkCheckboxCollectionChecked} from "./formCheck.js";
$(function (){
    let elHeadline = $("input[name='headline']");
    let elCheckbox = $("fieldset.checkbox.required");
    let edit = false;
    let origName = $("h1").text().split(' ')[0];
    let path = $(location).attr('pathname');
    let pathArray = path.slice(1).split("/");
    if(pathArray[1] === 'edit'){
        edit = true;
    }
    elHeadline.blur(function (){
        if(!edit){
            checkDuplicate(elHeadline, 'headline', elHeadline.val(), 'articles');
        }
        else{
            checkDuplicate(elHeadline, 'headline', elHeadline.val(), 'articles', origName);
        }
    })
    if(!edit){
        checkDuplicate(elHeadline, 'headline', elHeadline.val(), 'articles');
    }
    else{
        checkDuplicate(elHeadline, 'headline', elHeadline.val(), 'articles', origName);
    }
    elCheckbox.children().children().each(function(){
        $(this).blur(function (){
            checkCheckboxCollectionChecked(elCheckbox);
        });
    });
    if(edit){
        checkCheckboxCollectionChecked(elCheckbox);
    }
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
});