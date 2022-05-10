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
        add_action('save_post', array($this,'rss_feeds_scraper_post_save'));
        add_action('wp_ajax_get_rss_content', array($this,'get_rss_content') ); 
    }
    //on activation,deactivation and uninstallation rewrite rules are flushed
    function activate(){flush_rewrite_rules();}
    function deactivate(){flush_rewrite_rules();}

    function custom_columns($columns){
        // $columns = array(
        //     'cb' => '<input type="checkbox" />',
        // );
        // echo json_encode($columns);
        return $columns;
    }
    function get_rss_content(){
        if(!wp_verify_nonce($_POST['import_feeds_interface_nonce'], 'import_feeds_interface_nonce')){
            $res['status'] = 'error';
            $res['message'] = 'Invalid request';
            echo json_encode($res);
            die();
        }
        $url = $_POST['source_url'];
        $contentType = get_headers($url, 1)["Content-Type"];
        $res =  null;
        $xmlContentTypes = array('application/xml', 'text/xml', 'application/rss+xml', 'application/atom+xml', 'application/rdf+xml');
        $jsonContentTypes = array('application/json', 'application/feed+json', 'application/vnd.feed+json');
        if(in_array($contentType, $xmlContentTypes)){
            $content = file_get_contents($url);
            $content = simplexml_load_string($content);
            $items = array();
            if(count($content->channel->item)==0){
                $res['status'] = 'danger';
                $res['message'] = "Couldn't find any post from source";
                echo json_encode($res);
                die();
            }
            for($i=0;$i<count($content->channel->item);$i++){
                $items[$i]= $content->channel->item[$i];
            }
            $res['content'] = $items;
            $res['contentType'] = 'XML';
            $res['status'] = 'success';
        }else if(in_array($contentType, $jsonContentTypes)){
            $content = file_get_contents($url);
            $content = json_decode($content,true);
            $records = $content['records'];
            if(count($records)==0){
                $res['status'] = 'danger';
                $res['message'] = "Couldn't find any post from source";
                echo json_encode($res);
                die();
            }
            $res['content'] = $records;
            $res['contentType'] = 'JSON';
            $res['status'] = 'success';
        }else{
            $res['status'] = 'warning';
            $res['message'] = 'Content type not supported';
        }
        echo json_encode($res);
        die(); 
    }
    // add or remove sub menu admin pages
    function register_pages(){
        // add_submenu_page( 'edit.php?post_type='.$this::$pluginSlug, 'RSS Feed Settings', 'Settings', 'manage_options', '', array($this,'settings_page'), null );
        // remove_submenu_page('edit.php?post_type='.$this::$pluginSlug,'post-new.php?post_type='.$this::$pluginSlug);
    }
    // 
    function rss_feeds_scraper_post_save($post_id){
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if(!isset($_POST['import_rss_feed_nonce'])){
            return;
        }
        if (!wp_verify_nonce($_POST['import_rss_feed_nonce'])){
            return;
        }
        if ($this::$pluginSlug == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return;
        } else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }
        update_post_meta($post_id, 'source_url', $_POST['source_url']);
        update_post_meta($post_id, 'source_type', isset($_POST['source_type'])?$_POST['source_type']:'');
        update_post_meta($post_id, 'include_text', isset($_POST['include_text'])?$_POST['include_text']:'');
        update_post_meta($post_id, 'exclude_text', isset($_POST['exclude_text'])?$_POST['exclude_text']:'');
        
    }
    function settings_page(){
        echo plugin_dir_url( __FILE__ ) . '/assets/post_new_page.js';
        echo '<div class="wrap">Setting</div>';
    }
    function rss_feeds_interface(){
        add_meta_box('import_rss_feed_interface', 'Import Rss Interface', array($this,'rss_feeds_interface_display'), $this::$pluginSlug);
    }
    function rss_feeds_interface_display($post)
    {
        wp_nonce_field(-1, 'import_rss_feed_nonce');
        echo "<div id='import_rss_feed_interface_app'></div>";

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
            'register_meta_box_cb'    => array($this,'rss_feeds_interface'),
        );
    
        register_post_type( $this::$pluginSlug, $args );
    }
    function import_feeds_actions_edit(){
        global $post;
        if($post->post_type == $this::$pluginSlug){
            remove_meta_box( 'submitdiv', $this::$pluginSlug, 'side' ); 
        }
    }
    function import_feeds_interface_script(){
        global $post;
        if( $this::$pluginSlug == $post->post_type ){
            $meta_data['source_url'] = get_post_meta($post->ID, 'source_url', true);
            $meta_data['source_type'] = get_post_meta($post->ID, 'source_type', true);
            $meta_data['include_text'] = get_post_meta($post->ID, 'include_text', true);
            $meta_data['exclude_text'] = get_post_meta($post->ID, 'exclude_text', true);

            wp_enqueue_script( 'import_feeds_interface_js', plugin_dir_url( __FILE__ ).'/assets/js/import_feeds_interface.js',array('jquery'),time(),true );
            wp_localize_script( 'import_feeds_interface_js', 'feeds_interface_object', 
                  array(
                    'post_slug' =>$this::$pluginSlug,
                    'ajax_nonce' => wp_create_nonce('import_feeds_interface_nonce'),
                    'ajax_url' => admin_url( 'admin-ajax.php' ), 
                    'path' => plugin_dir_url( __FILE__ ),
                    'meta_data' => $meta_data,
                ) 
            );
            wp_enqueue_style( 'import_feeds_interface_css', plugin_dir_url( __FILE__ ).'/assets/css/import_feeds_interface.css',null, time(), 'all' );
        }
    }
}



if(class_exists('AutoRssFeedParser')) {
    $auto_rss_feed_parser = new AutoRssFeedParser();
    //activation,deactivation and uninstallation hooks are added
    register_activation_hook(__FILE__,array($auto_rss_feed_parser,'activate'));
    register_deactivation_hook(__FILE__,array($auto_rss_feed_parser,'deactivate'));
    //edit default pages
    add_action('admin_head-post-new.php', array($auto_rss_feed_parser,'import_feeds_actions_edit'));
    add_action('admin_head-post.php', array($auto_rss_feed_parser,'import_feeds_actions_edit'));
    //add scripts and styles
    add_action( 'admin_print_scripts-post.php', array($auto_rss_feed_parser,'import_feeds_interface_script'), 11 );
    add_action( 'admin_print_scripts-post-new.php', array($auto_rss_feed_parser,'import_feeds_interface_script'), 11 );
}

