$(function () {
    let ajaxPath = "https://wiki.verplant-durch-aventurien.de/assets/ajax/ajax.php";
    let elName = $("input[name='projectName']");
    elName.blur(function (){
        let projectName = $(this).val();
        $.post(ajaxPath, {name: projectName}, function(data){
            let result = JSON.parse(data);
            if(result.exists === true){
                $("input[name='projectName'] ~ .error.exists").removeClass("hide");
            }
            else {
                $("input[name='projectName'] ~ .error.exists").addClass("hide");
            }
        });
    });
    var projectName = elName.val();
    $.post(ajaxPath, {name: projectName}, function(data){
        let result = JSON.parse(data);
        if(result.exists === true){
            $("input[name='projectName'] ~ .error.exists").removeClass("hide");
        }
        else {
            $("input[name='projectName'] ~ .error.exists").addClass("hide");
        }
    });
    $("main form input, main form textarea").each(function (){
        let input = $(this);
        let i = input.val().length
        $(this).siblings().each(function (){
            if($(this).hasClass('length')){
                let lengthSpan = $(this);
                let max = $(this).text()
                $(this).text( i + "/" + max)
                input.keyup(function (){
                    lengthSpan.text(input.val().length + "/" + max);
                });
            }
        });
    });
});