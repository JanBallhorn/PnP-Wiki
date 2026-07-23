import {checkDuplicate, showMaxLength, checkFileType, checkFileSize, getTemplate} from "./formCheck.js";
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
    $(".newTemplateRow").on("click", function (){
        let lastGroup = $(".templateRow").last().find("input[name='templateGroup[]']").val() || "";
        getTemplate($(".templateRows"), "newTemplateRow.twig", [lastGroup], true);
    });
    $(document).on("click", ".templateRow .delete", function (){
        $(this).closest(".templateRow").remove();
    });
    $(document).on("click", ".templateRow .moveUp", function (){
        let row = $(this).closest(".templateRow");
        row.prev(".templateRow").before(row);
    });
    $(document).on("click", ".templateRow .moveDown", function (){
        let row = $(this).closest(".templateRow");
        row.next(".templateRow").after(row);
    });
    $(".newSectionRow").on("click", function (){
        getTemplate($(".sectionRows"), "newSectionRow.twig", [""], true);
    });
    $(document).on("click", ".sectionRow .delete", function (){
        $(this).closest(".sectionRow").remove();
    });
    $(document).on("click", ".sectionRow .moveUp", function (){
        let row = $(this).closest(".sectionRow");
        row.prev(".sectionRow").before(row);
    });
    $(document).on("click", ".sectionRow .moveDown", function (){
        let row = $(this).closest(".sectionRow");
        row.next(".sectionRow").after(row);
    });
});