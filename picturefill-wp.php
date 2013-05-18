<?php
/*
Plugin Name: Picturefill.WP
Plugin URI: http://github.com/kylereicks/picturefill.js.wp
Description: A wordpress plugin to load responsive/retina images via picturefill.js.
Author: Kyle Reicks
Version: 1.1.1
Author URI: http://github.com/kylereicks/
*/

define('PICTUREFILL_WP_PATH', plugin_dir_path(__FILE__));
define('PICTUREFILL_WP_URL', plugins_url('/', __FILE__));

require_once(PICTUREFILL_WP_PATH . 'php/class-picturefill-wp.php');

$picturefill_wp = new Picturefill_WP();
