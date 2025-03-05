import {checkFileSize, checkFileType, showMaxLength, getTemplate} from "./formCheck.js";

const images = [];

$(function (){
    newTable();
    newRow();
    ImgUpload();
    setInterval(function (){
        controlButtons();
        $("main form input, main form textarea").each(function (){
            let input = $(this);
            showMaxLength(input);
        });
        parseImages();
    }, 500);
});

function parseImages(){
    $("input[name='images']").val(JSON.stringify(images));
}

function ImgUpload(){
    $(".galleryUpload").on("change", function (){
        checkFileType($(this), "image/gif image/jpeg image/png image/svg+xml image/webp");
        checkFileSize($(this), 1000000);
        let uploadEl = $(".uploads");
        for(const i of $(this)[0].files){
            let galleryReader = new FileReader();
            galleryReader.readAsDataURL(i);
            galleryReader.onload = function (){
                setTimeout(function (){
                    images.push(galleryReader.result);
                    getTemplate(uploadEl, 'newImg.twig', [galleryReader.result, '', '', i['name']], true);
                }, 200);
            }
        }
    });
}

function newTable() {
    $(".newTable").on("click", function () {
        let tableEl = $(".tables");
        let pos = tableEl.children().length + 1;
        getTemplate(tableEl, "newTable.twig", [pos], true);
        $(".newRow").off("click");
        setTimeout(function () {
            newRow();
        }, 100);
    });
}

function newRow(){
    $(".newRow").on("click", function (){
        let rowEl = $(this).parent().siblings(".rows");
        let tablePos = $(this).closest(".infoTable").attr("data-position");
        let amount = $(this).closest(".infoTable").find("input[type='number']").val();
        for (let i = 0; i < amount; i++){
            getTemplate(rowEl, "newRow.twig", [tablePos], true);
        }
    });
}

function controlButtons(){
    let minimizeEl = $(".minimize");
    let deleteEl = $(".delete");
    let mUpEl = $(".moveUp");
    let mDownEl = $(".moveDown");
    minimizeEl.off("click");
    deleteEl.off("click");
    mUpEl.off("click");
    mDownEl.off("click");
    minimizeEl.on("click", function (){
        $(this).toggleClass("minimized");
    });
    deleteEl.on("click", function (){
        let elToDel = $(this).parent().parent();
        if(elToDel.hasClass("infoTableRow")){
            elToDel.remove();
        }
        else if(elToDel.hasClass("infoTable")){
            let alert = confirm('Bist du sicher, dass du dieses Element löschen möchtest?\r\nDann klicke auf OK!');
            if(alert){
                elToDel.remove();
            }
        }
        else if(elToDel.hasClass("contentImage")){
            let curPos = elToDel.prevAll().length + 1;
            images.splice(curPos, 1);
            elToDel.remove();
        }
    });
    mUpEl.on("click", function (){
        let elToMove = $(this).parent().parent();
        if(elToMove.hasClass("infoTable")){
            let prevEl = elToMove.prev();
            let curPos = elToMove.prevAll().length + 1;
            let curTopics = [];
            let prevTopics = [];
            let curInfos = [];
            let prevInfos = [];
            elToMove.find("input[name='rowTopic" + curPos + "[]']").each(function (){
                curTopics.push($(this).val());
            });
            prevEl.find("input[name='rowTopic" + (curPos - 1) + "[]']").each(function (){

                prevTopics.push($(this).val());
            });
            elToMove.find("input[name='rowInfo" + curPos + "[]']").each(function (){
                curInfos.push($(this).val());
            });
            prevEl.find("input[name='rowInfo" + (curPos - 1) + "[]']").each(function (){
                prevInfos.push($(this).val());
            });
            let curHeadline = elToMove.find("input[name='tableHeadline[]']").val();
            elToMove.find("input[name='tableHeadline[]']").val(prevEl.find("input[name='tableHeadline[]']").val());
            prevEl.find("input[name='tableHeadline[]']").val(curHeadline);
            let curHTML = elToMove.find(".rows").html();
            elToMove.find(".rows").html(prevEl.find(".rows").html());
            prevEl.find(".rows").html(curHTML);
            let i = 0;
            prevEl.find("input[name='rowTopic" + curPos + "[]']").each(function (){
                $(this).val(curTopics[i]);
                $(this).attr("name", "rowTopic" + (curPos - 1) + "[]");
                i++;
            });
            i = 0;
            elToMove.find("input[name='rowTopic" + (curPos - 1) + "[]']").each(function (){
                $(this).val(prevTopics[i]);
                $(this).attr("name", "rowTopic" + curPos + "[]");
                i++;
            });
            i = 0;
            prevEl.find("input[name='rowInfo" + curPos + "[]']").each(function (){
                $(this).val(curInfos[i]);
                $(this).attr("name", "rowInfo" + (curPos - 1) + "[]");
                i++;
            });
            i = 0;
            elToMove.find("input[name='rowInfo" + (curPos - 1) + "[]']").each(function (){
                $(this).val(prevInfos[i]);
                $(this).attr("name", "rowInfo" + curPos + "[]");
                i++;
            });
        }
        else if(elToMove.hasClass("contentImage")){
            let curPos = elToMove.prevAll().length;
            let curImg = images[curPos];
            let prevImg = images[curPos - 1];
            images.splice(curPos - 1, 2, curImg, prevImg);
            let prevEl = elToMove.prev();
            prevEl.find("img").attr("src", curImg);
            let prevImgAlt = prevEl.find("img").attr("alt");
            elToMove.find("img").attr("src", prevImg)
            let curImgAlt = elToMove.find("img").attr("alt");
            prevEl.find("img").attr("alt", curImgAlt);
            elToMove.find("img").attr("alt", prevImgAlt);
            let prevElVal = prevEl.find("input[type='text']").val();
            let curVal = elToMove.find("input[type='text']").val();
            elToMove.find("input[type='text']").val(prevElVal);
            prevEl.find("input[type='text']").val(curVal);
        }
    });
    mDownEl.on("click", function (){
        let elToMove = $(this).parent().parent();
        if(elToMove.hasClass("infoTable")){
            let nextEl = elToMove.next();
            let curPos = elToMove.prevAll().length + 1;
            let curTopics = [];
            let nextTopics = [];
            let curInfos = [];
            let nextInfos = [];
            elToMove.find("input[name='rowTopic" + curPos + "[]']").each(function (){
                curTopics.push($(this).val());
            });
            nextEl.find("input[name='rowTopic" + (curPos + 1) + "[]']").each(function (){

                nextTopics.push($(this).val());
            });
            elToMove.find("input[name='rowInfo" + curPos + "[]']").each(function (){
                curInfos.push($(this).val());
            });
            nextEl.find("input[name='rowInfo" + (curPos + 1) + "[]']").each(function (){
                nextInfos.push($(this).val());
            });
            let curHeadline = elToMove.find("input[name='tableHeadline[]']").val();
            elToMove.find("input[name='tableHeadline[]']").val(nextEl.find("input[name='tableHeadline[]']").val());
            nextEl.find("input[name='tableHeadline[]']").val(curHeadline);
            let curHTML = elToMove.find(".rows").html();
            elToMove.find(".rows").html(nextEl.find(".rows").html());
            nextEl.find(".rows").html(curHTML);
            let i = 0;
            nextEl.find("input[name='rowTopic" + curPos + "[]']").each(function (){
                $(this).val(curTopics[i]);
                $(this).attr("name", "rowTopic" + (curPos + 1) + "[]");
                i++;
            });
            i = 0;
            elToMove.find("input[name='rowTopic" + (curPos + 1) + "[]']").each(function (){
                $(this).val(nextTopics[i]);
                $(this).attr("name", "rowTopic" + curPos + "[]");
                i++;
            });
            i = 0;
            nextEl.find("input[name='rowInfo" + curPos + "[]']").each(function (){
                $(this).val(curInfos[i]);
                $(this).attr("name", "rowInfo" + (curPos + 1) + "[]");
                i++;
            });
            i = 0;
            elToMove.find("input[name='rowInfo" + (curPos + 1) + "[]']").each(function (){
                $(this).val(nextInfos[i]);
                $(this).attr("name", "rowInfo" + curPos + "[]");
                i++;
            });
        }
        else if(elToMove.hasClass("contentImage")){
            let curPos = elToMove.prevAll().length;
            let curImg = images[curPos];
            let nextImg = images[curPos + 1];
            images.splice(curPos, 2, nextImg, curImg);
            let nextEl = elToMove.next();
            nextEl.find("img").attr("src", curImg);
            let nextImgAlt = nextEl.find("img").attr("alt");
            elToMove.find("img").attr("src", nextImg)
            let curImgAlt = elToMove.find("img").attr("alt");
            nextEl.find("img").attr("alt", curImgAlt);
            elToMove.find("img").attr("alt", nextImgAlt);
            let nextElVal = nextEl.find("input[type='text']").val();
            let curVal = elToMove.find("input[type='text']").val();
            elToMove.find("input[type='text']").val(nextElVal);
            nextEl.find("input[type='text']").val(curVal);
        }
    });
}