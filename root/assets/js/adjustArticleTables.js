$(function (){
    let tables = $(".paragraphContent table");
    if(tables.length > 0){
        tables.each(function (){
            $(this).css({"width": "auto", "height": "auto"});
            let trs = $(this).find("tr");
            trs.css("height", "auto");
        });
        let articleInfo = $(".articleInfo");
        if(articleInfo.length > 0){
            let infoBottom = articleInfo.offset().top + articleInfo.outerHeight(true);
            let infoWidth = articleInfo.outerWidth(true);
            tables.each(function (){
                if($(this).offset().top <= infoBottom){
                    $(this).css("width", "calc(100% - " + infoWidth + "px)");
                }
                else{
                    $(this).css("width", "100%");
                }
            });
        }
    }
});