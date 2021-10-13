jQuery( document ).ready(function() { 
var group_id = 0;
var group_title = '';
  jQuery.getJSON( "", { check_session: "" } )
  .done(function( data ) {
    if(data != '') {
      var group_id = data.Group_id;
      var group_title = data.Group_title;
      console.log("group_id"+group_id);
    }
    console.log("group_id"+group_id);
    if(group_id != '' && group_id > 0) {
    } else {
      var message = '';
      if( jQuery('meta[name="error_messsage"]').length ) {
        message = jQuery('meta[name="error_messsage"]').attr("content");
      }

      jQuery("body").append('<form method="post" name="verify_group" id="verify_group"><div id="ask_group_code" style="width: 100%;height: 100%;position: fixed;background: rgba(113, 148, 48, 0.3);top: 0;left: 0;z-index: 10;text-align: center;vertical-align: middle;padding: 9px 0;font-weight: bold;color: #fff;z-index: 1000;border-radius: 10px;font-size: 50px;"><div class="center_fix_verticle" style="position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);"><span class="show_message" style="font-size: 20px;color: red;">'+message+'</span><input type="text" name="group_code" class="form_control" style="    width: 100%;    text-align: center;    margin: 0 auto;" placeholder="Enter Code"><input type="submit" name="group_code_submit" class="form_control" style="width: 100%;text-align: center;margin: 0 auto;color: #ffffff;background-color: #719430;border-color: #507210;font-size: 28px;" placeholder="Enter Code"></div>    </div></form>');
    }
  })

})
