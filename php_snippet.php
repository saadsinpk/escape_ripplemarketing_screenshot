 
add_action("wp_ajax_canvas_to_gallery", "canvas_to_gallery");
add_action("wp_ajax_nopriv_canvas_to_gallery", "canvas_to_gallery");

function canvas_to_gallery() {
  if(isset($_POST['dataURL'])) {
      $filename  = 'ScreenShot_img_'.time();
      sidtechno_save_image($_POST['dataURL'], $filename);
  }
  exit();
}

add_action( 'init', 'my_script_enqueuer' );

function my_script_enqueuer() {
  if(isset($_GET['check_session'])) {
    if(!session_id()) {
        session_start();
    }
    echo json_encode(array('Group_id'=>$_SESSION['group_id'], 'Group_title'=>$_SESSION['group_title']));
    exit();
  }
  if(isset($_POST['group_code_submit'])) {
    $group_code = $_POST['group_code'];
    global $wpdb;
    $results = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_key = 'sidtechno_group_code' AND meta_value = '".$group_code."'", ARRAY_A );
    $error = 1;

    if(count($results) > 0) {
      foreach ($results as $key => $value) {
        if (get_post_status($value['post_id']) == 'publish' ) {
          if(!session_id()) {
              session_start();
          }
          $_SESSION['group_id'] = $value['post_id'];
          $_SESSION['group_title'] = get_the_title($value['post_id']);
          $error = 0;
          break;
        }
      }
    }
    if($error == 1) {
      echo '<meta name="error_messsage" content="Sorry! Code does not match!">';
    }
  }
   wp_localize_script( 'canvas_to_gallery', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( 'canvas_to_gallery' );
}
function sidtechno_save_image( $base64_img, $title ) {

  // Upload dir.
  $upload_dir  = wp_upload_dir();
  $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

  $img             = str_replace( 'data:image/png;base64,', '', $base64_img );
  $img             = str_replace( ' ', '+', $img );
  $decoded         = base64_decode( $img );
  $filename        = $title . '.png';
  $file_type       = 'image/png';
  $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

  // Save the image in the uploads directory.
  $upload_file = file_put_contents( $upload_path . $hashed_filename, $decoded );

  $attachment = array(
    'post_mime_type' => $file_type,
    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
    'post_content'   => '',
    'post_status'    => 'inherit',
    'guid'           => $upload_dir['url'] . '/' . basename( $hashed_filename )
  );
  $attach_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $hashed_filename );

  // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
  require_once( ABSPATH . 'wp-admin/includes/image.php' );
    
  // Generate the metadata for the attachment, and update the database record.
  $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_dir['path'] . '/' . $hashed_filename );
  wp_update_attachment_metadata( $attach_id, $attach_data );

  if(!session_id()) {
      session_start();
  }
  $old_gallery_data = get_post_meta($_SESSION['group_id'], '_gallery_data', true);

  if(!empty($old_gallery_data)) {
    $new_gallery_data = $old_gallery_data.','.$attach_id;
  } else {
    $new_gallery_data = $attach_id;
  }

  update_post_meta($_SESSION['group_id'], '_gallery_data', $new_gallery_data);
  echo '<a href="'.wp_get_attachment_image_src($attach_id, 'full')[0].'" style="margin-right:1%" data-lightbox="roadtrip">'.wp_get_attachment_image($attach_id).'</a>';
}

function sidtechno_custom_post_type() {
 
    $labels = array(
        'name'                => _x( 'Escape Groups', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Escape Group', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Escape Groups', 'twentytwenty' ),
        'parent_item_colon'   => __( 'Parent Escape Group', 'twentytwenty' ),
        'all_items'           => __( 'All Escape Groups', 'twentytwenty' ),
        'view_item'           => __( 'View Escape Group', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Escape Group', 'twentytwenty' ),
        'add_new'             => __( 'Add New', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Escape Group', 'twentytwenty' ),
        'update_item'         => __( 'Update Escape Group', 'twentytwenty' ),
        'search_items'        => __( 'Search Escape Group', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
    );
     
    $args = array(
        'label'               => __( 'Escape Groups', 'twentytwenty' ),
        'description'         => __( 'Custom Escape Group', 'twentytwenty' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array(  ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );
     
    // Registering your Custom Post Type
    register_post_type( 'escape_group', $args );
    add_action( 'add_meta_boxes', 'sidtechno_add_post_meta_boxes' );
    add_action( 'save_post', 'sidtechno_save_post_class_meta', 10, 2 );

}
add_action( 'init', 'sidtechno_custom_post_type', 0 );

function sidtechno_add_post_meta_boxes() {

    add_meta_box(
        'sidtechno-escape-group',      // Unique ID
        esc_html__( 'Group Detail', 'example' ),    // Title
        'sidtechno_post_class_meta_box',   // Callback function
        'escape_group',         // Admin page (or post type)
        'normal',         // Context
        'default'         // Priority
    );
}

function sidtechno_post_class_meta_box( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'sidtechno_post_class_nonce' );
    echo '<p>
        <label for="sidtechno-option-url"><b>Group Code</b></label>
        <br />';
        echo '<input class="widefat" type="text" name="sidtechno_group_code" id="sidtechno_group_code" value="'.esc_attr( get_post_meta( $post->ID, 'sidtechno_group_code', true ) ).'" size="30" placeholder="Group Code" />
    </p>';
    echo '<p>
        <label for="sidtechno-option-url"><b>Group Gallery images</b></label>
        <br />';
        $gallery_images = get_post_meta($_GET['post'], '_gallery_data', true);
        if(!empty($gallery_images)) {
          if( strpos($gallery_images, ',') === false ) {
            $return .= '<a href="'.wp_get_attachment_image_src($value, 'full')[0].'" style="margin-right:1%" data-lightbox="roadtrip">'.wp_get_attachment_image($value).'</a>';
          } else {
            $explode_gallery_images = explode(",", $gallery_images);
            foreach ($explode_gallery_images as $key => $value) {
              $return .= '<a href="'.wp_get_attachment_image_src($value, 'full')[0].'" style="margin-right:1%" data-lightbox="roadtrip">'.wp_get_attachment_image($value).'</a>';
            }
          }
        }
    echo '</p>';

}

function sidtechno_save_post_class_meta( $post_id, $post ) {

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['sidtechno_post_class_nonce'] ) || !wp_verify_nonce( $_POST['sidtechno_post_class_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;


  $sidtechno_group_code = 'sidtechno_group_code';
  $new_sidtechno_group_code = ( isset( $_POST['sidtechno_group_code'] ) ? $_POST['sidtechno_group_code'] : ’ );
  $sidtechno_group_code = 'sidtechno_group_code';


  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $sidtechno_group_code, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_sidtechno_group_code && ’ == $meta_value )
    add_post_meta( $post_id, $sidtechno_group_code, $new_sidtechno_group_code, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_sidtechno_group_code && $new_sidtechno_group_code != $meta_value )
    update_post_meta( $post_id, $sidtechno_group_code, $new_sidtechno_group_code );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( ’ == $new_sidtechno_group_code && $meta_value )
    delete_post_meta( $post_id, $sidtechno_group_code, $meta_value );


}

function sidtechno_show_group_gallery() { 
  if(!is_admin()) {
    $return = '';
    if(!session_id()) {
        session_start();
    }
    $gallery_images = array();
    $gallery_images = get_post_meta($_SESSION['group_id'], '_gallery_data', true);
    if(!empty($gallery_images)) {
      if( strpos($gallery_images, ',') === false ) {
        $return .= '<a href="'.wp_get_attachment_image_src($gallery_images, 'full')[0].'" style="margin-right:1%" data-lightbox="roadtrip">'.wp_get_attachment_image($gallery_images).'</a>';
      } else {
        $explode_gallery_images = explode(",", $gallery_images);
        foreach ($explode_gallery_images as $key => $value) {
          $return .= '<a href="'.wp_get_attachment_image_src($value, 'full')[0].'" style="margin-right:1%" data-lightbox="roadtrip">'.wp_get_attachment_image($value).'</a>';
        }
      }
    }
    return $return;
  }
} 

// register shortcode
add_shortcode('show_group_gallery', 'sidtechno_show_group_gallery'); 
