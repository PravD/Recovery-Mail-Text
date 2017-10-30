<?php 
/*
Plugin Name:  Recovery Mail Text
Description:  Changes the default email text in password recovery mail.
Version:      1.0.0
Author:       Pravin Durugkar 
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

function fdpl012_theme_settings_page(){
	?>
	    <div class="wrap">
	    <h1>Recovery Mail Settings</h1>
	    <p>To add user name, use this : <code>[user_name]</code></p>
	    <p>To add user email, use this : <code>[user_email]</code></p>
	    <p>To add password reset link, use this : <code>[pass_reset_link]</code></p> 
	    <form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("recovery-mail-settings-section");      
	            submit_button(); 
	        ?>          
	    </form>
		</div>
	<?php
}

function fdpl012_add_theme_menu_item()
{
	add_menu_page("Recovery Mail Text", "Recovery Mail Text", "manage_options", "recovery-mail-settings", "fdpl012_theme_settings_page", 'dashicons-email-alt', 99);
}

add_action("admin_menu", "fdpl012_add_theme_menu_item");

function fdpl012_display_mail_element()
{
	$settings = array(
		'textarea_name' => 'recovery_mail_text'
    );
    $value = get_option('recovery_mail_text');
	wp_editor($value,"recovery_mail_text",$settings);
}

function fdpl012_display_theme_panel_fields()
{
	add_settings_section("section", "All Settings", null, "recovery-mail-settings-section");
	
	add_settings_field("recovery_mail_text", "Recovery Mail Text", "fdpl012_display_mail_element", "recovery-mail-settings-section", "section");

    register_setting("section", "recovery_mail_text");
}

add_action("admin_init", "fdpl012_display_theme_panel_fields");


function fdpl012_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','fdpl012_set_content_type' );

/* 
 *  Recovery mail text change 
 *
 *  Password reset activation E-mail -> Body
 */

add_filter( 'retrieve_password_message', 'fdpl012_wpse_retrieve_password_message', 10, 2 );
function fdpl012_wpse_retrieve_password_message( $message, $key ){
    $user_data = '';
    // If no value is posted, return false
    if( ! isset( $_POST['user_login'] )  ){
            return '';
    }
    // Fetch user information from user_login
    if ( strpos( $_POST['user_login'], '@' ) ) {

        $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
    } else {
        $login = trim($_POST['user_login']);
        $user_data = get_user_by('login', $login);
    }
    if( ! $user_data  ){
        return '';
    }
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    // Setting up message for retrieve password
	$message = wpautop(get_option('recovery_mail_text'));

    $message_link .= '<a href="';
    $message_link .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $message_link .= '" target="_blank">';
    $message_link .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $message_link .= '</a>"';

    $message = str_replace("[pass_reset_link]",$message_link,$message);
    $message = str_replace("[user_name]",$user_login,$message);
    $message = str_replace("[user_email]",$user_email,$message);

    // Return completed message for retrieve password
    return $message;
}

