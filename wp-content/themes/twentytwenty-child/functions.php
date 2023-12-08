<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;


// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')) :
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('chld_thm_cfg_parent_css')) :
    function chld_thm_cfg_parent_css()
    {
        wp_enqueue_style('chld_thm_cfg_parent', trailingslashit(get_template_directory_uri()) . 'style.css', array());
        wp_enqueue_style('child-style', get_stylesheet_directory_uri() . 'style.css', array('chld_thm_cfg_parent'));
        // wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/css/theme.css', array('chld_thm_cfg_parent'), filemtime(get_stylesheet_directory() . '/css/theme.css'));
    }
endif;
add_action('wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10);

add_theme_support( 'custom-header' );

// END ENQUEUE PARENT ACTION
