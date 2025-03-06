$(function (){
    setTimeout(function (){
        let headline = $(".content > h1").text();
        let ajaxPath = "../../src/Ajax.php";
        $.post(ajaxPath,
            {
                'type': 'track',
                'article': headline
            },
            function (){
                console.log('visited');
            }
        );
    }, 10000);
});