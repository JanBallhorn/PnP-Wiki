$(function (){
    var ajaxPath = "https://wiki.verplant-durch-aventurien.de/assets/ajax/ajax.php"
    var elEmail = $("input[name='email']");
    var elUsername = $("input[name='username']");
    var elPassword = $("input[name='password']");
    elEmail.blur(function (){
        var email = $(this).val();
        $.post(ajaxPath, {email: email}, function(data){
            let result = JSON.parse(data);
            if(result.exists === true){
                $("input[name='email'] ~ .error.exists").removeClass("hide");
            }
            else {
                $("input[name='email'] ~ .error.exists").addClass("hide");
            }
        });
    });
    var email = elEmail.val();
    $.post(ajaxPath, {email: email}, function(data){
        let result = JSON.parse(data);
        if(result.exists === true){
            $("input[name='email'] ~ .error.exists").removeClass("hide");
        }
        else {
            $("input[name='email'] ~ .error.exists").addClass("hide");
        }
    })
    elUsername.blur(function (){
        var username = $(this).val();
        $.post(ajaxPath, {username: username}, function(data){
            let result = JSON.parse(data);
            if(result.exists === true){
                $("input[name='username'] ~ .error.exists").removeClass("hide");
            }
            else {
                $("input[name='username'] ~ .error.exists").addClass("hide");
            }
            if(result.length === false){
                $("input[name='username'] ~ .error.length").removeClass("hide");
            }
            else {
                $("input[name='username'] ~ .error.length").addClass("hide");
            }
        })
    })
    var username = elUsername.val();
    $.post(ajaxPath, {username: username}, function(data){
        let result = JSON.parse(data);
        if(result.exists === true){
            $("input[name='username'] ~ .error.exists").removeClass("hide");
        }
        else {
            $("input[name='username'] ~ .error.exists").addClass("hide");
        }
        if(result.length === false){
            $("input[name='username'] ~ .error.length").removeClass("hide");
        }
        else {
            $("input[name='username'] ~ .error.length").addClass("hide");
        }
    })
    elPassword.blur(function (){
        var password = $(this).val();
        $.post(ajaxPath, {password: password}, function(data){
            let result = JSON.parse(data);
            if(result.length === false){
                $("input[name='password'] ~ .error.length").removeClass("hide");
            }
            else {
                $("input[name='password'] ~ .error.length").addClass("hide");
            }
        })
    })
    var password = elPassword.val();
    $.post(ajaxPath, {password: password}, function(data){
        let result = JSON.parse(data);
        if(result.length === false){
            $("input[name='password'] ~ .error.length").removeClass("hide");
        }
        else {
            $("input[name='password'] ~ .error.length").addClass("hide");
        }
    })
})