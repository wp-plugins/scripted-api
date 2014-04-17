<?php
add_action('admin_menu', 'scripted_settings_menu');
add_action( 'admin_notices', 'scripted_install_warning' );

/**
 * Display an admin-facing warning if the current user hasn't authenticated with scripted yet
 *
 * @since 1.0
 */
function scripted_install_warning() {
	$apiKey                  = get_option( '_scripted_api_key' );
        $_scripted_business_id   = get_option( '_scripted_business_id' );

	$page = (isset($_GET['page']) ? $_GET['page'] : null);

	if ((empty($apiKey)  || empty($_scripted_business_id)) && $page != 'scripted_settings_menu' && current_user_can( 'manage_options' ) ) {
		admin_dialog( sprintf( 'You must %sconfigure the plugin%s to enable Scripted for WordPress.', '<a href="admin.php?page=scripted_settings_menu">', '</a>' ), true);
	}
}
function admin_dialog($message, $error = false) {
	if ($error) {
		$class = 'error';
	}
	else {
		$class = 'updated';
	}
	
	echo '<div ' . ( $error ? 'id="scripted_warning" ' : '') . 'class="' . $class . ' fade' . '"><p>'. $message . '</p></div>';
}
function scripted_admin_styles() {
    wp_register_style( 'scripteAdminStyle', plugins_url('admin/scripts/scripted.css', SCRIPTED_FILE_URL) );
    wp_enqueue_style( 'scripteAdminStyle' );
}

function scripted_settings_menu() {
   add_menu_page('scripted_settings', 'Settings', 'add_users','scripted_settings_menu', 'scripted_settings_menu_function', SCRIPTED_ICON, 83);
   
   $apiKey                  = get_option( '_scripted_api_key' );
   $_scripted_business_id  = get_option( '_scripted_business_id' );
    
    if($apiKey != '' and $_scripted_business_id !='') {
	$createAJobPage = add_submenu_page( 'scripted_settings_menu', 'Create a Job', 'Create a Job', 'manage_options', 'scripted_create_a_job', 'scripted_create_a_job_callback' ); 
        add_action( 'admin_footer-'. $createAJobPage, 'getFormFields' );
        $currentJobPage = add_submenu_page( 'scripted_settings_menu', 'Current Jobs', 'Current Jobs', 'manage_options', 'scripted_create_current_jobs', 'scripted_create_current_jobs_callback' );
        $finishedPage = add_submenu_page( 'scripted_settings_menu', 'Finished Jobs', 'Finished Jobs', 'manage_options', 'scripted_create_finished_jobs', 'scripted_create_finished_jobs_callback' );
        
        // javascript functions
        add_action( 'admin_footer-'. $finishedPage, 'createProjectAjax' );
        
        //adding style sheet to admin pages
        add_action( 'admin_print_styles-' . $createAJobPage, 'scripted_admin_styles' );
        add_action( 'admin_print_styles-' . $finishedPage, 'scripted_admin_styles' );
        add_action( 'admin_print_styles-' . $currentJobPage, 'scripted_admin_styles' );
    }
}
function scripted_settings_menu_function() {
    
  if(isset($_POST) && wp_verify_nonce($_POST['_wpnonce'],'scriptedFormAuthSettings')) {        
      
      $validate = validateApiKey($_POST['_scripted_api_key'],$_POST['_scripted_business_id']);
        if($validate) {
            update_option( '_scripted_api_key', sanitize_text_field($_POST['_scripted_api_key']) );        
            update_option( '_scripted_business_id', sanitize_text_field($_POST['_scripted_business_id'] ));        
        } else {
            echo '<div class="updated" id="message"><p>Sorry, we found an error. Please confirm your API key and Business ID are correct and try again.</p></div>';
        }
    }
   $out = '<div class="wrap">
            <div class="icon32" style="width:100px;padding-top:5px;" id="icon-scripted"><img src="'.SCRIPTED_LOGO.'"></div><h2>Settings</h2>';
   
   $out .='<p>Authentication is required for many functions of the Scripted API. We use token-based authentication.<br />
You can think of your business_id as your username, and your key as your password.</p>';
   
   $out .='<p>To get your Business ID and key, please register or log in at Scripted.com, and go to https://Scripted.com/api. Your credentials will show at the top of this page.</p>';
            
   $out .='<form action="" method="post" name="scripted_settings">'.wp_nonce_field( 'scriptedFormAuthSettings', '_wpnonce' );
   
   $apiKey                  = get_option( '_scripted_api_key' );
   $_scripted_business_id   = get_option( '_scripted_business_id' );
   
   $out .='<table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="api_key">API Key</label></th>
          <td><input type="text" class="regular-text" value="'.$apiKey.'" id="_scripted_api_key" name="_scripted_api_key"></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="business_id">Business Id</label></th>
          <td><input type="text" class="regular-text" value="'.$_scripted_business_id.'" id="_scripted_business_id" name="_scripted_business_id"></td>
        </tr>
     </tbody>
    </table>
    <p class="submit">
      <input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit">
    </p>';
   
   
   $out .='</form>';
   
   $out .='</div>';// end of wrap div
   echo $out;
}
function validateApiKey($apiKey,$businessId)
{
   $_currentJobs = @file_get_contents('https://scripted.com/jobs?key='.$apiKey.'&business_id='.$businessId); 
   return true;
   if($_currentJobs != '') {
        $_currentJobs = json_decode($_currentJobs);
        if(isset($_currentJobs->total)) {
            return true;
        }
   }
   return false;
}