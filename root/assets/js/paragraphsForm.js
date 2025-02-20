import {showMaxLength, checkFileType, checkFileSize} from "./formCheck.js";
$(function () {
    $(".newParagraph").on("click", function () {
        let lastEl = 1;
        $(".paragraph").each(function (){
            if($(this).attr("data-position") > lastEl){
                lastEl = $(this).attr("data-position");
            }
        })
        lastEl++;
        getTemplate($(this),'newParagraph.twig', [lastEl]);
        initMaxLength();
        $(".newText").off("click");
        $(".newGallery").off("click");
        setTimeout(function (){
            controlButtons();
            $(".newText").on("click", function (){
                let data = defineContentParams($(this).closest('.paragraph').attr("data-position"));
                getTemplate($(".paragraph[data-position='" + data[1] + "'] .newButtons"), 'newText.twig', data);
                initMce();
                $(".imgUpload").off("change");
                setTimeout(function (){
                    controlButtons();
                    $(".imgUpload").on("change", function (){
                        let paragraphNum = $(this).closest('.paragraph').attr("data-position");
                        let contentNum = $(this).closest('.text').attr("data-position");
                        let uploadEl = $(this).parent().siblings(".upload");
                        uploadEl.html("");
                        let reader = new FileReader();
                        reader.onload = function (){
                            setTimeout(function (){
                                getTemplate(uploadEl, 'newImg.twig', [reader.result, 1, paragraphNum, contentNum], true);
                                controlButtons();
                                initMaxLength();
                            }, 100);
                        }
                        reader.readAsDataURL($(this)[0].files[0]);
                    })
                }, 100);
            });
            $(".newGallery").on("click", function (){
                let data = defineContentParams($(this).closest('.paragraph').attr("data-position"));
                getTemplate($(".paragraph[data-position='" + data[1] + "'] .newButtons"), 'newGallery.twig', data);
                $(".galleryUpload").off("change");
                setTimeout(function (){
                    controlButtons();
                    $(".galleryUpload").on("change", function (){
                        let paragraphNum = $(this).closest('.paragraph').attr("data-position");
                        let contentNum = $(this).closest('.gallery').attr("data-position")
                        let j = 1;
                        let uploadEl = $(this).parent().siblings();
                        if(uploadEl.html()){
                            uploadEl.children().each(function (){
                                if($(this).attr("data-position") > j){
                                    j = $(this).attr("data-position");
                                }
                            })
                            j++;
                        }
                        for(const i of $(this)[0].files){
                            let galleryReader = new FileReader();
                            galleryReader.onload = function (){
                                setTimeout(function (){
                                    getTemplate(uploadEl, 'newImg.twig', [galleryReader.result, j, paragraphNum, contentNum], true);
                                    j++;
                                    setTimeout(function (){
                                        controlButtons();
                                        initMaxLength();
                                    },100);
                                }, 100);
                            }
                            galleryReader.readAsDataURL(i);
                        }
                    });
                }, 100);
            });
        }, 100);
    })
});

function getTemplate(el, template, data = [], append = false){
    let ajaxPath = "../../src/Ajax.php";
    $.post(ajaxPath,
        {
            'type': 'render',
            'template': template,
            'data': data
        },
        function(data) {
            let result = JSON.parse(data);
            let finalResult = result.render;
            if(append === true){
                el.append($(finalResult));
            }
            else{
                $(finalResult).insertBefore(el);
            }
        }
    );
}

function initMaxLength(){
    let i = 0;
    const interval = setInterval(function (){
        $("main form input, main form textarea").each(function (){
            let input = $(this);
            showMaxLength(input);
        });
        i++;
        if(i === 10){
            clearInterval(interval);
        }
    }, 100);
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
    /*let deleteEl = $(".delete");
    let mUpEl = $(".moveUp");
    let mDownEl = $(".moveDown");
    deleteEl.off("click");
    mUpEl.off("click");
    mDownEl.off("click");
    deleteEl.on("click", function (){
        $(this).parent().parent().remove();
    });
    mUpEl.on("click", function (){
        let html1 = $(this).parent().parent().html();
        let html2 = $(this).parent().parent().prev().html();
        console.log(html1);
        console.log(html2);
        $(this).parent().parent().html(html2);
        $(this).parent().parent().prev().html(html1);
    });*/
}