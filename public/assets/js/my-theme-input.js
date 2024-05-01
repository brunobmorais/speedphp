$(function () {

    $(".field-wrapper .field-placeholder").on("click", function () {
        $(this).closest(".field-wrapper").find("input").focus();
    });
    $(".field-wrapper input").on("blur input", function () {
        var value = $.trim($(this).val());
        if (value) {
            $(this).closest(".field-wrapper").addClass("hasValue");
        } else {
            $(this).closest(".field-wrapper").removeClass("hasValue");
        }
    });

    setTimeout(function() {
        $(".field-wrapper input").each(function() {
            var elem = $(this);
            if (elem.val()) verificaAlteracaoInput(elem);
        })
    }, 100);

    $(".field-wrapper .field-placeholder").on("click", function () {
        $(this).closest(".field-wrapper").find("select").focus();
    });
    $(".field-wrapper select").on("blur keyup change", function () {
        var value = $.trim($(this).val());
        if (value) {
            $(this).closest(".field-wrapper").addClass("hasValue");
        } else {
            $(this).closest(".field-wrapper").removeClass("hasValue");
        }
    });

});

function verificaAlteracaoInput(text){
    var value = $.trim(text.val());
    //console.log(value);
    if (value) {
        text.closest(".field-wrapper").addClass("hasValue");
    } else {
        text.closest(".field-wrapper").removeClass("hasValue");
    }
}