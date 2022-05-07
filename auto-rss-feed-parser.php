<?php
/** 
 * @package AutoRssFeedParser
 */
/*
Plugin Name: Auto RSS Feed Parser
Plugin URI: http://xentola.com/wordpress-plugins/auto-rss-feed-parser/
Description: Automatically parses RSS feeds and imports them into your WordPress site.
Version: 1.0.0
Author: Xentola
Author URI: https://xentola.com/
License: GPLv2 or later
Text Domain: auto-rss-feed-parser
*/

/*
auto-rss-feed-parser Automatically parses RSS feeds and imports them into your WordPress site
Copyright (C) Xentola 

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined('ABSPATH') or die('You are not allowed to call this page directly.');
function_exists('add_action') or die('You are not allowed to call this page directly.');


class AutoRssFeedParser {
    public static $pluginSlug = 'rss-feed-scraper';
    function __construct(){
        add_action('admin_menu',array($this,'register_pages'));
        add_filter('manage_'.$this::$pluginSlug.'_posts_columns', array($this,'custom_columns'));
        add_action( 'init', array($this,'rss_feeds_scraper_post_type') );
    }
    //on activation,deactivation and uninstallation rewrite rules are flushed
    function activate(){flush_rewrite_rules();}
    function deactivate(){flush_rewrite_rules();}

    function custom_columns($columns){
        // $columns = array(
        //     'cb' => '<input type="checkbox" />',
        // );
        return $columns;
    }
    // add or remove sub menu admin pages
    function register_pages(){
        add_submenu_page( 'edit.php?post_type='.$this::$pluginSlug, 'RSS Feed Settings', 'Settings', 'manage_options', '', array($this,'settings_page'), null );
        remove_submenu_page('edit.php?post_type='.$this::$pluginSlug,'post-new.php?post_type='.$this::$pluginSlug);
    }
    // 
    function settings_page(){
        echo plugin_dir_url( __FILE__ ) . '/assets/post_new_page.js';
        echo '<div class="wrap">Setting</div>';
    }
    function register_recipe_meta_boxes(){
        add_meta_box('import_rss_feed_interface', 'Import Rss Interface', array($this,'recipe_info_display'), $this::$pluginSlug);
        // add_meta_box('')
    }
    function recipe_info_display($post)
    {
        wp_nonce_field(-1, 'import_rss_feed_nonce');
        $content = '';
        echo $content;
    }
    function rss_feeds_scraper_post_type() {
        $labels = array(
            'name'                => 'Rss Feeds',
            'singular_name'       => 'Rss Feed',
            'menu_name'           => 'Rss Feeds',
            'parent_item_colon'   => 'Category',
            'all_items'           => 'All Rss Feeds',
            'view_item'           => 'View Rss Feeds',
            'add_new_item'        => 'New Rss Feed',
            'add_new'             => 'New Import',
            'edit_item'           => 'Edit',
            'update_item'         => 'Update',
            'search_items'        => 'Search Rss Feeds',
            'not_found'           => 'No Rss Feeds found',
            'not_found_in_trash'  => 'No Rss Feeds found in Trash',
        );
            
        $args = array(
            'label'               => 'Rss Feeds',
            'description'         => 'Rss Feeds Auto Scarper',
            'labels'              => $labels,
            'supports'            => array('title'),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'rewrite' => array( 'slug' => $this::$pluginSlug, 'with_front' => false ),
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-rss',
            'register_meta_box_cb'    => array($this,'register_recipe_meta_boxes'),
        );
    
        register_post_type( $this::$pluginSlug, $args );
    }
}
function import_feeds_actions_edit(){
    global $post;
    if($post->post_type == 'rss-feed-scraper'){
        remove_meta_box( 'submitdiv', 'rss-feed-scraper', 'side' ); 
    }
}
function import_feeds_interface_script(){
    global $post_type;
    if( 'rss-feed-scraper' == $post_type ){
        wp_enqueue_script( 'import_feeds_interface_js', plugin_dir_url( __FILE__ ).'/assets/js/import_feeds_interface.js',array('jquery'),time(),true );
        wp_localize_script( 'import_feeds_interface_js', 'feeds_interface_object', 
		  	array( 
				'path' => plugin_dir_url( __FILE__ ),
			) 
		);
        wp_enqueue_style( 'import_feeds_interface_css', plugin_dir_url( __FILE__ ).'/assets/css/import_feeds_interface.css',null, time(), 'all' );
    }
}
add_action('admin_head-post-new.php', 'import_feeds_actions_edit');
add_action('admin_head-post.php', 'import_feeds_actions_edit');
add_action( 'admin_print_scripts-post.php', 'import_feeds_interface_script', 11 );
add_action( 'admin_print_scripts-post-new.php', 'import_feeds_interface_script', 11 );

if(class_exists('AutoRssFeedParser')) {
    $auto_rss_feed_parser = new AutoRssFeedParser();
    //activation,deactivation and uninstallation hooks are added
    register_activation_hook(__FILE__,array($auto_rss_feed_parser,'activate'));
    register_deactivation_hook(__FILE__,array($auto_rss_feed_parser,'deactivate'));
    //register new custom post type
    
    // add_action('add_meta_boxes', array($auto_rss_feed_parser,'register_recipe_meta_boxes'));
    
    
}

