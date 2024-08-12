$(function (){
    $(".content ul li i").click(function (){
        let el = $(this).parent().parent();
        let sublist = el.find ("> ul");
        if(sublist.hasClass("hidden")){
            sublist.show("slide", { direction: "up" }, 300)
            sublist.removeClass("hidden");
        }
        else{
            sublist.hide("slide", { direction: "up" }, 300);
            sublist.addClass("hidden");
        }
    })
})