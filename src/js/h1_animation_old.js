$(function(){
    $(".first_button").fadeIn(1000);
    $(".second_button").fadeIn(1000, function(){
        $("h1").slideDown(1000)
    });
});