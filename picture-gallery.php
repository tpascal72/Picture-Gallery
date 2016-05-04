<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              not available
 * @since             1.0.0
 * @package           Picture_Gallery
 *
 * @wordpress-plugin
 * Plugin Name:       Picture Gallery
 * Plugin URI:        http://wordpresstest-tpascal.rhcloud.com/
 * Description:       Allows for upload of images and definition/deletion of gallery categories
 * Version:           1.0.0
 * Author:            Thomas Pascal
 * Author URI:        not available
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       picture-gallery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-picture-gallery-activator.php
 */
function activate_picture_gallery() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery-activator.php';
  Picture_Gallery_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-picture-gallery-deactivator.php
 */
function deactivate_picture_gallery() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery-deactivator.php';
  Picture_Gallery_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_picture_gallery' );
register_deactivation_hook( __FILE__, 'deactivate_picture_gallery' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_picture_gallery() {

  $plugin = new Picture_Gallery();
  $plugin->run();

  add_action( 'admin_menu', 'picture_gallery_custom_admin_menu' );
}

function picture_gallery_custom_admin_menu() {
    add_menu_page(
        'Picture Gallery',
        'Picture Gallery',
        'manage_options',
        'picture-gallery',
        'wporg_options_page'
    );
}

function wporg_options_page() {
    include 'admin/partials/picture-gallery-admin-display.php';
}


function be_attachment_field_credit( $form_fields, $post ) {

      $form_fields['Category'] = array(

          'label' => 'Image Category',

          'input' => 'text',

          'value' => get_post_meta( $post->ID, 'Category', true ),

          'helps' => 'Will be displayed under appropriate gallery category if selected');

      return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

function be_attachment_field_credit_save( $post, $attachment ) {
      if( isset( $attachment['Category'] ) )
          update_post_meta( $post['ID'], 'Category', $attachment['Category'] );
   
      return $post;
}

add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );

function picture_gallery_display( $atts ){
    global $wpdb;
    $table_name = $wpdb->prefix . "picture_category";
    $query_categories = $wpdb->get_results( 'SELECT name FROM ' . $table_name . ' ORDER BY time DESC');

    $category_names_array = array();

    foreach ( $query_categories as $key=>$category )
    {
      array_push($category_names_array, $category->name );
    }

    $images = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image' , 'posts_per_page' => -1 ) );

    foreach($images->posts as $image)
    {
      $img_src = wp_get_attachment_url($image->ID);
      $image_category = get_post_field('Category', $image->ID);
      $content = get_post_field('post_content', $image->ID);

      if(in_array($image_category, $category_names_array))
      {

        $category_array[$image_category][] = array($img_src, $content);
      }
    }
    /*
    if( $images->have_posts() ){
      while( $images->have_posts() ) {
        $images->the_post();
        $img_src = wp_get_attachment_image_src(get_the_ID(), 'original');
        $image_category = get_post_field('Category', get_the_ID());

        if(in_array($image_category, $category_names_array))
        {
          $category_array[$image_category][] = $img_src[0];
        }
      } 
    }
    */



    /*
    print_r($query_categories);

    foreach($query_categories as $key => $category)
    {
      if($category->name)
      {
        array_splice($query_categories, $key, 1);
      }
    }
    */


?>

<style type="text/css">
.carousel {
    margin-top: 20px;
}
.item .thumb {
  width: 25%;
  cursor: pointer;
  height: 100%;
  float: left; 
}
.item .thumb img {
  width: 100%;
  margin: 2px;
  height:100%;
}
.item img {
  width: 100%;  
}

.fullWidth {
  width:100%;
}

.description-box {
  background-color: rgba(0,0,0,.5);
  padding: 5px;
  border-radius: 5px;
}

.description-box p {
  margin-bottom: 0px;
}

.thumnail-carousel{
  position: relative;
}
.thumnail-carousel:before{
  content: "";
  display: block;
  padding-top: 25%;  /* initial ratio of 1:1*/
}
.thumnail-carousel .item{
  position:  absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}
</style>

<div class="container">
  <div class="row">
    <div class="col-sm-8">

      <?php $current_category_index = 0; ?>
        <?php foreach($category_array as $key => $value) : ?>
            <?php $current_image_index = 0; ?>
            <div class="clearfix">
        <div id="thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" class="carousel" data-interval="false">
            <div class="carousel-inner thumnail-carousel">

                <?php foreach($value as $image_source_value) : ?>
                    <?php if ($current_image_index == 0) : ?>
                      <div class="item active">
                    <?php endif ?>
                        <div data-target="#carousel<?= preg_replace("/[\s]/", "-", $key) ?>" data-slide-to="<?= $current_image_index ?>" class="thumb">
                            <img class="img-responsive" src="<?= $image_source_value[0] ?>">
                        </div>
                    <?php if (((($current_image_index+1) % 4) == 0 && $current_image_index != 0) || $current_image_index == (count($value)-1)) : ?>
                      </div>
                    <?php endif ?>
                    <?php if ((($current_image_index+1) % 4) == 0 && $current_image_index != (count($value)-1)) : ?>
                      <div class="item">
                    <?php endif ?>
                    <?php $current_image_index ++; ?>
                <?php endforeach ?>
                
            </div><!-- /carousel-inner -->
            <a class="left carousel-control" href="#thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a class="right carousel-control" href="#thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div> <!-- /thumbcarousel -->

        <?php
            $current_category_index ++;
        ?>
        </div><!-- /clearfix -->

        <?php endforeach ?>
    
        <?php $current_category_index = 0; ?>
        <?php foreach($category_array as $key => $value) : ?>
            <?php $current_image_index = 0; ?>
        <div id="carousel<?= preg_replace("/[\s]/", "-", $key) ?>" class="carousel slide" data-ride="carousel" data-interval="0">
            <div class="carousel-inner">
                <?php foreach($value as $image_source_value) : ?>
                    <div class="item <?php if ($current_image_index == 0) { echo 'active';} ?>">
                      
                        <img src="<?= $image_source_value[0] ?>" />
                      
                        <div class="carousel-caption description-box">
                          <p><?= $image_source_value[1] ?></p>
                        </div>
                    <?php $current_image_index ++; ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div> 

        <?php
            $current_category_index ++;
        ?>

        <?php endforeach ?>
    </div> <!-- /col-sm-6 -->
    <div class="col-sm-4">
        <h2>Categories</h2>
        <nav class="navbar navbar-default sidebar" role="navigation">
          <div class="container-fluid">
            <ul class="nav navbar-nav">
              <?php $is_first = true; ?>
              <?php foreach ( $query_categories as $key=>$category ) : ?>
                <?php if(array_key_exists($category->name, $category_array)) : ?>
                <?php $active_text = ($is_first ? ' active':'') ?>
                <li class="fullWidth"><button id="button<?= $key ?>" type="button" class="btn btn-default selector-button navbar-btn fullWidth<?= $active_text ?>"><?= $category->name?></button></li>
                <?php endif ?>
                <?php $is_first = false; ?>
              <?php endforeach ?>
            </ul>
        </div>
      </nav>
    </div> <!-- /col-sm-6 -->
  </div> <!-- /row -->
</div> <!-- /container -->

<script type="text/javascript">
jQuery(function($) {
    $(".carousel").hide();

    var first_category = $(".selector-button:first").html();
    $("#carousel" + first_category.replace(/\s/g, '-')).show();
    $("#thumbcarousel" + first_category.replace(/\s/g, '-')).show();

    $(".selector-button").click(function(){
        
        $("button").removeClass("active");
        $(this).addClass("active");

        $(".carousel").hide();
        var this_category = $(this).html();
        $("#carousel" + this_category.replace(/\s/g, '-')).show();
        $("#thumbcarousel" + this_category.replace(/\s/g, '-')).show();
    }); 
});
</script>

    <?php
}

add_shortcode( 'display_picture_gallery', 'picture_gallery_display' );

run_picture_gallery();
?>