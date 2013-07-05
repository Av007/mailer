$(function () {
	$("select").ikSelect();

    var elements = document.getElementsByTagName("INPUT");
    for (var i = 0; i < elements.length; i++) {
        elements[i].oninvalid = function(e) {
            e.target.setCustomValidity("");
            if (!e.target.validity.valid) {
                e.target.setCustomValidity("Это поле обязательно");
            }
        };
        elements[i].oninput = function(e) {
            e.target.setCustomValidity("");
        };
    }

    // dropdown
    $("#more").click(function() {
        $(this).toggleClass("active");
        $(this).next(".dropdown").toggle();
        $("#arrow span").toggleClass("arrdown").toggleClass("arrup");

        return false;
    });
});