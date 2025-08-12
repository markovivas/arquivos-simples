<?php
/*
Plugin Name: Simple File List
Plugin URI: https://yourwebsite.com/simple-file-list
Description: A simple file management system for WordPress with upload capabilities.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
License: GPL2
*/

// Security check
defined('ABSPATH') or die('No script kiddies please!');

// Define constants
define('SFL_VERSION', '1.0');
define('SFL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SFL_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/simple-file-list/');
define('SFL_UPLOAD_URL', content_url() . '/uploads/simple-file-list/');

// Create upload directory if it doesn't exist
if (!file_exists(SFL_UPLOAD_DIR)) {
    wp_mkdir_p(SFL_UPLOAD_DIR);
}

// Include required files
require_once SFL_PLUGIN_DIR . 'includes/admin.php';
require_once SFL_PLUGIN_DIR . 'includes/frontend.php';
require_once SFL_PLUGIN_DIR . 'includes/functions.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'sfl_activate_plugin');
register_deactivation_hook(__FILE__, 'sfl_deactivate_plugin');

function sfl_activate_plugin() {
    // Create database table if needed
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'simple_file_list';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        file_name varchar(255) NOT NULL,
        file_path varchar(255) NOT NULL,
        file_url varchar(255) NOT NULL,
        file_size varchar(20) NOT NULL,
        file_type varchar(100) NOT NULL,
        description text,
        category varchar(100),
        upload_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        user_id bigint(20) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add default options
    add_option('sfl_max_files', 10);
    add_option('sfl_max_size', 64); // in MB (alterado para 64MB)
    add_option(
        'sfl_allowed_types',
        'jpg,jpeg,png,tif,pdf,mov,mp4,mp3,zip,doc,docx,xls,xlsx,ppt,pptx'
        // Adicionadas extensões do Word, Excel e PowerPoint
    );
}

function sfl_deactivate_plugin() {
    // Clean up if needed
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'sfl_enqueue_scripts');
function sfl_enqueue_scripts() {
    wp_enqueue_style('sfl-style', SFL_PLUGIN_URL . 'assets/css/style.css');
    wp_enqueue_style('dashicons'); // Adicione esta linha
    wp_enqueue_script('sfl-script', SFL_PLUGIN_URL . 'assets/js/script.js', array('jquery'), SFL_VERSION, true);
    
    // Localize script for AJAX
    wp_localize_script('sfl-script', 'sfl_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sfl-nonce')
    ));
}

// Add admin menu
add_action('admin_menu', 'sfl_admin_menu');
function sfl_admin_menu() {
    add_menu_page(
        'Simple File List',
        'File List',
        'manage_options',
        'simple-file-list',
        'sfl_admin_page',
        'dashicons-media-default',
        30
    );
    
    add_submenu_page(
        'simple-file-list',
        'Settings',
        'Settings',
        'manage_options',
        'simple-file-list-settings',
        'sfl_settings_page'
    );
}

// Shortcode for frontend display
add_shortcode('simple_file_list', 'sfl_display_file_list');
function sfl_display_file_list($atts) {
    ob_start();
    
    if (is_user_logged_in()) {
        sfl_render_upload_form();
    }
    
    sfl_render_file_list();
    
    return ob_get_clean();
}

// Handle file upload
add_action('wp_ajax_sfl_upload_file', 'sfl_handle_file_upload');
add_action('wp_ajax_nopriv_sfl_upload_file', 'sfl_handle_file_upload');
function sfl_handle_file_upload() {
    // Verify nonce
    check_ajax_referer('sfl-nonce', 'security');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to upload files.');
    }
    
    if (!isset($_FILES['sfl_file_upload'])) {
        wp_send_json_error('No file was uploaded.');
    }
    
    $file = $_FILES['sfl_file_upload'];
    $file_name = sanitize_file_name($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Check for upload errors
    if ($file_error !== UPLOAD_ERR_OK) {
        wp_send_json_error('Upload error: ' . $file_error);
    }
    
    // Check file size
    $max_size = get_option('sfl_max_size') * 1024 * 1024; // Convert MB to bytes
    if ($file_size > $max_size) {
        wp_send_json_error('File size exceeds maximum allowed size.');
    }
    
    // Check file type
    $allowed_types = explode(',', get_option('sfl_allowed_types'));
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        wp_send_json_error('File type not allowed.');
    }
    
    // Generate unique filename if file exists
    $counter = 1;
    $original_name = pathinfo($file_name, PATHINFO_FILENAME);
    $new_file_name = $file_name;
    
    while (file_exists(SFL_UPLOAD_DIR . $new_file_name)) {
        $new_file_name = $original_name . '-' . $counter . '.' . $file_ext;
        $counter++;
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, SFL_UPLOAD_DIR . $new_file_name)) {
        // Save file info to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'simple_file_list';
        
        $data = array(
            'file_name' => $new_file_name,
            'file_path' => SFL_UPLOAD_DIR . $new_file_name,
            'file_url' => SFL_UPLOAD_URL . $new_file_name,
            'file_size' => size_format($file_size, 2),
            'file_type' => $file_ext,
            'description' => sanitize_text_field($_POST['description']),
            'category' => sanitize_text_field($_POST['category']),
            'user_id' => get_current_user_id()
        );
        
        $wpdb->insert($table_name, $data);
        
        wp_send_json_success('File uploaded successfully.');
    } else {
        wp_send_json_error('Error moving uploaded file.');
    }
}

// Handle file deletion
add_action('wp_ajax_sfl_delete_file', 'sfl_handle_file_delete');
function sfl_handle_file_delete() {
    // Verify nonce and permissions
    check_ajax_referer('sfl-nonce', 'security');
    
    if (!is_user_logged_in() || !current_user_can('upload_files')) {
        wp_send_json_error('You do not have permission to delete files.');
    }
    
    $file_id = intval($_POST['file_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_file_list';
    
    // Get file info
    $file = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $file_id
    ));
    
    if (!$file) {
        wp_send_json_error('File not found.');
    }
    
    // Delete file from server
    if (file_exists($file->file_path)) {
        unlink($file->file_path);
    }
    
    // Delete record from database
    $wpdb->delete($table_name, array('id' => $file_id));
    
    wp_send_json_success('File deleted successfully.');
}