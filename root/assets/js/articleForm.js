import {checkDuplicate, showMaxLength, checkCheckboxCollectionChecked, searchSelect} from "./formCheck.js";
$(function (){
    searchSelect();
    let elHeadline = $("input[name='headline']");
    let elCheckbox = $("fieldset.checkbox.required");
    let elAltHeadlines = $("textarea[name='altHeadlines']")
    let edit = false;
    let origName = $("h1").text().split(' ')[1];
    let path = $(location).attr('pathname');
    let pathArray = path.slice(1).split("/");
    if(pathArray[1] === 'edit' || pathArray[1] === 'update'){
        edit = true;
    }
    elHeadline.blur(function (){
        checkForDuplicateHeadline(elHeadline, elHeadline.val(), origName, edit);
    });
    checkForDuplicateHeadline(elHeadline, elHeadline.val(), origName, edit);
    elCheckbox.children().children().each(function(){
        $(this).click(function (){
            checkCheckboxCollectionChecked(elCheckbox);
        });
    });
    if(edit){
        checkCheckboxCollectionChecked(elCheckbox);
        checkForDuplicateAltHeadlines(elAltHeadlines, elAltHeadlines.val().split(","), edit, origName);
    }
    else{
        checkForDuplicateAltHeadlines(elAltHeadlines, elAltHeadlines.val().split(","), edit);
    }
    elAltHeadlines.on("blur", function (){
        let altHeadlines = $(this).val().split(",");
        if(edit){
            checkForDuplicateAltHeadlines($(this), altHeadlines, edit, origName);
        }
        else{
            checkForDuplicateAltHeadlines($(this), altHeadlines, edit);
        }
    });
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        showMaxLength(input);
    });
});

function checkForDuplicateHeadline(el, val, origName, edit){
    if(!edit){
        checkDuplicate(el, 'headline', val, 'articles');
        setTimeout(function (){
            if(el.parent().find(".error.exists").hasClass('hide') === true){
                checkDuplicate(el, 'headline', val, 'article_alt_headline');
            }
        }, 300);
    }
    else{
        checkDuplicate(el, 'headline', val, 'articles', origName);
        setTimeout(function (){
            if(el.parent().find(".error.exists").hasClass('hide') === true){
                checkDuplicate(el, 'headline', val, 'article_alt_headline');
            }
        }, 300);
    }
}

function checkForDuplicateAltHeadlines(el, altHeadlines, edit, origName = ''){
    let ajaxPath = "../../src/Ajax.php";
    $.post(ajaxPath,
        {
            'errorType': 'altHeadlineDuplicate',
            'value': altHeadlines,
            'orig': origName
        },
        function(data){
            let result = JSON.parse(data);
            let headlines = result.duplicate;
            let origAlts = result.origAlts;
            let duplicate = false;
            for(let headline in headlines){
                if(!edit){
                    if(headlines[headline].includes(true)){
                        duplicate = true;
                    }
                }
                else{
                    if(headlines[headline].includes(true) && origAlts.includes(headline) === false){
                        duplicate = true;
                    }
                }
            }
            if(duplicate){
                el.parent().find(".error.exists").removeClass("hide");
            }
            else {
                el.parent().find(".error.exists").addClass("hide");
            }
        });
}