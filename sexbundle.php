<?php
/*

Plugin Name: SexBundle
Plugin URI: http://www.sexbundle.com
Description: SexBundle.Com Management plugin
Version: 1.4
Author: P. Razvan
Author URI: http://www.sexbundle.com
*/


define('sexbundle_plugin_version', '1.4');
add_action( 'wp_login', 'check_for_version', 10, 2 ); 

function check_for_version($user_login, $user){


	if(is_super_admin( $user->data->ID )){

		$string_to_send = "action=get_latest_plugin_version";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'http://sexbundle.com/processor');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
			$string_to_send  );

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_TIMEOUT,4000);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
			
		$answer = json_decode($server_output);

		update_option('sb_current_version', $answer->version);
		update_option('sb_message', $answer->message);

	}

}


add_action( 'admin_init', 'version_reminder' );



function version_reminder(){

	$sb_current_version = get_option('sb_current_version');
	if( $sb_current_version != sexbundle_plugin_version ){
		add_action( 'admin_notices', 'update_notice');
	}

} 

function update_notice(){

		$html  =  '<div class="error fade">';
		$msg   = get_option('sb_message', $answer->message);
		$html .= $msg;
		$html .= '</div>';

		echo $html;

}



if ( ! defined( 'ABSPATH' ) ) die();


load_plugin_textdomain('atc-menu', false, dirname(plugin_basename(__FILE__)) . '/languages/');

register_activation_hook( __FILE__, 'sb_atc_install' );
register_uninstall_hook( __FILE__, 'sb_atc_uninstall' );
//register_deactivation_hook( __FILE__, 'sb_deactivate' );

function sb_my_enqueue($hook) {

    wp_enqueue_script( 'my_custom_script', plugins_url( 'sexbundle/chart/Chart.js') );
}


add_action( 'admin_enqueue_scripts', 'sb_my_enqueue' );

add_action('admin_head', 'sb_set_image_size');
add_action('admin_init', 'sb_set_image_size');
add_action( 'init', 'sb_set_image_size' );


function sb_set_image_size(){
	add_image_size( 'sb_thumb', 300, 300, true );
}




add_action( 'admin_notices', 'activation_notice');

function activation_notice(){

	$sk = get_option( 'sb_secret_key');

	if( strlen($sk) < 2 ){

		$html =  '<div class="error fade">';
		
		$html .= '<p style="font-style:italic; font-size:16px;">Your <span style="color:red">SexBundle.com</span> plugin has not been set up. </p>';

		$html .= '<strong>How to set up ?</strong><br/><hr/><br/>';
		$html .= '<strong>1.</strong> For this go to <a href="'.site_url().'/wp-admin/admin.php?page=sexbundle" >SexBundle.com</a>, found in the left menu. <br/> <br/>';
		$html .= '<strong>2.</strong> Enter your username / password  from SexBundle.com, press "Login".  <br/> <br/>';
		$html .= '<strong>3.</strong> If you account credentials are correct, select your website from the list and press "Select website".<br/><br/> ';
		$html .= '<p style="font-weight:bold">All done! Enjoy the SexBundle plugin!</p>';


		$html .= '</div>';
		echo $html;

	}

}

add_filter('manage_posts_columns', 'sb_ST4_columns_head');
add_action('manage_posts_custom_column', 'sb_ST4_columns_content', 10, 2);


function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=sexbundle">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );



// ADD NEW COLUMN
function sb_ST4_columns_head($defaults) {
    $defaults['sexbundle'] = 'SexBundle.com';
    return $defaults;
}
 

function sb_ST4_columns_content($column_name, $post_ID) {
    if ($column_name == 'sexbundle') {
    	global $wpdb;
  			$sb_article_url = end($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'sexbundle_connections  WHERE ID_post =  '.(int)$post_ID.'  LIMIT 1'));

			if( isset( $sb_article_url->URL ) ) {
				echo '<p style="color:#7ad03a">Submited</p>';
			}
			else{
				echo '<p style="color:#a00">Not submited</p>';
			}
    }
}


// add page visible to editors
add_action( 'admin_menu', 'sb_register_my_page' );

function sb_register_my_page(){
    add_menu_page( 'SexBundle.com', 'SexBundle.com', 'edit_others_posts', 'sexbundle', 'sb_my_page_function', plugins_url( 'sexbundle/icon.png' ), 99999 ); 
}

// modify capability
function sb_my_page_capability( $capability ) {
	return 'edit_others_posts';
}
add_filter( 'option_page_capability_my_page_slug', 'sb_my_page_capability' );


function sb_my_page_function(){
	include('tabs/main.php');
}


function sb_deactivate() {

	// delete_option( 'sb_atc_settings' );

}



function sb_atc_uninstall() {

	delete_option( 'sb_atc_settings' );

}

function sb_atc_install() {

$installed_ver = get_option( "sb_atc_settings" );

	if ( $installed_ver != 1 ) {
	
		global $wpdb;
		$sql = 'CREATE TABLE IF NOT EXISTS `'. $wpdb->prefix.'sexbundle_connections` (
				  `ID_post` int(10) NOT NULL,
				  `URL` varchar(200) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		';

		$wpdb->query( $sql );

		add_option( 'sb_atc_settings', 1 );
	}




	
}

// Hook for adding admin menus
if ( is_admin() ){ // admin actions

  // Hook for adding admin menu
  add_action( 'admin_menu', 'sb_atc_op_page' );
  add_action( 'admin_init', 'sb_atc_register_setting' );

// Display the 'Settings' link in the plugin row on the installed plugins list page
	//add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'atc_admin_plugin_actions', -10);


} else { // non-admin enqueues, actions, and filters

}


// action function for above hook
function sb_atc_op_page() {

    // Add a new submenu under Settings:
   //add_menu_page( 'SexBundle.com', 'SexBundle.com', 'edit_others_posts', 'sexbundle', 'my_page_function', plugins_url( 'sexbundle/icon.png' ), 99999 ); 
   add_submenu_page('sexbundle', 'Statistics', 'Statistics', 'manage_options', 'sexbundle-statistics', 'sb_atc_settings_page'); 

   // add_options_page(__('SexBundle Widget Manager','atc-menu'), __('SexBundle Widget Manager','atc-menu'), 'manage_options', __FILE__, 'atc_settings_page');
}
function sb_atc_register_setting() {	
register_setting( 'atc_options', 'atc_settings' );
}

// atc_settings_page() displays the page content 
function sb_atc_settings_page() { 
	include('tabs/statistics.php');
 }



/*  doest the processing here*/


/* PROCCESS THE ADD WIDGET TO CATEGORIES */
if(isset($_POST['widget_cat_script'])){


	$id = $_POST['widget_script_id'];
	$content = $_POST['widget_cat_script'];

	$category_widgets = get_option('category_widgets');
	

	if($category_widgets == '')
		$cat_info = array();
	else
		$cat_info = json_decode( $category_widgets,true );

	$cat_info[$_POST['widget_script_id_ref']] = $id;


	update_option( 'sexcat_'.$id, $content );
	update_option('category_widgets', json_encode($cat_info));
	clear_w3();

}


if( isset($_POST['remove_from_category'])){


	
	$category_widgets = get_option('category_widgets');
	$cat_info = json_decode( $category_widgets,true );

	delete_option( 'sexcat_'.$_POST['widget_script_id'] );
	
	unset($cat_info[$_POST['widget_script_id_ref']]) ;


	update_option('category_widgets', json_encode($cat_info));
	clear_w3();


}

if(isset($_POST['filter_period'])){
	
	update_option('statistics_period', $_POST['show_period']);

}

function clear_w3(){

	if (function_exists('w3tc_dbcache_flush')) { w3tc_dbcache_flush(); }
	if ( function_exists( 'w3tc_objectcache_flush' ) ){ w3tc_objectcache_flush(); }
}

if(isset($_POST['widget_id_add'])){

	$widget_id = $_POST['widget_id_add'];
	update_option( 'sb_widget_all_id', $_POST['widget_id_add'] );
	update_option( 'sb_widget_all_script', $_POST['widget_id_add_script'] );
	
	clear_w3();
}

if(isset($_POST['widget_id_remove'])){

	$widget_id = $_POST['widget_id_remove'];

	delete_option( 'sb_widget_all_id' );
    delete_option( 'sb_widget_all_script' );	

	clear_w3();
}


/* PROCESS THE ADD SECRET KEY */

if(isset($_POST['sb_update_account'])){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,'http://sexbundle.com/processor');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	                "action=check_secret_key&sb_username=".esc_sql($_POST['sb_username']).'&sb_password='.esc_sql($_POST['sb_password'])  
	            );

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_TIMEOUT,7000);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
	
	global $answer;
	$answer = json_decode($server_output);

	if ($answer->error == "0") { 

		update_option( 'sb_sites', json_encode($answer->websites) );
		update_option( 'sb_username', esc_sql($_POST['sb_username']) );
		update_option( 'sb_password', esc_sql($_POST['sb_password']) );
	} 
		else { 

			echo '<script>alert("Username / Password combination incorrect. ")</script>';
	}

}



/* PROCESS THE SELECT WEBSITE FORM */

if( isset($_POST['sb_select_web'])):

	update_option( 'sb_sweb', esc_sql($_POST['sb_sweb']) );

	$json = array();
	$json['sb_sweb'] =  esc_sql($_POST['sb_sweb']) ;
	$json['sb_skey'] =  $sk; ;


	$sw = get_option( 'sb_sites' );
	$sites = json_decode($sw );
	$sk = '';
	$sn = '';
	$su = '';

	foreach( $sites as $site):
		if($site->ID == (int)$_POST['sb_sweb'] ):
			$sk = $site->secret_key;
			$su = $site->website_url;
			$sn = $site->website;
		endif;
	endforeach;

	$json = array();

	$json['sb_sweb'] =  esc_sql($_POST['sb_sweb']) ;
	$json['sb_skey'] =  $sk ;
	$json['sb_name'] =  $sn ;
	$json['sb_url']  =  $su ;

	update_option( 'sb_selected_web' , json_encode( $json) );
	update_option( 'sb_secret_key' ,  $sk );



endif;

/* PROCESS THE SUBMIT FORM ! */

if(isset($_POST['sb_submit_article_final_form'])){

	$sk = get_option( 'sb_secret_key');

	$article_url = $_POST['sb_article_url'];
	$article_name = $_POST['sb_article_title'];
	$article_content = $_POST['sb_article_title_content'];
	$article_thumb  = $_POST['sb_article_thumb'];
	$article_category  = $_POST['sb_article_category'];

	global $wpdb;
	$size = sizeof($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'sexbundle_connections   WHERE ID_post  ='. (int)$_POST['sb_post_id']  ));

	if( $size == 0){
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'sexbundle_connections ( ID_post, URL) VALUES ( '.(int)$_POST['sb_post_id'].', "'.$article_url.'" )');
	}
	else{
		$wpdb->query('UPDATE '.$wpdb->prefix.'sexbundle_connections SET URL = "'.$article_url.'"  WHERE ID_post = '.(int)$_POST['sb_post_id']);
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,'http://sexbundle.com/processor');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"action=submitarticle&secret_key=".$sk."&article_url=".$article_url."&article_name=".$article_name."&article_content=".$article_content."&article_thumb=".$article_thumb."&category=".$article_category );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_TIMEOUT,8000);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
}


function hook_content($content)
{
	$widget_all = get_option( 'sb_widget_all_script' );
	$categories = get_categories();

	$c = '';
	foreach( $categories as $cat ):
	
		global $post;

		$content_widget = get_option( 'sexcat_'.$cat->term_id );
		$cat_array = array(0 => $cat->term_id); 

		if(  in_category( $cat_array ) &&  is_single() && ( $content_widget  != '') ){
			$c =  '<div >'.$content_widget .'</div>';
		}
		else{

			$c =  '<div>'.$widget_all .'</div>';
		}

		endforeach;
		
		
		if(is_single())
			$content .= stripslashes(urldecode($c));

	return $content;
}


add_filter( 'the_content', 'hook_content' , -1);

function sb_get_the_excerpt($post_id) {
  global $post;  
  $save_post = $post;
  $post = get_post($post_id);
  $output = wp_trim_words($post->excerpt, 170);

  if(empty($output))
  {
  	$output = wp_trim_words($post->post_content, 170);
  }

  $post = $save_post;
  return $output;
}



add_action( 'post_submitbox_misc_actions', 'sb_custom_button' );
add_action( 'in_admin_header', 'posts_widget_header' );

function posts_widget_header(){
$html = '';

if(isset($_GET['post'])){

			$post_info = get_post( (int)$_GET['post'] );
			$url_img = wp_get_attachment_url( get_post_thumbnail_id((int)$_GET['post'], 'sb_thumb') );
			
			$html .= '<div id="sb_overlay" style="position:fixed; z-index:9989; display:none; height:2500px; width:2500px; left:0px; top:0px; background-image:url('.plugins_url('sexbundle/transparent_bg.png').');"></div>';
			$html .= '<div style="width:600px; display:none;  position:fixed; z-index:9999; left:50%; padding:10px; margin-left:-300px; top:20%; background-color:#fff; border:1px solid #ccc" id="submit_to_sb_form">
						<form method="post" action="">
						<input type="hidden" name="sb_post_id" value="'.$_GET['post'].'" />
						<input type="hidden" name="sb_submit_article_final_form" id="sb_submit_article_final_form" value="1">
						<a href="#" style="position:absolute; right:-20px; top:-20px;" id="sb_form_close"><img src="'.plugins_url('sexbundle/close.png').'" s></a>
						
						<div style="float:left">
							<div id="sb_thumb_wrapper">'.
								get_the_post_thumbnail((int)$_GET['post'], 'sb_thumb', array('id' => 'article_thumb_image','style'=>' width:200px; height:auto; min-height:150px; display:block'));
								if(strlen($url_img) <= 2){
									$html .= '<img src="'.plugins_url('sexbundle/no_image_found.jpg').'" id="article_thumb_image" style="width:200px; height:200px;">';
								}

					$html .='
							</div>
							
							<br/><br/>
							<input type="submit" class="button-primary" value="Submit to SexBundle.com" id="sb_submit_article_final" name="sb_submit_article_final">
						</div>	

						<div style="float:left; width:387px; margin-left:10px;">
							<p style="margin-top:0px;"><label>Article URL</label><br/><input name="sb_article_url" id="sb_article_url" placeholder="Article URL" type="text" style="width:100%;border:1px solid #cccccc" value="'.get_permalink((int)$_GET['post']).'"></p>
							<p><label>Article IMAGE URL ( Best resolution: 300 x 300 px )</label><br/><input type="text" name="sb_article_thumb" " style="width:100%;border:1px solid #cccccc" placeholder="Article IMAGE URL" id="sb_article_thumb" value="'.$url_img .'"></p>
							<p><label>Article title</label><br/><input type="text" style="width:100%;border:1px solid #cccccc" placeholder="Article title" name="sb_article_title" id="sb_article_title" value="'.$post_info->post_title.'"></p>
							<p><label>Article category</label><br/>
							<select type="text" style="width:100%; border:1px solid #cccccc" placeholder="Article category" name="sb_article_category" id="sb_article_category">
		              			<option value="1">Select article category</option>
		                        <option value="2">Dating</option>
		                       	<option value="5">Erotica</option>
		                       	<option value="12">News</option>
		                       	<option value="4">Relationships</option>
		                       	<option value="3">Reviews</option>
		                       	<option value="6">Sex</option>
							</select>


							</p>


							<p><label>Article short description</label><br/><textarea  style="width:100%;height:130px;border:1px solid #cccccc"  placeholder="Article description" name="sb_article_title_content" id="sb_article_title_content">'.sb_get_the_excerpt((int)$_GET['post']).'</textarea></p>
						</div>
						<p style="font-style:italic">You can manually edit all the above fields if you choose to.</p>
						</form>	
					</div>
					<script type="text/javascript">
						jQuery(document).ready(function(){


							jQuery("#sb_article_thumb").change(function(){

								jQuery("#article_thumb_image").attr("src", jQuery(this).val() );

							});

							jQuery("#sb_submit_article_final").click(function(){

								var error = 0;

								if( jQuery("#sb_article_title_content").val().length <2 ){
									error = 1;
									jQuery("#sb_article_title_content").attr("style","width:100%; border:1px solid red; height:130px");
								}
								else{
									jQuery("#sb_article_title_content").attr("style","width:100%;height:130px; border:1px solid #cccccc");
								}


								if( jQuery("#sb_article_category").val() == 1 || jQuery("#sb_article_category").val() == "1" ){
									error = 1;
									jQuery("#sb_article_category").attr("style","width:100%; border:1px solid red");
								}
								else{
									jQuery("#sb_article_category").attr("style","width:100%; border:1px solid #cccccc");
								}



								if( jQuery("#sb_article_thumb").val().length <20 ){
									error = 1;
									jQuery("#sb_thumb_wrapper").attr("style","border:1px solid red");
								}
								else{
									jQuery("#sb_thumb_wrapper").attr("style","border:0px solid red");
								}



								if( jQuery("#sb_article_title").val().length < 2 ){
									error = 1;
									jQuery("#sb_article_title").attr("style","width:100%; border:1px solid red");
								}
								else{
									jQuery("#sb_article_title").attr("style","width:100%; border:1px solid #cccccc");
								}



								if( jQuery("#sb_article_url").val().length < 2 ){
									error = 1;
									jQuery("#sb_article_url").attr("style","width:100%; border:1px solid red");
								}
								else{
									jQuery("#sb_article_url").attr("style","width:100%; border:1px solid #cccccc");
								}


								if( error == 1)
									return false;

								jQuery(this).hide();
							});
						});


					</script>
			';
		}

echo $html;
}


function sb_custom_button(){

		$html = '';

		if(isset($_GET['post'])){

			$post_info = get_post( $_GET['post'] );
			$url_img = wp_get_attachment_url( get_post_thumbnail_id($_GET['post'], 'sb_thumb') );

		}



        $html .= '<div id="major-publishing-actions" style="overflow:hidden">';
        $html .= '<div id="publishing-action">';
    	
    	$sk = get_option( 'sb_secret_key');
    		if(strlen($sk) < 2 ){
				echo '<div id="major-publishing-actions" style="overflow:hidden"><div id="publishing-action"><p style="color:red">Validate your SexBundle plugin first.</p></div></div>';
				return;
			}

    	if(isset($_GET['post'])){
    		
    		global $wpdb;
		
			$sb_article_url = end($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'sexbundle_connections  WHERE ID_post =  '.(int)$_GET['post'].'  LIMIT 1'));
			$sb_article_url = $sb_article_url->URL;

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,'http://sexbundle.com/processor');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,"action=getpostinfo&secret_key=".$sk."&article_url=".$sb_article_url );
			curl_setopt($ch,CURLOPT_TIMEOUT,7000);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
		
			$answer_ = json_decode($server_output);
	
			if($answer_->error == 1){

        		$html .= '<a  href="#" class="button-primary" id="submit_to_sb" >Submit to SexBundle.com</a>';				
			
			}else{

				if($answer_->status == 'draft')
					$html .= 'Waiting for approval by SexBundle.com';	
				elseif($answer_->status == 'publish')
        			$html .= 'Article was <span style="color:#7ad03a">APPROVED</span> by SexBundle.com <br/> <a  target="_blank" href="'.$answer_->URL.'" class="button-primary" id="go_to_article" >View on SexBundle.com</a>';		
				else
        			$html .= '<p>Article was <span style="color:#a00">DENIED</span> by SexBundle.com</p>';	

			}

    	}
    	else{
        	$html .= '<a href="#" class="button" onclick="alert(\'The post must be published before submiting. \'); return false;" >Submit to SexBundle.com</a>';
    	}

        $html .= '</div>';
        $html .= '</div>';
  
        $html .= '<script type="text/javascript">
        	jQuery(document).ready(function(){

        		jQuery("#submit_to_sb").click(function(){

        			jQuery("#submit_to_sb_form").show();
        			jQuery("#sb_overlay").show();


        			return false;

        		});

				jQuery("#sb_form_close").click(function(){

					jQuery("#submit_to_sb_form").hide();
					jQuery("#sb_overlay").hide();

					return false;

				});
        	});

        </script>';		
    echo $html;
}