<?php 
require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/includes/vdgallery-option-fields.php';

/**
* Save meta boxes.
*/
add_action( 'save_post', 'vdgallery_save_post_class_meta', 10, 2 );
function vdgallery_save_post_class_meta( $post_id, $post ) {

  global $post; 

  if (isset($post->post_type) && $post->post_type != 'vdgallery') {
      return;
  }

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['vdgallery_post_nonce'] ) || !wp_verify_nonce( $_POST['vdgallery_post_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_meta_value = ( isset( $_POST['vdgaleri-post'] ) && is_array( $_POST['vdgaleri-post'] ) ? $_POST['vdgaleri-post'] : array() );

  if ( isset( $new_meta_value['media'] ) && is_array( $new_meta_value['media'] ) ) {
    $new_meta_value['media'] = array_map( 'absint', $new_meta_value['media'] );
  }

  $posted_options = ( isset( $new_meta_value['option'] ) && is_array( $new_meta_value['option'] ) ) ? $new_meta_value['option'] : array();
  $new_meta_value['option'] = array_merge(
    vdgallery_sanitize_options( $posted_options, vdgallery_get_gallery_option_fields() ),
    vdgallery_sanitize_options( $posted_options, vdgallery_get_slideshow_option_fields() )
  );
  $new_meta_value['gallery_use_global_options'] = isset( $new_meta_value['gallery_use_global_options'] ) ? absint( $new_meta_value['gallery_use_global_options'] ) : 0;
  $new_meta_value['slideshow_use_global_options'] = isset( $new_meta_value['slideshow_use_global_options'] ) ? absint( $new_meta_value['slideshow_use_global_options'] ) : 0;

  /* Get the meta key. */
  $meta_key = 'vdgaleri';

  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* Update meta. */
  update_post_meta( $post_id, $meta_key, $new_meta_value );

} 

/**
 * Register meta boxes.
 */
function vdgallery_register_meta_boxes() {
  add_meta_box(
       'vdgallery-meta', 
       'Detail Galeri', 
       'vdgallery_display_callback', 
       'vdgallery',
       'normal',
       'high',
       ''
  );
}
add_action( 'add_meta_boxes', 'vdgallery_register_meta_boxes' );

/**
* Meta box display callback.
*
* @param WP_Post $post Current post object.
*/
function vdgallery_display_callback( $post ) {
  $getId        = isset($_GET['post'])?$_GET['post']:'';
  $datagaleri   = get_post_meta( $post->ID, 'vdgaleri', true );
  $option_values = ( $datagaleri && isset( $datagaleri['option'] ) && is_array( $datagaleri['option'] ) ) ? $datagaleri['option'] : array();
  $gallery_use_global_options = ( $datagaleri && isset( $datagaleri['gallery_use_global_options'] ) ) ? $datagaleri['gallery_use_global_options'] : 1;
  $slideshow_use_global_options = ( $datagaleri && isset( $datagaleri['slideshow_use_global_options'] ) ) ? $datagaleri['slideshow_use_global_options'] : 1;

  // print_r($datagaleri);
  wp_nonce_field( basename( __FILE__ ), 'vdgallery_post_nonce' );
  ?>

  <div class="vdgallery-tabs">
    <ul class="vdgallery-tabs-link">
      <li>
        <span class="tabs-link" data-target="tab-1">Galeri</span>
      </li>
      <li>
        <span class="tabs-link" data-target="tab-2">Galeri Option</span>
      </li>
      <li>
        <span class="tabs-link" data-target="tab-3">Slideshow Option</span>
      </li>
    </ul>
    <div class="vdgallery-tabs-opt">
      <div class="tabs-item" data-target="tab-1">
        <div class="vdgallery-main">
          <?php if($datagaleri && isset( $datagaleri['media'] ) && is_array( $datagaleri['media'] )): ?>
            <?php foreach($datagaleri['media'] as $galeri): ?>
              <?php $idunik = uniqid(); ?>
              <div class="vdgallery-image vdgallery-image-<?php echo $idunik; ?>" data-node="<?php echo $idunik; ?>" data-id="<?php echo $galeri; ?>">
                  <input name="vdgaleri-post[media][]" value="<?php echo $galeri; ?>" type="hidden">
                  <img src="<?php echo wp_get_attachment_image_src($galeri)[0]; ?>" alt="">
                  <div class="vdgallery-option">
                      <span class="vdgallery-remove dashicons dashicons-no-alt"></span>
                  </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="box-vdgallery-clone">
          <span class="button vdgallery-add">+ Edit Galeri</span> 
        </div>
      </div>

      <div class="tabs-item" data-target="tab-2">

        <table class="form-table vdgallery-option-table" role="presentation">
          <tr>
            <th scope="row">
              <label for="vdgallery_gallery_use_global_options">Gunakan Global Option</label>
            </th>
            <td>
              <select id="vdgallery_gallery_use_global_options" name="vdgaleri-post[gallery_use_global_options]" class="regular-text">
                <option value="0" <?php selected( $gallery_use_global_options, 0 ); ?>>Tidak</option>
                <option value="1" <?php selected( $gallery_use_global_options, 1 ); ?>>Ya</option>
              </select><br/>
              <small class="description">Jika ya, maka opsi galeri akan menggunakan pengaturan global.</small>
            </td>
          </tr>
          <?php vdgallery_render_option_fields( vdgallery_get_gallery_option_fields(), $option_values ); ?>
        </table>

      </div>
      <div class="tabs-item" data-target="tab-3">
        <table class="form-table vdgallery-option-table" role="presentation">
          <tr>
            <th scope="row">
              <label for="vdgallery_slideshow_use_global_options">Gunakan Global Option</label>
            </th>
            <td>
              <select id="vdgallery_slideshow_use_global_options" name="vdgaleri-post[slideshow_use_global_options]" class="regular-text">
                <option value="0" <?php selected( $slideshow_use_global_options, 0 ); ?>>Tidak</option>
                <option value="1" <?php selected( $slideshow_use_global_options, 1 ); ?>>Ya</option>
              </select><br/>
              <small class="description">Jika ya, maka opsi slideshow akan menggunakan pengaturan global.</small>
            </td>
          </tr>
          <?php vdgallery_render_option_fields( vdgallery_get_slideshow_option_fields(), $option_values ); ?>
        </table>
      </div>
    </div>
  </div>

  <?php
}


/**
 * Register meta boxes.
 */
add_action( 'add_meta_boxes', 'vdgallery_register_side_boxes' );
function vdgallery_register_side_boxes() {
  add_meta_box(
       'vdgallery-meta-side', 
       'Shortcode Galeri', 
       'vdgallery_display_sideback', 
       'vdgallery',
       'side',
       'default',
       ''
  );
}

function vdgallery_display_sideback() {
  $getId        = isset($_GET['post'])?$_GET['post']:'';
  ?>
  <?php if($getId): ?>
      <div class="vdgallery-shortcode">
        <table>
          <tr>
              <td>Gallery</td>
              <td> : </td>
              <td><span>[vdgallery id="<?php echo $getId; ?>"]</span></td>
          </tr>
          <tr>
              <td>Slide</td>
              <td> : </td>
              <td><span>[vdgalleryslide id="<?php echo $getId; ?>"]</span></td>
          </tr>
        </table>
      </div>
  <?php endif; ?>
  <?php
}
