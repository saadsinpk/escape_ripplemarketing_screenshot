jQuery('#take_screenshot').on('click', 'a' ,function(){
  jQuery("body").append('<div id="loading" style="width: 100%;height: 100%;position: fixed;background: rgba(113, 148, 48, 0.3);top: 0;left: 0;z-index: 10;text-align: center;vertical-align: middle;padding: 9px 0;font-weight: bold;color: #fff;z-index: 1000;border-radius: 10px;font-size: 50px;"><div class="center_fix_verticle" style="position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">Loading</div></div>')

  html2canvas(document.querySelector("#screenshot_div")).then(canvas => {
    var dataURL = canvas.toDataURL();
    jQuery.ajax({
      type : "post",
      url : avia_framework_globals.ajaxurl,
      data : {action: "canvas_to_gallery", dataURL: dataURL},
      success: function(response) {
        jQuery("#gallery_div .avia_textblock").append(response);
        jQuery("#loading").remove();
      }
    })   
  });
  return false;
})
