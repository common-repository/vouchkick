<?php
   /*
   Plugin Name: Vouchkick
   Plugin URI: https://vouchkick.com/
   description: Integrate Vouchkick with Wordpress
   Version: 1.0.1
   Author: Vouchkick
   License: GPL2
   */
?>
<?php
if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['SignOut']))
{
    vouchkick_sign_out();
}
add_action( 'admin_menu', 'vouchkick_sd_register_top_level_menu' );
add_action( 'admin_menu', 'vouchkick_sd_register_sub_menu1' );
add_action( 'admin_menu', 'vouchkick_sd_register_sub_menu2' );
register_setting( 'vouchkick-settings-group', 'vouchkick_sign_in_field1' );
register_setting( 'vouchkick-settings-group', 'vouchkick_sign_in_field2' );
add_action('wp_head', 'vouchkick_add_script_wp_head');

function vouchkick_utm_user_scripts() {
            $plugin_url = plugin_dir_url( __FILE__ );

        wp_enqueue_style( 'style',  $plugin_url . "/style.css");
    }

    add_action( 'admin_print_styles', 'vouchkick_utm_user_scripts' );

function vouchkick_sd_register_top_level_menu(){
	add_menu_page(
		'Vouchkick Sign in',
		'Vouchkick',
		'manage_options',
		'vouchkick_sign_in',
		'vouchkick_sd_display_sign_in_menu_page',
		'',
		500
	);
}


function vouchkick_sd_register_sub_menu1() {
    $script_exist = get_option('vouchkick_script_format'); 
    	if ( $script_exist  )
    {
       $page_title = 'Sign out';
       $menu_title = 'Sign out';
    }
    else{
       $page_title = 'Sign in';
       $menu_title = 'Sign in';
    }
	add_submenu_page(
		'vouchkick_sign_in',
		 $page_title ,
		 $menu_title,
		'manage_options',
		'vouchkick_sign_in',
		'vouchkick_sd_display_sign_in_menu_page'
	);
}

function vouchkick_sd_register_sub_menu2() {
	add_submenu_page(
		'vouchkick_sign_in',
		'Sign up',
		'Sign up',
		'manage_options',
		'vouchkick_sign_up',
		'vouchkick_sd_display_sign_up_menu_page'
	);
	
}	

function vouchkick_sd_display_sign_in_menu_page()
	{
?>

<div class="vouchkick">
<form id="signin" method="post" action="options.php">
    <?php settings_fields( 'vouchkick-settings-group' ); ?>
    <?php do_settings_sections( 'vouchkick-settings-group' ); ?>
    <h1>Sign in</h1>
        <label>Email</label>
        <input type="text" id="vouchkick_sign_in_field1" name="vouchkick_sign_in_field1" value="<?php echo esc_attr( get_option('vouchkick_sign_in_field1') ); ?>" />
        <label>Password</label>
        <input type="password" id="vouchkick_sign_in_field2" name="vouchkick_sign_in_field2" value="<?php echo esc_attr( get_option('vouchkick_sign_in_field2') ); ?>" />
        <input type="submit" id="Signin" name="Signin" value="Sign in" />
    </form>
<?php
$domain = $_SERVER['HTTP_HOST'];
$vouchkick_email = esc_attr( get_option('vouchkick_sign_in_field1') );
$vouchkick_password = esc_attr( get_option('vouchkick_sign_in_field2') );
$vouchkick_api_url = "https://app.vouchkick.com/api/login";
$response = wp_remote_post( $vouchkick_api_url, array(
    'method'      => 'POST',
    'body'        => "{\n    \"email\": \"$vouchkick_email\",\n    \"password\":\"$vouchkick_password\"\n}"
    )
);
$response = json_decode(wp_remote_retrieve_body( $response ), true);
$response_status_code = $response[status_code];
$response_status_message = $response[message];
if ($response_status_code == 200 )
{
    $script_code = $response[response]['script_code'];
    $script_id = $response[response]['tracking_id'];

$response = $response[response];
$response = $response[script_format];
add_option('vouchkick_script_format', $response);
add_option('vouchkick_script_code', $script_code);
add_option('vouchkick_script_id', $script_id);
$scrpt_frmt =  get_option('vouchkick_script_format');
if (!empty($scrpt_frmt)){
echo "<script> document.getElementById('signin').style.display='none'; </script>";
echo "<div id='success-msg'>";
echo "Congratulations, Script has been installed successfully. ";
echo "You are signed in as ";
echo esc_attr($vouchkick_email);
echo "</div>";
?>
    <?php if (!isset($_POST['SignOut'])) : ?>
        <script>
            let send2 = new Image();
            send2.src = `https://app.vouchkick.com/api/installed-domain-track/?domain=<?php echo esc_attr($domain); ?>&installed=1&script_code=<?php echo esc_attr($script_code); ?>`;
        </script>
    <?php endif; ?>
<form id="signout" method="post">
    <input type="submit" id="SignOut" name="SignOut" value="Sign Out"/>
</form>

<?php } ?>

<?php

}

else 
{
echo "<div id='warning-msg'>";
echo esc_attr($response_status_message);
delete_option('vouchkick_sign_in_field1');
delete_option('vouchkick_sign_in_field2');
delete_option('vouchkick_script_format');
echo "</div>";
}

echo "</div>"; 

} // sign in tab code ends here 


/* Vouchkick script printed out in the header */

function vouchkick_add_script_wp_head() {


$vouchkick_script_id = esc_attr(get_option('vouchkick_script_id'));
$vouchkick_script_code = esc_attr(get_option('vouchkick_script_code'));
$vouchkick_script_url = "https://app.vouchkick.com/pixel/" .$vouchkick_script_code ;

if (!empty($vouchkick_script_id)) {

echo "<!-- Pixel Code for https://app.vouchkick.com/ -->";
echo '<script async id="'.esc_attr($vouchkick_script_id).'" src="'.esc_url($vouchkick_script_url).'"></script>';
echo "<!-- END Pixel Code -->";

}

}

function vouchkick_sd_display_sign_up_menu_page(){
?>

<div class="vouchkick">

<form method="post" action="options.php">

     <h1>Sign up</h1>
        
        <label>Email</label>
        
        <input type="text" id="vouchkick_sign_up_field" name="vouchkick_sign_up_field" value="<?php echo esc_attr( get_option('admin_email') ); ?>" />

    <script>
   
    function redirect_to_vouchkick () {
    
    var vouchkick_sign_up_field = document.getElementById("vouchkick_sign_up_field").value;
    var register_redirect = "https://app.vouchkick.com/register/" + "email=" + vouchkick_sign_up_field + "&redirect=" + '<?php echo esc_attr(get_bloginfo("wpurl")); ?>' + "/wp-admin/admin.php?page=vouchkick_sign_in" ;
    return register_redirect;
   
    }
   
    </script>

<input type="button" target="_blank" value="Sign up" id="vouchkick-signup" 

onClick="javascript:window.open(redirect_to_vouchkick (), '_blank');" />

</div>

 <div class="about-vouchkick">Learn more about Vouchkick here: <a href="https://vouchkick.com" target="_blank">Vouchkick.com</a></div>
 
<?php 
} // sign up tab code ends here 
?>
<?php // Call when plugin is deactivated
register_deactivation_hook( __FILE__, 'vouchkick_deactivate' );
function vouchkick_deactivate () {
    delete_option('vouchkick_sign_in_field1');
    delete_option('vouchkick_sign_in_field2');
    delete_option('vouchkick_script_format');
    delete_option('vouchkick_script_code');
    delete_option('vouchkick_script_id');

?>
<?php }

function vouchkick_sign_out()
{
    $domain = $_SERVER['HTTP_HOST'];
    $script_code = get_option('vouchkick_script_code');
    delete_option('vouchkick_sign_in_field1');
    delete_option('vouchkick_sign_in_field2');
    delete_option('vouchkick_script_format');
    delete_option('vouchkick_script_code');
    delete_option('vouchkick_script_id');
    echo "<script> document.getElementById('signin').style.display='block'; </script>";
    echo "<script> document.getElementById('SignOut').style.display='none'; </script>";
    echo "<script> document.getElementById('success-msg').style.display='none'; </script>";
    ?>
    <script>
        let send23 = new Image();
        send23.src = `https://app.vouchkick.com/api/installed-domain-track/?domain=<?php echo esc_attr($domain); ?>&installed=0&script_code=<?php echo esc_attr($script_code); ?>`;
    </script>
    <?php
}
?>
