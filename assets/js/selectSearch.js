$(function (){
    $(".searchselect").click(function (){
        if($(".searchselect + datalist").is(":visible")){
            $(".searchselect ~ i").css("transform", "translateY(-50%) rotate(180deg)");
        }
        else{
            $(".searchselect ~ i").css("transform", "translateY(-50%) rotate(0)");
        }
    })
    $(".searchselect + datalist option").click(function (){
        if($(".searchselect + datalist").is(":visible")){
            $(".searchselect ~ i").css("transform", "translateY(-50%) rotate(180deg)");
        }
        $(".searchselect").val($(this).val());
    });
    $(".searchselect").keyup(function (){
        let input = $(this).val();
        $(".searchselect + datalist option").each(function (){
            if(!$(this).val().toLowerCase().includes(input.toLowerCase())){
                $(this).addClass("hide");
            }
            else {
                $(this).removeClass("hide");
            }
        });
    });
    $(".searchselect").blur(function (){
        let exists = false;
        let input = $(this).val();
        $(".searchselect + datalist option").each(function (){
            if($(this).val().toLowerCase() === input.toLowerCase()){
                exists = true;
                input = $(this).val();
            }
        });
        if(exists === false){
            $(".searchselect ~ .error.notfound").removeClass("hide");
        }
        else{
            $(".searchselect ~ .error.notfound").addClass("hide");
        }
        if($(".searchselect + datalist").is(":hidden")){
            $(".searchselect ~ i").css("transform", "translateY(-50%) rotate(0)");
        }
    });
});