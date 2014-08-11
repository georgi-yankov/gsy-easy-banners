<?php
/*
 * Plugin Name: GSY Easy Banners
 * Plugin URI: https://github.com/georgi-yankov/gsy-easy-banners
 * Description: This plugin provides an easy way to add banners
 * Version: 1.0
 * Author: Georgi Yankov
 * Author URI: http://gsy-design.com
 */

/*
 * This plugin creates 'Banners' item in the main admin menu. When you add new
 * banner you will have to:
 * 1. Provide a title for the banner
 * 2. Enter the target URL
 * 3. Upload your banner picture on the featured image option
 * 
 * Below you can see an example of how to use the plugin. Please do pay
 * attention on several things:
 * 1. Always make a query by 'post_type' => 'gsy_banners'
 * 2. Most important field is 'gsy_banner_meta_box_text'
 * 3. the_post_thumbnail('thumbnail') //feel free to use your customized thumbnail
 * 
 * Simple working example:
 *  <?php
    $args = array(
        'post_type' => 'gsy_banners',
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );

    $the_query = new WP_Query($args);
    ?>
    <?php if ($the_query->have_posts()) : while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php if (( has_post_thumbnail() ) AND ( get_post_meta($post->ID, 'gsy_banner_meta_box_text', true) )) : ?>
                <div class="banner">
                    <a href="<?php echo get_post_meta($post->ID, 'gsy_banner_meta_box_text', true) ?>" title="<?php the_title(); ?>">
                        <?php the_post_thumbnail('thumbnail'); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endwhile;
    endif; ?>
    <?php wp_reset_postdata(); ?>
 */

/* =============================================================================
  CREATE CUSTOM POST TYPE OF BANNERS
  =========================================================================== */

function gsy_banners_post_type() {
    $labels = array(
        'name' => _x('Banners', 'post type general name'),
        'singular_name' => _x('Banner', 'post type singular name'),
        'add_new' => _x('Add New', 'gsy_banners'),
        'add_new_item' => __('Add New Banner'),
        'edit_item' => __('Edit Banner'),
        'new_item' => __('New Banner'),
        'all_items' => __('All Banners'),
        'view_item' => __('View Banner'),
        'search_items' => __('Search Banners'),
        'not_found' => __('No banners found'),
        'not_found_in_trash' => __('No banners found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Banners')
    );

    $args = array(
        'labels' => $labels,
        'description' => "This post type is used for banners",
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'banners'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'thumbnail', 'page-attributes')
    );

    register_post_type('gsy_banners', $args);
}

add_action('init', 'gsy_banners_post_type');


/* =============================================================================
  CREATE META BOX FOR CUSTOM POST TYPE OF BANNERS
  =========================================================================== */

add_action('add_meta_boxes', 'gsy_banner_meta_box_add');

function gsy_banner_meta_box_add() {
    add_meta_box('gsy-banner-meta-box-id', 'Banner', 'gsy_banner_meta_box_cb', 'gsy_banners', 'normal', 'high');
}

function gsy_banner_meta_box_cb($post) {
    $values = get_post_custom($post->ID);
    $text = isset($values['gsy_banner_meta_box_text']) ? esc_attr($values['gsy_banner_meta_box_text'][0]) : '';
    wp_nonce_field('gsy_banner_meta_box_nonce', 'meta_box_nonce');
    ?>
    <ol style="color: #e80000">
        <li>Upload your banner image by using "Featured Image" on the right.</li>
        <li>Use "Order" on the right to order it.</li>
        <li>Place your url address below.</li>
    </ol>
    <p>
        <label for="gsy_banner_meta_box_text">URL:</label>
        <input style="width: 200px;" type="text" name="gsy_banner_meta_box_text" id="gsy_banner_meta_box_text" value="<?php echo $text; ?>" />
    </p>
    <?php
}

add_action('save_post', 'gsy_banner_meta_box_save');

function gsy_banner_meta_box_save($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'gsy_banner_meta_box_nonce'))
        return;

    // if our current user can't edit this post, bail
    if (!current_user_can('edit_post'))
        return;

    // now we can actually save the data
    $allowed = array(
        'a' => array(// on allow a tags
            'href' => array() // and those anchords can only have href attribute
        )
    );

    // Probably a good idea to make sure your data is set
    if (isset($_POST['gsy_banner_meta_box_text']))
        update_post_meta($post_id, 'gsy_banner_meta_box_text', wp_kses($_POST['gsy_banner_meta_box_text'], $allowed));
}