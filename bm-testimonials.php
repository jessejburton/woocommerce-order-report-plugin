<?php
/**
 * @package SSCY
 */
/*
Plugin Name: Burton Media - Testimonials Plugin
Plugin URI: https://github.com/jessejburton/bm-testimonials
Description: Add testimonials to a page based on sections
Author: Jesse James Burton
Author URI: http://www.burtonmediainc.com
Version: 1.0
License: GPLv2 or later
Text Domain: burtonmedia
*/

/*
  ==========================================================
  Exit if file is called directly
  ==========================================================
*/  
defined( 'ABSPATH' ) or die( 'Hey! Get outta here!' );

/*
  =======================================================
  Custom Post Types
  =======================================================
*/
function bm_testimonial_custom_post_type(){
  $singular = "Testimonial";
  $plural = "Testimonials";
  
  $labels = array(
    'name'        => $plural,
    'singular_name'   => $singular,
    'add_name'      => 'Add New',
    'add_new_item'    => 'Add New ' . $singular,
    'edit'        => 'Edit',
    'edit_item'     => 'Edit ' . $singular,
    'new_item'      => 'New ' . $singular,
    'view'        => 'View ' . $singular,
    'view_item'     => 'View ' . $singular,
    'search_term'   => 'Search ' . $plural,
    'parent'      => 'Parent ' . $singular,
    'not_found'     => 'No ' . $plural . ' found',
    'not_found_in_trash'=> 'No ' . $plural . ' found in trash'
  );
  
  $args = array( 
    'labels'            => $labels,
    'public'            => true,
    'has_archive'       => true,
    'publicly_queyable' => true,
    'query_var'         => true,
    'rewrite'           => true,
    'capability_type'   => 'post',
    'hierarchical'      => true,
    'supports'           => array (
      'title',
      'editor',
      'thumbnail'
    ),
    'taxonomies'        => array( 'section' ),
    'menu_position'     => 6,
    'exlude_from_search'=> false
  );
  register_post_type( 'bm_testimonial', $args );
}
add_action( 'init', 'bm_testimonial_custom_post_type' );

/* Custom Testimonial Taxonomy */  
function bm_testimonial_taxonomy() { 
    // Labels part for the GUI
    $labels = array(
        'name' => _x( 'Section', 'taxonomy general name' ),
        'singular_name' => _x( 'Section', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Sections' ),
        'popular_items' => __( 'Popular Sections' ),
        'all_items' => __( 'All Sections' ),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Edit Section' ), 
        'update_item' => __( 'Update Section' ),
        'add_new_item' => __( 'Add New Section' ),
        'new_item_name' => __( 'New Section Name' ),
        'separate_items_with_commas' => __( 'Separate sections with commas' ),
        'add_or_remove_items' => __( 'Add or remove sections' ),
        'choose_from_most_used' => __( 'Choose from the most used sections' ),
        'menu_name' => __( 'Sections' ),
    ); 
    
    // Now register the non-hierarchical taxonomy like tag
    register_taxonomy('section','bm_testimonial',array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array( 'slug' => 'section' ),
    ));
}
add_action( 'init', 'bm_testimonial_taxonomy', 0 );

/* Update the title text to ask for the author name instead of the post title */
function custom_enter_title( $input ) {

    global $post_type;

    if( is_admin() && 'Enter title here' == $input && 'bm_testimonial' == $post_type )
        return 'Authors Name';

    return $input;
}
add_filter('gettext','custom_enter_title');

/*
  =======================================================
  Custom Post Type Meta Boxes
  =======================================================
*/

function bm_testimonial_custom_meta_boxes(){
  require_once( 'bm-testimonials-fields.php' );    

  // Define the custom attachment for pages
  add_meta_box(
      'bm_testimonials_url',   // Unique ID
      'URL ',                   // Box Title
      'bm_testimonials_url',   // Content Callback
      'bm_testimonial'         // Post Type
  );
}
add_action( 'add_meta_boxes', 'bm_testimonial_custom_meta_boxes' );

function bm_testimonial_shortcode( $atts = [] )
{
    $section = $atts['section'];

    ob_start();

        wp_reset_query();
        $args = array('post_type' => 'bm_testimonial',
            'tax_query' => array(
                array(
                    'taxonomy' => 'section',
                    'field' => 'slug',
                    'terms' => array( $section ),
                ),
            ),
            );

            echo '<div class="bm_testimonials">';
                $loop = new WP_Query($args);

                if($loop->have_posts()) {

                    echo '<div class="bm-testimonials">';

                        while($loop->have_posts()) : 
                            $loop->the_post();
                                ?>
                                    <blockquote class="bm-testimonial">
                                        <p class="bm-testimonial__content"><?php echo the_content(); ?></p>
                                        <div class="bm-testimonial__author">
                                            <?php if( isset($post) ){ ?>
                                                <div class="bm-testimonial__url">
                                                    <a class="bm-testimonial__url-link" href="#">http://www.burtonmediainc.com</a>
                                                </div>
                                            <?php } ?>
                                            <div class="bm-testimonial__author-name">~ <?php echo the_title(); ?></div>
                                        </div>
                                    </blockquote>
                                <?php
                        endwhile;

                    echo '</div>';
                }

    return ob_get_clean();
}
add_shortcode('bm_testimonial', 'bm_testimonial_shortcode');

/*
  =======================================================
  Save Custom Post Type Meta Data
  =======================================================
*/
function bm_testimonial_save_meta_data( $post_id ) {
    // !!!!!NONCE NOT WORKING NEED TO FIX!!!!!!

    /*
    // Checks save status
    $is_autosave  = wp_is_post_autosave( $post_id );
    $is_revision  = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST['sscy_jobs_nonce'] ) && wp_verify_nonce( $_POST['sscy_jobs_nonce'], basename(__FILE__) ) ) ? 'true' : 'false';  
      
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
      die();
      return; 
    } 

    */  

    if( isset($_POST['bm_testimonial_url']) ){  
      update_post_meta( $post_id, 'bm_testimonial_url', $_POST['bm_testimonial_url'] );
    }

}
add_action( 'save_post', 'bm_testimonial_save_meta_data' );

