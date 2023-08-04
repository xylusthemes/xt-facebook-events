// Color Picker
jQuery(document).ready(function($){
    $('.xtfe_color_field').each(function(){
        $(this).wpColorPicker();
    });
});

//Shortcode Copy Text
jQuery(document).ready(function($){
    $(document).on("click", ".xtfe-btn-copy-shortcode", function() { 
        var trigger = $(this);
        $(".xtfe-btn-copy-shortcode").removeClass("text-success");
        var $tempElement = $("<input>");
        $("body").append($tempElement);
        var copyType = $(this).data("value");
        $tempElement.val(copyType).select();
        document.execCommand("Copy");
        $tempElement.remove();
        $(trigger).addClass("text-success");
        var $this = $(this),
        oldText = $this.text();
        $this.attr("disabled", "disabled");
        $this.text("Copied!");
        setTimeout(function(){
            $this.text("Copy");
            $this.removeAttr("disabled");
        }, 800);
    });
});