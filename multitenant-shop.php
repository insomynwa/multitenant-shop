<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin admin area. This file also includes all of the dependencies used by
 * the plugin, and defines a function that starts the plugin.
 *
 * @link              http://code.tutsplus.com/tutorials/adding-custom-fields-to-simple-products-with-woocommerce--cms-27904
 * @package           CWF
 *
 * @wordpress-plugin
 * Plugin Name:       Multitenant Shop
 * Plugin URI:        http://www.tuntangundercover.net
 * Description:       Extension plugin for Groups Plugin & Woocommerce. It's created for multiple tenant and vendor.
 * Version:           1.0.0
 * Author:            Pika
 * Author URI:        https://twitter.com/insomynwa/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'WPINC' ) || die;

if( in_array('groups/groups.php', apply_filters('active_plugins', get_option('active_plugins')))){

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-multitenant.php';

	function multitenant_wc_input_start() {
		if (is_admin()) {
			$admin = new Multitenant();
			$admin->run();
		}
	}

	multitenant_wc_input_start();
}