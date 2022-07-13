<?php 
    /**
     * Plugin Name:       Booknook
     * Plugin URI:        https://www.darrenhewer.com
     * Description:       Embed a custom book cover into your WordPress content 
     * Version:           1.0.0
     * Requires at least: 5.2
     * Requires PHP:      7.2
     * Author:            Darren Hewer
     * Author URI:        https://www.darrenhewer.com
     * License:           GPL v2 or later
     * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
     * Text Domain:       booknook
     */

    // Prevent loading plugin directly
    if (!defined('ABSPATH')) {
        die();
    }

    // Custom plugin constants
    define('BOOKNOOK_DIR', plugin_dir_url(__FILE__));
    define('BOOKNOOK_AUTHOR_MAXLENGTH', 128);
    define('BOOKNOOK_COLOR_MAXLENGTH', 7);
    define('BOOKNOOK_HEXCOLOR_REGEX', '/^#[A-Fa-f0-9]{6}$/');
    define('BOOKNOOK_DEFAULT_COLOR', '#000000');
    define('BOOKNOOK_DEFAULT_TEXTCOLOR', '#FFFFFF');

    /**
     * booknook_enqueue_css
     * Load custom plugin CSS file (front end & admin)
     */
    function booknook_enqueue_css() {
        wp_register_style('booknook_css', BOOKNOOK_DIR.'css/booknook.css', false, '1.0.0' );
        wp_enqueue_style('booknook_css');
    }
    add_action('wp_enqueue_scripts', 'booknook_enqueue_css'); 
    add_action('admin_enqueue_scripts', 'booknook_enqueue_css'); 

    /**
     * booknook_enqueue_scripts
     * Load custom plugin Javascript file (admin only)
     */
    function booknook_enqueue_scripts() {
        wp_register_script('booknook_scripts', BOOKNOOK_DIR.'js/booknook.js', false, '1.0.0' );
        wp_enqueue_script('booknook_scripts');
    }
    add_action('admin_enqueue_scripts', 'booknook_enqueue_scripts'); 

    /**
     * booknook_register_custom_post_type
     * Registers the custom post type
     */
    function booknook_register_custom_post_type() {
        $args = array(
            'labels' => array(
                'name' => __('Books', 'booknook'),
                'singular_name' => __('Book', 'booknook'),
                'not_found' => __('No books found', 'booknook'),
                'add_new' => __('Add New Book', 'booknook'),
                'add_new_item' => __('Add New Book', 'booknook'),
                'edit_item' => __('Edit Book', 'booknook'),
                'view_item' => __('View Book', 'booknook'),
                'view_items' => __('View Book', 'booknook'),
                'item_published' => __('Book published', 'booknook'),
                'item_published_privately' => __('Book published privately', 'booknook'),
                'item_reverted_to_draft' => __('Book reverted to draft', 'booknook'),
                'item_scheduled' => __('Book scheduled', 'booknook'),
                'item_updated' => __('Book updated', 'booknook'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-book',
        );
        register_post_type('booknook', $args);
    }
    add_action('init', 'booknook_register_custom_post_type');

    /**
     * booknook_add_custom_meta
     * Registers the custom meta box for admin panel
     * (Fields are added in booknook_custom_meta_box)
     */
    function booknook_add_custom_meta() {
        add_meta_box(
            'booknook_meta_box_id',
            'Book Custom Meta Data',
            'booknook_custom_meta_box',
            'booknook',
            'booknook',
            'high',
        );
    }
    add_action('add_meta_boxes', 'booknook_add_custom_meta');

    /**
     * booknook_custom_meta_box
     * Display the following fields in meta box in admin panel when adding/editing a booknook:
     * - Author name (booknook_author)
     * - Background color (booknook_color)
     */
    function booknook_custom_meta_box() {
        global $post;
        $post_status = get_post_status($post->ID);
        // Get current values for fields (if any)
        $booknook_author = get_post_meta($post->ID, '_booknook_author', true);
        $booknook_color = get_post_meta($post->ID, '_booknook_color', true);
        $booknook_textcolor = get_post_meta($post->ID, '_booknook_textcolor', true);
        // Sanitize input
        $booknook_author = sanitize_text_field($booknook_author);
        $booknook_color = sanitize_text_field($booknook_color);
        $booknook_textcolor = sanitize_text_field($booknook_textcolor);
        // Nonce safety check field
        wp_nonce_field('booknook_meta_edit', 'booknook_nonce');
        // Display meta form fields on add/edit book screen
        ?>
        <p>
            <label for="booknook_author"><?= __('Author name') ?></label>
            <input type="text" id="booknook_author" name="booknook_author" maxlength="128" style="width:100%;max-width:24em;" value="<?= $booknook_author ?>">
        </p>
        <p>
            <label for="booknook_color"><?= __('Book cover background color') ?></label>
            <input type="color" id="booknook_color" name="booknook_color" maxlength="6" pattern="[A-Fa-f0-9]{6}" style="max-width:6em;" value="<?= $booknook_color ?>">
        </p>
        <p>
            <label for="booknook_textcolor"><?= __('Book cover text color') ?></label>
            <input type="color" id="booknook_textcolor" name="booknook_textcolor" maxlength="6" pattern="[A-Fa-f0-9]{6}" style="max-width:6em;" value="<?= $booknook_textcolor ?>">
        </p>
        <p>
            <?= __('Preview:') ?> <span class="booknook-color-display" style="background-color: <?= $booknook_color ?>;color: <?= $booknook_textcolor ?>"><?= $booknook_textcolor ?></span>
        </p>
        <?php
            if ($post_status !== 'auto-draft') {
        ?>
            <p><?= __('Use this shortcode to add a book cover to your content') ?>: <input type="text" class="booknook-shortcodeCopy" value='[book id="<?= $post->ID ?>"]' size="12"> <button type="button" class="booknook-copyButton button button-sm">Copy</button></p>
        <?php
        }
    }

    /**
     * booknook_move_meta_to_top
     * Adds ability to move meta box to top of edit page under title (also defaults to top)
     */
    function booknook_move_meta_to_top() {
        global $post, $wp_meta_boxes;
        do_meta_boxes(get_current_screen(), 'booknook', $post);
        unset($wp_meta_boxes['post']['booknook']);
    }
    add_action('edit_form_after_title', 'booknook_move_meta_to_top');

    /**
     * booknook_save_meta
     * Stores custom meta fields in postmeta table when book custom post is saved
     */
    function booknook_save_meta($post_id) {
        // Verify nonce (except on initial add of post, ie auto-draft)
        $post_status = get_post_status($post_id);
        if ($post_status !== 'auto-draft') {
            if (!wp_verify_nonce($_POST['booknook_nonce'], 'booknook_meta_edit')) {
                return;
            }
        }
        // Author meta update
        if (array_key_exists('booknook_author', $_POST)) {
            $author = substr(sanitize_text_field($_POST['booknook_author']), 0, BOOKNOOK_AUTHOR_MAXLENGTH);
            update_post_meta($post_id, '_booknook_author', $author);
        }
        // Background color meta update
        if (array_key_exists('booknook_color', $_POST)) {
            $color = substr($_POST['booknook_color'], 0, BOOKNOOK_COLOR_MAXLENGTH);
            // Confirm pattern matches hex color
            preg_match(BOOKNOOK_HEXCOLOR_REGEX, $color, $formatted);
            if (!empty($formatted)) {
                update_post_meta($post_id, '_booknook_color', $color);
            }
        }
        // Text color meta update
        if (array_key_exists('booknook_textcolor', $_POST)) {
            $color = substr($_POST['booknook_textcolor'], 0, BOOKNOOK_COLOR_MAXLENGTH);
            // Confirm pattern matches hex color
            preg_match(BOOKNOOK_HEXCOLOR_REGEX, $color, $formatted);
            if (!empty($formatted)) {
                update_post_meta($post_id, '_booknook_textcolor', $color);
            }
        }
    }
    add_action('save_post', 'booknook_save_meta');

    /**
     * booknook_shortcode
     * Allows insertion of book cover in content via shortcode
     * Format: [book id="##"]
     */
    function booknook_shortcode($atts) {
        // Process attributes, grab id if present
        $a = shortcode_atts(array(
            'id' => '',
        ), $atts);
        // Return if id missing or invalid
        if (empty($a['id'])) {
            return;
        }
        $id = intval($a['id']);
        if (empty($id) || !is_int($id)) {
            return;
        }
        // Get book title, author & color
        $book = get_post($id, ARRAY_A, 'edit');
        if (empty($book)) {
            return;
        }
        $title = $book['post_title'];
        $author = get_post_meta($id, '_booknook_author', true);
        $color = get_post_meta($id, '_booknook_color', true);
        $textcolor = get_post_meta($id, '_textbooknook_color', true);
        // Sanitize fields
        $title = empty($title) ? '' : esc_html($title);
        $author = empty($author) ? '' : esc_html($author);
        // Make sure colors contains a valid hex colors
        preg_match(BOOKNOOK_HEXCOLOR_REGEX, $color, $formatted);
        if (empty($formatted)) {
            $color = BOOKNOOK_DEFAULT_COLOR;
        }
        preg_match(BOOKNOOK_HEXCOLOR_REGEX, $textcolor, $formatted);
        if (empty($formatted)) {
            $textcolor = BOOKNOOK_DEFAULT_TEXTCOLOR;
        }
        // Generate the shortcode template html to return
        ob_start();
        ?>
        <span class="booknook-cover" style="background-color: <?= $color ?>;color: <?= $textcolor ?>">
            <span class="booknook-cover-title"><?= $title ?></span>
            <span class="booknook-cover-author"><?= __('By') ?> <?= $author ?></span>
        </span>
        <?php
        $html = ob_get_clean();
        return $html;
    }
    add_shortcode('book', 'booknook_shortcode');
