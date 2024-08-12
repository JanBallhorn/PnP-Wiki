$(function (){
    $(".searchselect + datalist option").click(function (){
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
    });
});