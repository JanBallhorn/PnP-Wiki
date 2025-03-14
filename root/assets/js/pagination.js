$(function (){
    let pagination = $(".pagination");
    let form = pagination.siblings("form");
    let pageButtons = pagination.find($(".page"));
    let pageInput = $("input[name='page']");
    pageButtons.each(function (){
        if($(this.disabled === false)){
            $(this).on("click", function (){
                pageInput.val($(this).val());
                form.submit();
            });
        }
    });
});