function function_change_image() {
  var img_url = 'https://escape.ripplemarketing.com.au/wp-content/uploads/2021/10/Screen-Shot-2021-10-10-at-12.25.07-pm.png';
  jQuery(".seconday_main_img").attr("src", img_url);

  var html = '<a href="#" ><img src="'+img_url+'" alt="Show/Hide Image" width="100%" height="auto"></a>';
  jQuery(".seconday_main_img").replaceWith(html);
}
