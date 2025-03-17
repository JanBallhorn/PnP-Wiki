import {checkFileSize, checkFileType, showMaxLength, getTemplate, searchSelect} from "./formCheck.js";

const images = {};
$(function () {
    getImages();
    newParagraph();
    newText();
    newGallery();
    singleImgUpload();
    galleryImgUpload();
    newSource();
    const intervalControlButtons = setInterval(function (){
        controlButtons();
        searchSelect();
        changeSourceType();
        $("main form input, main form textarea").each(function (){
            let input = $(this);
            showMaxLength(input);
        });
        parseImages();
    }, 500);
});

function getImages(){
    $(".contentImage").each(function (){
        let paragraphNum = $(this).closest('.paragraph').attr("data-position");
        let contentClass = $(this).closest('.contentElement').attr("class");
        let contentNum = $(this).closest('.contentElement').attr("data-position");
        let data = $(this).find("img").attr("src");
        let property = "p" + paragraphNum + "c" + contentNum;
        if(typeof images[property] === "undefined"){
            images[property] = [];
        }
        if(contentClass.includes("text")){
            images[property] = [data];
        }
        else{
            images[property][images[property].length] = data;
        }
    });
}

function newParagraph(){
    $(".newParagraph").on("click", function () {
        let lastEl = 1;
        $(".paragraph").each(function (){
            if($(this).attr("data-position") > lastEl){
                lastEl = $(this).attr("data-position");
            }
        })
        lastEl++;
        getTemplate($(this),'newParagraph.twig', [lastEl]);
        $(".newText").off("click");
        $(".newGallery").off("click");
        setTimeout(function (){
            newText();
            newGallery();
        }, 100);
    });
}

function newText(){
    $(".newText").on("click", function (){
        let data = defineContentParams($(this).closest('.paragraph').attr("data-position"));
        getTemplate($(".paragraph[data-position='" + data[1] + "'] .newButtons"), 'newText.twig', data);
        initMce();
        $(".imgUpload").off("change");
        setTimeout(function (){
            singleImgUpload();
        }, 200);
    });
}

function newGallery(){
    $(".newGallery").on("click", function (){
        let data = defineContentParams($(this).closest('.paragraph').attr("data-position"));
        getTemplate($(".paragraph[data-position='" + data[1] + "'] .newButtons"), 'newGallery.twig', data);
        $(".galleryUpload").off("change");
        setTimeout(function (){
            galleryImgUpload();
        }, 200);
    });
}

function singleImgUpload(){
    $(".imgUpload").on("change", function (){
        checkFileType($(this), "image/gif image/jpeg image/png image/svg+xml image/webp");
        checkFileSize($(this), 1000000);
        let paragraphNum = $(this).closest('.paragraph').attr("data-position");
        let contentNum = $(this).closest('.text').attr("data-position");
        let uploadEl = $(this).parent().siblings(".upload");
        uploadEl.html("");
        let reader = new FileReader();
        let file = $(this)[0].files[0];
        reader.onload = function (){
            setTimeout(function (){
                images["p" + paragraphNum + "c" + contentNum] = [reader.result];
                getTemplate(uploadEl, 'newImg.twig', [reader.result, paragraphNum, contentNum, file['name']], true);
            }, 100);
        }
        reader.readAsDataURL(file);
    });
}

function galleryImgUpload(){
    $(".galleryUpload").on("change", function (){
        checkFileType($(this), "image/gif image/jpeg image/png image/svg+xml image/webp");
        checkFileSize($(this), 1000000);
        let paragraphNum = $(this).closest('.paragraph').attr("data-position");
        let contentNum = $(this).closest('.gallery').attr("data-position");
        let uploadEl = $(this).parent().siblings();
        let property = "p" + paragraphNum + "c" + contentNum;
        if(typeof images[property] === 'undefined'){
            images[property] = [];
        }
        for(const i of $(this)[0].files){
            let galleryReader = new FileReader();
            galleryReader.readAsDataURL(i);
            galleryReader.onload = function (){
                const firstTO = setTimeout(function (){
                    images[property][images[property].length] = galleryReader.result;
                    getTemplate(uploadEl, 'newImg.twig', [galleryReader.result, paragraphNum, contentNum, i['name']], true);
                }, 100);
            }
        }
    });
}

function initMce(){
    $.getScript('/tinymce/tinymce.min.js', function() {
        tinymce.init({
            selector: 'textarea.tinymce',
            license_key: 'gpl',
            plugins: [ 'link','lists','table' ],
            toolbar: [
                { name: 'history', items: [ 'undo', 'redo' ] },
                { name: 'styles', items: [ 'styles' ] },
                { name: 'formatting', items: [ 'bold', 'italic','underline' ] },
                { name: 'alignment', items: [ 'alignleft', 'aligncenter', 'alignright', 'alignjustify' ] },
                { name: 'indentation', items: [ 'outdent', 'indent' ] },
                { name: 'lists', items: [ 'numlist', 'bullist' ] },
                { name: 'links', items: [ 'link', 'unlink' ] }
            ],
            language: 'de',
            promotion: false,
            skin: 'tinymce-5',
            content_css: 'tinymce-5'
        });
    });
}

function parseImages(){
    $("input[name='images']").val(JSON.stringify(images));
}

function defineContentParams(paragraphPos){
    let lastContent = 0;
    $(".paragraph[data-position='" + paragraphPos + "'] .contentElement").each(function (){
        if ($(this).attr("data-position") > lastContent){
            lastContent = $(this).attr("data-position");
        }
    })
    lastContent++;
    return [lastContent, paragraphPos];
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
        $(this).parent().parent().toggleClass("minimized");
    });
    deleteEl.on("click", function (){
        let elToDel = $(this).parent().parent();
        let parNum = elToDel.closest('.paragraph').attr('data-position');
        let curPos = elToDel.attr('data-position');
        if(elToDel.hasClass("contentImage")){
            curPos = elToDel.prevAll().length;
            let contentNum = elToDel.closest('.contentElement').attr('data-position');
            images["p" + parNum + "c" + contentNum].splice(curPos, 1);
            elToDel.remove();
        }
        else if(elToDel.hasClass("source")){
            elToDel.remove();
        }
        else{
            let alert = confirm('Bist du sicher, dass du dieses Element löschen möchtest?\r\nDann klicke auf OK!');
            if(alert){
                if(elToDel.hasClass("contentElement")){
                    if(images["p" + parNum + "c" + curPos] !== "undefined"){
                        delete images["p" + parNum + "c" + curPos];
                    }
                    const imageEntries = Object.entries(images)
                    imageEntries.sort();
                    for (const i of imageEntries){
                        if(i[0].charAt(1) === parNum && i[0].charAt(3) > curPos){
                            delete images["p" + parNum + "c" + i[0].charAt(3)];
                            images["p" + parNum + "c" + (i[0].charAt(3) - 1)] = i[1];
                        }
                    }
                    elToDel.nextAll(".contentElement").each(function (){
                        $(this).attr('data-position', $(this).attr('data-position') - 1);
                        $(this).find(".contentImage input[type='text']").each(function (){
                            $(this).attr("name", "p" + parNum + "c" + ($(this).attr("name").charAt(3) - 1) + "figcaption[]");
                        });
                    });
                    elToDel.remove();
                }
                else{
                    const imageEntries = Object.entries(images)
                    imageEntries.sort();
                    for (const i of imageEntries){
                        if(i[0].charAt(1) === parNum){
                            delete images["p" + parNum + "c" + i[0].charAt(3)];
                        }
                        else if(i[0].charAt(1) > parNum){
                            delete images["p" + i[0].charAt(1) + "c" + i[0].charAt(3)];
                            images["p" + (i[0].charAt(1) - 1) + "c" + i[0].charAt(3)] = i[1];
                        }
                    }
                    elToDel.nextAll(".paragraph").each(function (){
                        $(this).attr('data-position', $(this).attr('data-position') - 1);
                        $(this).find(".contentElement input.galleryUpload, .contentElement textarea.tinymce").each(function (){
                            let elementName = "";
                            if($(this).hasClass("galleryUpload")){
                                elementName = "gallery";
                            }
                            else{
                                elementName = "text";
                            }
                            $(this).attr("name", "p" + ($(this).attr("name").charAt(1) - 1) + "c" + $(this).attr("name").charAt(3) + elementName);
                            $(this).closest(".contentElement").find(".contentImage input[type='text']").each(function (){
                                $(this).attr("name", "p" + ($(this).attr("name").charAt(1) - 1) + "c" + $(this).attr("name").charAt(3) + "figcaption[]");
                            });
                        });
                    });
                    elToDel.remove();
                }
            }
        }
    });
    mUpEl.on("click", function (){
        let elToMove = $(this).parent().parent();
        let parNum = elToMove.closest('.paragraph').attr('data-position');
        let curPos = Number(elToMove.attr('data-position'));
        if(elToMove.hasClass("contentImage")){
            curPos = elToMove.prevAll().length;
            if(curPos !== 0){
                let contentNum = elToMove.closest('.contentElement').attr('data-position');
                let curImg = images["p" + parNum + "c" + contentNum][curPos];
                let prevImg = images["p" + parNum + "c" + contentNum][curPos - 1];
                images["p" + parNum + "c" + contentNum][curPos] = prevImg;
                images["p" + parNum + "c" + contentNum][curPos - 1] = curImg;
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
        }
        else if(elToMove.hasClass("contentElement")){
            if(curPos !== 0){
                if(images["p" + parNum + "c" + curPos] !== "undefined" && images["p" + parNum + "c" + (curPos - 1)] !== "undefined"){
                    let curImg = images["p" + parNum + "c" + curPos];
                    images["p" + parNum + "c" + curPos] = images["p" + parNum + "c" + (curPos - 1)];
                    images["p" + parNum + "c" + (curPos - 1)] = curImg;
                }
                else if(images["p" + parNum + "c" + curPos] !== "undefined" && images["p" + parNum + "c" + (curPos - 1)] === "undefined"){
                    images["p" + parNum + "c" + (curPos - 1)] = images["p" + parNum + "c" + curPos];
                    delete images["p" + parNum + "c" + curPos];
                }
                else if(images["p" + parNum + "c" + curPos] === "undefined" && images["p" + parNum + "c" + (curPos - 1)] !== "undefined"){
                    images["p" + parNum + "c" + curPos] = images["p" + parNum + "c" + (curPos - 1)];
                    delete images["p" + parNum + "c" + (curPos - 1)];
                }
                let prevEl = elToMove.prev();
                if(elToMove.hasClass("text") && prevEl.hasClass("text")){
                    let elToMoveText = elToMove.find("iframe")[0].contentDocument.body.innerHTML;
                    elToMove.find("iframe")[0].contentDocument.body.innerHTML = prevEl.find("iframe")[0].contentDocument.body.innerHTML;
                    prevEl.find("iframe")[0].contentDocument.body.innerHTML = elToMoveText;
                    if(elToMove.find(".contentImage").length !== 0){
                        let imageData = elToMove.find("img").attr("src");
                        let imageAlt = elToMove.find("img").attr("alt");
                        let inputVal = elToMove.find("input[type='text']").val();
                        elToMove.find(".contentImage").remove();
                        getTemplate(prevEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos - 1, imageAlt, inputVal], true);
                    }
                    if(prevEl.find(".contentImage").length !== 0){
                        let imageData = prevEl.find("img").attr("src");
                        let imageAlt = prevEl.find("img").attr("alt");
                        let inputVal = prevEl.find("input[type='text']").val();
                        prevEl.find(".contentImage").remove()
                        getTemplate(elToMove.find(".upload"), 'newImg.twig', [imageData, parNum, curPos, imageAlt, inputVal], true);
                    }
                }
                else if(elToMove.hasClass("text") && prevEl.hasClass("gallery")){
                    let elToMoveText = elToMove.find("iframe")[0].contentDocument.body.innerHTML;
                    let prevVals = [];
                    prevEl.find("input[type='text']").each(function (){
                        prevVals.push($(this).val());
                    })
                    let prevHTML = prevEl.html();
                    prevEl.remove();
                    getTemplate(elToMove, "newText.twig", [curPos - 1, parNum]);
                    initMce();
                    setTimeout(function (){
                        let newEl = elToMove.prev();
                        newEl.find("iframe")[0].contentDocument.body.innerHTML = elToMoveText;
                        if(elToMove.find(".contentImage").length !== 0){
                            let imageData = elToMove.find("img").attr("src");
                            let imageAlt = elToMove.find("img").attr("alt");
                            let inputVal = elToMove.find("input[type='text']").val();
                            getTemplate(newEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos - 1, imageAlt, inputVal], true);
                        }
                    }, 200);
                    setTimeout(function (){
                        elToMove.removeClass("text");
                        elToMove.addClass("gallery");
                        changeInputNamesAndVals(elToMove, prevHTML, prevVals, parNum, curPos);
                        singleImgUpload();
                        galleryImgUpload();
                    }, 400);
                }
                else if(elToMove.hasClass("gallery") && prevEl.hasClass("text")){
                    let prevElText = prevEl.find("iframe")[0].contentDocument.body.innerHTML;
                    let elToMoveVals = [];
                    elToMove.find("input[type='text']").each(function (){
                        elToMoveVals.push($(this).val());
                    })
                    let elToMoveHTML = elToMove.html();
                    elToMove.remove();
                    getTemplate(prevEl.next(), "newText.twig", [curPos, parNum]);
                    initMce();
                    setTimeout(function (){
                        let newEl = prevEl.next();
                        newEl.find("iframe")[0].contentDocument.body.innerHTML = prevElText;
                        if(prevEl.find(".contentImage").length !== 0){
                            let imageData = prevEl.find("img").attr("src");
                            let imageAlt = prevEl.find("img").attr("alt");
                            let inputVal = prevEl.find("input[type='text']").val();
                            getTemplate(newEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos, imageAlt, inputVal], true);
                        }
                    }, 200);
                    setTimeout(function (){
                        prevEl.removeClass("text");
                        prevEl.addClass("gallery");
                        changeInputNamesAndVals(prevEl, elToMoveHTML, elToMoveVals, parNum, curPos - 1);
                        singleImgUpload();
                        galleryImgUpload();
                    }, 400);
                }
                else if(elToMove.hasClass("gallery") && prevEl.hasClass("gallery")){
                    let elToMoveVals = [];
                    elToMove.find("input[type='text']").each(function (){
                        elToMoveVals.push($(this).val());
                    })
                    let elToMoveHTML = elToMove.html();
                    let prevVals = [];
                    prevEl.find("input[type='text']").each(function (){
                        prevVals.push($(this).val());
                    })
                    let prevHTML = prevEl.html();
                    setTimeout(function (){
                        changeInputNamesAndVals(elToMove, prevHTML, prevVals, parNum, curPos);
                        changeInputNamesAndVals(prevEl, elToMoveHTML, elToMoveVals, parNum, curPos - 1);
                        singleImgUpload();
                        galleryImgUpload();
                    }, 400);
                }
            }
        }
        else{
            let prevEl = elToMove.prev().prev();
            const imageEntries = Object.entries(images)
            imageEntries.sort();
            let curEntries = [];
            let prevEntries = [];
            for (const i of imageEntries){
                if(i[0].charAt(1) === parNum){
                    curEntries.push(i);
                    delete images["p" + parNum + "c" + i[0].charAt(3)];
                }
                else if(Number(i[0].charAt(1)) === parNum - 1){
                    prevEntries.push(i);
                    delete images["p" + i[0].charAt(1) + "c" + i[0].charAt(3)];
                }
            }
            for(const i of curEntries){
                images["p" + (Number(i[0].charAt(1)) - 1) + "c" + i[0].charAt(3)] = i[1];
            }
            for(const i of prevEntries){
                images["p" + (Number(i[0].charAt(1)) + 1) + "c" + i[0].charAt(3)] = i[1];
            }
            let curHeadline = elToMove.find("input[name='headline[]']").val();
            let prevHeadline = prevEl.find("input[name='headline[]']").val();
            prevEl.find("input[name='headline[]']").val(curHeadline);
            elToMove.find("input[name='headline[]']").val(prevHeadline);
            let currentHTMLs = getParagraphContents(elToMove);
            let prevHTMLs = getParagraphContents(prevEl);
            elToMove.find(".contentElement").each(function(){
                $(this).remove();
            });
            prevEl.find(".contentElement").each(function(){
                $(this).remove();
            })
            fillMovedParagraph(prevEl, parNum - 1, currentHTMLs).then(value => {fillMovedParagraph(elToMove, parNum, prevHTMLs).then(value => {initUploads().then(value => {console.log("done")})})});
        }
    });
    mDownEl.on("click", function(){
        let elToMove = $(this).parent().parent();
        let parNum = elToMove.closest('.paragraph').attr('data-position');
        let curPos = Number(elToMove.attr('data-position'));
        if(elToMove.hasClass("contentImage")){
            curPos = elToMove.prevAll().length;
            let contentNum = elToMove.closest('.contentElement').attr('data-position');
            let curImg = images["p" + parNum + "c" + contentNum][curPos];
            let nextImg = images["p" + parNum + "c" + contentNum][curPos + 1];
            images["p" + parNum + "c" + contentNum][curPos] = nextImg;
            images["p" + parNum + "c" + contentNum][curPos + 1] = curImg;
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
        else if(elToMove.hasClass("contentElement")){
            if(images["p" + parNum + "c" + curPos] !== "undefined" && images["p" + parNum + "c" + (curPos + 1)] !== "undefined"){
                let curImg = images["p" + parNum + "c" + curPos];
                images["p" + parNum + "c" + curPos] = images["p" + parNum + "c" + (curPos + 1)];
                images["p" + parNum + "c" + (curPos + 1)] = curImg;
            }
            else if(images["p" + parNum + "c" + curPos] !== "undefined" && images["p" + parNum + "c" + (curPos + 1)] === "undefined"){
                images["p" + parNum + "c" + (curPos + 1)] = images["p" + parNum + "c" + curPos];
                delete images["p" + parNum + "c" + curPos];
            }
            else if(images["p" + parNum + "c" + curPos] === "undefined" && images["p" + parNum + "c" + (curPos + 1)] !== "undefined"){
                images["p" + parNum + "c" + curPos] = images["p" + parNum + "c" + (curPos + 1)];
                delete images["p" + parNum + "c" + (curPos + 1)];
            }
            let nextEl = elToMove.next();
            if(elToMove.hasClass("text") && nextEl.hasClass("text")){
                let elToMoveText = elToMove.find("iframe")[0].contentDocument.body.innerHTML;
                elToMove.find("iframe")[0].contentDocument.body.innerHTML = nextEl.find("iframe")[0].contentDocument.body.innerHTML;
                nextEl.find("iframe")[0].contentDocument.body.innerHTML = elToMoveText;
                if(elToMove.find(".contentImage").length !== 0){
                    let imageData = elToMove.find("img").attr("src");
                    let imageAlt = elToMove.find("img").attr("alt");
                    let inputVal = elToMove.find("input[type='text']").val();
                    elToMove.find(".contentImage").remove();
                    getTemplate(nextEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos + 1, imageAlt, inputVal], true);
                }
                if(nextEl.find(".contentImage").length !== 0){
                    let imageData = nextEl.find("img").attr("src");
                    let imageAlt = nextEl.find("img").attr("alt");
                    let inputVal = nextEl.find("input[type='text']").val();
                    nextEl.find(".contentImage").remove()
                    getTemplate(elToMove.find(".upload"), 'newImg.twig', [imageData, parNum, curPos, imageAlt, inputVal], true);
                }
            }
            else if(elToMove.hasClass("text") && nextEl.hasClass("gallery")){
                let elToMoveText = elToMove.find("iframe")[0].contentDocument.body.innerHTML;
                let nextVals = [];
                nextEl.find("input[type='text']").each(function (){
                    nextVals.push($(this).val());
                })
                let nextHTML = nextEl.html();
                nextEl.remove();
                getTemplate(elToMove.next(), "newText.twig", [curPos + 1, parNum]);
                initMce();
                setTimeout(function (){
                    let newEl = elToMove.next();
                    newEl.find("iframe")[0].contentDocument.body.innerHTML = elToMoveText;
                    if(elToMove.find(".contentImage").length !== 0){
                        let imageData = elToMove.find("img").attr("src");
                        let imageAlt = elToMove.find("img").attr("alt");
                        let inputVal = elToMove.find("input[type='text']").val();
                        getTemplate(newEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos + 1, imageAlt, inputVal], true);
                    }}, 200);
                setTimeout(function (){
                    elToMove.removeClass("text");
                    elToMove.addClass("gallery");
                    changeInputNamesAndVals(elToMove, nextHTML, nextVals, parNum, curPos);
                    singleImgUpload();
                    galleryImgUpload();
                    }, 400);
            }
            else if(elToMove.hasClass("gallery") && nextEl.hasClass("text")){
                let nextElText = nextEl.find("iframe")[0].contentDocument.body.innerHTML;
                let elToMoveVals = [];
                elToMove.find("input[type='text']").each(function (){
                    elToMoveVals.push($(this).val());
                })
                let elToMoveHTML = elToMove.html();
                elToMove.remove();
                getTemplate(nextEl, "newText.twig", [curPos, parNum]);
                initMce();
                setTimeout(function (){
                    let newEl = nextEl.prev();
                    newEl.find("iframe")[0].contentDocument.body.innerHTML = nextElText;
                    if(nextEl.find(".contentImage").length !== 0){
                        let imageData = nextEl.find("img").attr("src");
                        let imageAlt = nextEl.find("img").attr("alt");
                        let inputVal = nextEl.find("input[type='text']").val();
                        getTemplate(newEl.find(".upload"), 'newImg.twig', [imageData, parNum, curPos, imageAlt, inputVal], true);
                    }}, 200);
                setTimeout(function (){
                    nextEl.removeClass("text");
                    nextEl.addClass("gallery");
                    changeInputNamesAndVals(nextEl, elToMoveHTML, elToMoveVals, parNum, curPos + 1);
                    singleImgUpload();
                    galleryImgUpload();}, 400);
            }
            else if(elToMove.hasClass("gallery") && nextEl.hasClass("gallery")){
                let elToMoveVals = [];
                elToMove.find("input[type='text']").each(function (){
                    elToMoveVals.push($(this).val());
                })
                let elToMoveHTML = elToMove.html();
                let nextVals = [];
                nextEl.find("input[type='text']").each(function (){
                    nextVals.push($(this).val());
                })
                let nextHTML = nextEl.html();
                setTimeout(function (){
                    changeInputNamesAndVals(elToMove, nextHTML, nextVals, parNum, curPos);
                    changeInputNamesAndVals(nextEl, elToMoveHTML, elToMoveVals, parNum, curPos + 1);
                    singleImgUpload();
                    galleryImgUpload();}, 400);
            }
        }
        else{
            let nextEl = elToMove.next().next();
            const imageEntries = Object.entries(images)
            imageEntries.sort();
            let curEntries = [];
            let nextEntries = [];
            for (const i of imageEntries){
                if(i[0].charAt(1) === parNum){
                    curEntries.push(i);
                    delete images["p" + parNum + "c" + i[0].charAt(3)];
                }
                else if(Number(i[0].charAt(1)) === Number(parNum) + 1){
                    nextEntries.push(i);
                    delete images["p" + i[0].charAt(1) + "c" + i[0].charAt(3)];
                }
            }
            for(const i of curEntries){
                images["p" + (Number(i[0].charAt(1)) + 1) + "c" + i[0].charAt(3)] = i[1];
            }
            for(const i of nextEntries){
                images["p" + (Number(i[0].charAt(1)) - 1) + "c" + i[0].charAt(3)] = i[1];
            }
            let curHeadline = elToMove.find("input[name='headline[]']").val();
            let nextHeadline = nextEl.find("input[name='headline[]']").val();
            nextEl.find("input[name='headline[]']").val(curHeadline);
            elToMove.find("input[name='headline[]']").val(nextHeadline);
            let currentHTMLs = getParagraphContents(elToMove);
            let nextHTMLs = getParagraphContents(nextEl);
            elToMove.find(".contentElement").each(function(){
                $(this).remove();
            });
            nextEl.find(".contentElement").each(function(){
                $(this).remove();
            })
            fillMovedParagraph(nextEl, Number(parNum) + 1, currentHTMLs).then(value => {fillMovedParagraph(elToMove, parNum, nextHTMLs).then(value => {initUploads().then(value => {console.log("done")})})});
        }
    });
}

function changeInputNamesAndVals(el, html, vals, parNum, contentPos){
    el.html(html);
    let i = 0;
    el.find("input[type='file']").attr("name", "p" + parNum + "c" + contentPos + "gallery");
    el.find("input[type='text']").each(function (){
        $(this).val(vals[i]);
        $(this).attr("name", "p" + parNum + "c" + contentPos + "figcaption[]");
        i++;
    })
}

function getParagraphContents(el){
    let contents = [];
    el.find(".contentElement").each(function(){
        let result;
        if($(this).hasClass("text")){
            let iframeHTML = $(this).find("iframe")[0].contentDocument.body.innerHTML;
            result = ["text", iframeHTML];
            if($(this).find(".contentImage").length !== 0){
                result.push($(this).find("img").attr("src"));
                result.push($(this).find("img").attr("alt"));
                result.push($(this).find("input[type='text']").val());
            }
        }
        else{
            let galleryHTML = $(this).html();
            let inputVals = [];
            $(this).find("input[type='text']").each(function (){
                inputVals.push($(this).val());
            })
            result = ["gallery", galleryHTML, inputVals];
        }
        contents.push(result);
    });
    return contents;
}
function templateForParagraph(el, template, data){
    return new Promise(function (resolve){
        setTimeout(function(){
            getTemplate(el, template, data);
            resolve("done");
        }, 100);
    });
}
function initMceForParagraph(){
    return new Promise(function (resolve){
        setTimeout(function(){
            initMce();
            resolve("done");
        }, 100);
    });
}
function getNewEl(el){
    return new Promise(function (resolve){
        setTimeout(function(){
            let newEl = el.find(".newButtons").prev();
            resolve(newEl);
        }, 100);
    });
}
function fillContent(el, html, parNum, x){
    return new Promise(function (resolve){
        setTimeout(function(){
            if(html[0] === "text"){
                el.find("iframe")[0].contentDocument.body.innerHTML = html[1];
                if(html.length !== 2){
                    getTemplate(el.find(".upload"), "newImg.twig", [html[2], parNum, x, html[3], html[4]], true);
                }
            }
            else{
                changeInputNamesAndVals(el, html[1], html[2], parNum, x);
            }
            resolve("done");
        }, 100);
    });
}
function initUploads(){
    return new Promise(function (resolve){
        setTimeout(function(){
            singleImgUpload();
            galleryImgUpload();
            resolve("done");
        }, 100);
    });
}
async function fillMovedParagraph(el, parNum, html){
    let x = 1;
    let template;
    for (const i of html){
        if(i[0] === "text"){
            template = "newText.twig";
        }
        else{
            template = "newGallery.twig";
        }
        await templateForParagraph(el.find(".newButtons"), template, [x, parNum]);
        await initMceForParagraph();
        let newEl = await getNewEl(el);
        await fillContent(newEl, i, parNum, x);
        x++;
    }
}

function newSource(){
    $(".newSource").on("click", function (){
        let rowEl = $(this).parent().siblings(".sources");
        let amount = $(this).closest(".sourceButtons").find("input[type='number']").val();
        for (let i = 0; i < amount; i++){
            getTemplate(rowEl, "newSource.twig", [], true);
        }
    });
}

function changeSourceType(){
    let options = $("input.searchSelect + datalist option");
    options.on("click", function (){
        let typeSelectOptions = $(this).closest("label").siblings("label:has(select)").find("option");
        let type = $(this).attr("data-type")
        typeSelectOptions.each(function (){
            if($(this).val() === type){
                $(this).prop("selected", true);
            }
        });
    });
}