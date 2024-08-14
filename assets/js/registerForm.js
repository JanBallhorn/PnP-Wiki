import {checkDuplicate, checkLength} from "./formCheck.js";
$(function (){
    let elEmail = $("input[name='email']");
    let elUsername = $("input[name='username']");
    let elPassword = $("input[name='password']");
    elEmail.blur(function (){
        checkDuplicate($(this), 'email', $(this).val(), 'users');
    });
    checkDuplicate(elEmail, 'email', elEmail.val(), 'users');
    elUsername.blur(function (){
        checkDuplicate($(this), 'username', $(this).val(), 'users');
        checkLength($(this), $(this).val(), 4);
    });
    checkDuplicate(elUsername, 'username', elUsername.val(), 'users');
    checkLength(elUsername, elUsername.val(), 4);
    elPassword.blur(function (){
        checkLength($(this), $(this).val(), 6)
    })
})
