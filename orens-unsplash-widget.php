<?php
/*
Plugin Name: Oren's Unsplash Widget
Description: Quickly display your Unsplash photos inside WordPress widget.
Author: Oren Yomtov
Version: 1.0.0
Author URI: https://orenyomtov.com
Text Domain: orens-unsplash-widget
Domain Path: /languages
*/


/*  Copyright 2020  Oren Yomtov  (website: orenyomtov.com)
    Copyright 2013  Meks  (email : support@mekshq.com)
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'UNSPLASH_WIDGET_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'UNSPLASH_WIDGET_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'UNSPLASH_WIDGET_VER', '1.0.0' );

/* Initialize Widget */
if ( !function_exists( 'unsplash_widget_init' ) ):
    function unsplash_widget_init() {
        require_once UNSPLASH_WIDGET_DIR.'inc/class-unsplash-widget.php';
        register_widget( 'Unsplash_Widget' );
    }
endif;

add_action( 'widgets_init', 'unsplash_widget_init' );

/* Load text domain */
function load_unsplash_widget_text_domain() {
    load_plugin_textdomain( 'unsplash-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'load_unsplash_widget_text_domain' );

?>