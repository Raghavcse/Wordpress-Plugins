<?php

/*

Plugin Name: Send activation link

Description: Send an activation link to the user when it is register in wordpress, and verify them.

Version: 1.0

Author: Raghav Shukla

Author URI: https://raghavspn.wordpress.com/

*/



function Plugin_Install(){

    if( ! Plugin_page_exists('activate') ){

		$postarr = array(

					'post_title' => 'Activate Account',

					'post_status' => 'publish',

					'post_type' => 'page',

					'post_name' => 'users-activate',

		);

		wp_insert_post( $postarr );

	}

}

register_activation_hook(__FILE__, 'Plugin_Install');

function Plugin_page_exists($page_slug) {

     $page = get_page_by_path( $page_slug , OBJECT );



     if ( isset($page) && $page->ID ) return true;

     else return false;

}



/*------------------ SEND ACTIVATION LINK HOOK ------------------*/

function Plugins_custom_send_link($userid){

	$hash = sha1( $userid . time() );

	add_user_meta( $userid, 'link_hash_activate', $hash, true );

	$user_info = get_userdata($userid);

	$to = $user_info->user_email;

	$subject = 'Email Verification';

	$message = 'Hello,'.$user_info->user_login;

	$message .= "\n\n";

	$message .= 'Welcome to Our Website.';

	$message .= "\n\n";

	$message .= 'Your login details are - .';

	$message .= "\n\n";

	$message .= 'Username: '.$user_info->user_login;

	$message .= "\n";

	$message .= 'Password: Your selected password.';

	$message .= "\n\n";

	$message .= 'Please click this link to activate your account:';

	$message .= home_url('/').'users-activate?id='.$userid.'&key='.$hash;

	$headers = 'From: '. get_option('admin_email') . "\r\n";

	wp_mail($to, $subject, $message, $headers);

}

add_action( 'user_register', 'Plugins_custom_send_link' );



/*------------------ USER AUTHENTICATION HOOK ------------------*/

if ( !function_exists('wp_authenticate') ) :

function wp_authenticate($username, $password) {

    $username = sanitize_user($username);

    $password = trim($password);



    $user = apply_filters('authenticate', null, $username, $password);



    if ( $user == null ) {

        // TODO what should the error message be? (Or would these even happen?)

        // Only needed if all authentication handlers fail to return anything.

        $user = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));

    } elseif ( get_user_meta( $user->ID, 'link_hash_activate', true ) != false ) {

        $user = new WP_Error('activation_failed', __('<strong>ERROR</strong>: Please activate your email first.'));

    }



    $ignore_codes = array('empty_username', 'empty_password');



    if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes) ) {

        do_action('wp_login_failed', $username);

    }



    return $user;

}

endif;



/*------------------ ACTIVATION PAGE SETUP ------------------*/

add_action( 'template_redirect', 'my_custom_activate_user' );

function my_custom_activate_user() {

    if ( is_page('users-activate') ) {

        $user_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );



        if ( $user_id ) {

            $code = get_user_meta( $user_id, 'link_hash_activate', true );

            if ( $code == filter_input( INPUT_GET, 'key' ) ) {

                delete_user_meta( $user_id, 'link_hash_activate' );

				add_filter('the_content', function(){

					return '<h2>Your account has been verified successfully</h2>';

				});

				add_filter('get_the_content', function(){

					return '<h2>Your account has been verified successfully</h2>';

				});

            }

			else{

				add_filter('the_content', function(){

					return '<h2>You have already verified your account.</h2>';

				});

				add_filter('get_the_content', function(){

					return '<h2>You have already verified your account.</h2>';

				});

			}

        }

		else{

			add_filter('the_content', function(){

				return '<h2>'.get_bloginfo('site_title').'</h2>';

			});

			add_filter('get_the_content', function(){

				return '<h2>'.get_bloginfo('site_title').'</h2>';

			});

		}

    }

}