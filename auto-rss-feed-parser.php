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

    function __construct()
    {
        add_action('admin_init',array($this,'register_pages'));
    }
    //on activation,deactivation and uninstallation rewrite rules are flushed
    function activate(){$this->custom_post_type();flush_rewrite_rules();}
    function deactivate(){flush_rewrite_rules();}


    // Register Custom Page Type
    function register_pages(){
        add_menu_page( 'Auto Rss Feed Scraper', 'RSS Feed', 'manage_options', 'rss-feed-scraper', array($this,'home_page'),'dashicons-rss', 1 );
    }
    function home_page(){
        echo 'Unsubscribe Email List';
    }
}

if(class_exists('AutoRssFeedParser')) {
    $auto_rss_feed_parser = new AutoRssFeedParser();
}
//activation,deactivation and uninstallation hooks are added
register_activation_hook(__FILE__,array($auto_rss_feed_parser,'activate'));
register_deactivation_hook(__FILE__,array($auto_rss_feed_parser,'deactivate'));