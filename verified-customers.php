<?php
/*
Plugin Name: Verified Customers
Plugin URI: https://www.littlebizzy.com/plugins/verified-customers
Description: Verified emails for Woo checkout
Version: 1.0.0
Requires PHP: 7.0
Tested up to: 6.9
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Update URI: false
GitHub Plugin URI: littlebizzy/verified-customers
Primary Branch: master
Text Domain: verified-customers
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// override wordpress.org with git updater
add_filter( 'gu_override_dot_org', function( $overrides ) {
	$overrides[] = 'verified-customers/verified-customers.php';
	return $overrides;
}, 999 );



// Ref: ChatGPT
