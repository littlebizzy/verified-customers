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

// user meta key
define( 'VERIFIED_CUSTOMERS_META_KEY', '_verified_email' );

// send verification email on user register
add_action( 'user_register', function( $user_id ) {

	if ( get_user_meta( $user_id, VERIFIED_CUSTOMERS_META_KEY, true ) ) {
		return;
	}

	$user = get_userdata( $user_id );

	if ( ! $user || empty( $user->user_email ) ) {
		return;
	}

	$token = wp_create_nonce( 'verified_customers_' . $user_id );

	$url = add_query_arg(
		array(
			'verified_customers_verify' => 1,
			'user_id'                   => $user_id,
			'token'                     => $token,
		),
		site_url( '/' )
	);

	$subject = __( 'Verify your email address', 'verified-customers' );

	$message = sprintf(
		__( "Please verify your email address by clicking the link below:\n\n%s", 'verified-customers' ),
		$url
	);

	wp_mail( $user->user_email, $subject, $message );

} );



// handle verification link
add_action( 'init', function() {

	if ( empty( $_GET['verified_customers_verify'] ) ) {
		return;
	}

	$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	$token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

	if ( ! $user_id || ! wp_verify_nonce( $token, 'verified_customers_' . $user_id ) ) {
		wp_die( esc_html__( 'Invalid or expired verification link.', 'verified-customers' ) );
	}

	update_user_meta( $user_id, VERIFIED_CUSTOMERS_META_KEY, 1 );

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}

	wp_safe_redirect( home_url( '/' ) );
	exit;

} );



// block checkout if email not verified
add_action( 'woocommerce_checkout_process', function() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( get_user_meta( $user_id, VERIFIED_CUSTOMERS_META_KEY, true ) ) {
		return;
	}

	wc_add_notice(
		__( 'Please verify your email address before placing an order. Check your inbox for the verification link.', 'verified-customers' ),
		'error'
	);

} );



// show resend notice on my account page
add_action( 'woocommerce_before_my_account', function() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( get_user_meta( $user_id, VERIFIED_CUSTOMERS_META_KEY, true ) ) {
		return;
	}

	$resend_url = add_query_arg(
		array(
			'verified_customers_resend' => 1,
		),
		wc_get_page_permalink( 'myaccount' )
	);

	wc_print_notice(
		sprintf(
			__( 'Your email address is not verified. <a href="%s">Resend verification email</a>.', 'verified-customers' ),
			esc_url( $resend_url )
		),
		'notice'
	);

} );



// handle resend request
add_action( 'init', function() {

	if ( empty( $_GET['verified_customers_resend'] ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = get_current_user_id();

	delete_user_meta( $user_id, VERIFIED_CUSTOMERS_META_KEY );

	do_action( 'user_register', $user_id );

	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;

} );

// Ref: ChatGPT
