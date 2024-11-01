<?php // encoding: utf-8

/*  

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
     Copyright 2008  Qian Qin  (email : mail@qianqin.de) 
 	This code has been modified based on the original source provided by Qian Qin qtranslate plugin. 
*/

/* Straker Services */


// check schedule
if (!wp_next_scheduled('qs_cron_hook')) {
	wp_schedule_event( time(), 'hourly', 'qs_cron_hook' );
}

define('QS_FAST_TIMEOUT',						10);
define('QS_VERIFY',								'verify');
define('QS_GET_SERVICES',						'get_services');
define('QS_INIT_TRANSLATION',					'init_translation');
define('QS_RETRIEVE_TRANSLATION',				'retrieve_translation');
define('QS_STATE_OPEN',							'open');
define('QS_STATE_ERROR',						'error');
define('QS_STATE_CLOSED',						'closed');
define('QS_ERROR_INVALID_LANGUAGE',				'QS_ERROR_INVALID_LANGUAGE');
define('QS_ERROR_NOT_SUPPORTED_LANGUAGE',		'QS_ERROR_NOT_SUPPORTED_LANGUAGE');
define('QS_ERROR_INVALID_SERVICE',				'QS_ERROR_INVALID_SERVICE');
define('QS_ERROR_INVALID_ORDER',				'QS_ERROR_INVALID_ORDER');
define('QS_ERROR_SERVICE_GENERIC',				'QS_ERROR_SERVICE_GENERIC');
define('QS_ERROR_SERVICE_UNKNOWN',				'QS_ERROR_SERVICE_UNKNOWN');
define('QS_DEBUG',								'QS_DEBUG');

// error messages
$qs_error_messages[QS_ERROR_INVALID_LANGUAGE] =			__('The language/s do not have a valid ISO 639-1 representation.','straker');
$qs_error_messages[QS_ERROR_NOT_SUPPORTED_LANGUAGE] =	__('The language/s you used are not supported by the service.','straker');
$qs_error_messages[QS_ERROR_INVALID_SERVICE] =			__('There is no such service.','straker');
$qs_error_messages[QS_ERROR_INVALID_ORDER] =			__('The system could not process your order.','straker');
$qs_error_messages[QS_ERROR_SERVICE_GENERIC] =			__('There has been an error with the selected service.','straker');
$qs_error_messages[QS_ERROR_SERVICE_UNKNOWN] =			__('An unknown error occured with the selected service.','straker');
$qs_error_messages[QS_DEBUG] =							__('The server returned a debugging message.','straker');

// hooks
add_action('straker_css',					'qs_css');
add_action('qs_cron_hook',						'qs_cron');
add_action('straker_configuration',			'qs_config_hook');
add_action('straker_loadConfig',				'qs_load');
add_action('straker_saveConfig',				'qs_save');
add_action('straker_clean_uri',				'qs_clean_uri');
add_action('admin_menu',						'qs_init');

add_filter('manage_order_columns',				'qs_order_columns');
add_filter('straker_configuration_pre',		'qs_config_pre_hook');




// get service details
function qs_queryQS($action, $data='', $fast = false) {


	if ($action == 'verify'){
		return true;
	}


	$arService["service_id"] =  "1";
	$arService["service_name"] =  "Straker";
	$arService["service_url"] =  "http://www.strakertranslations.com";
	$arService["service_description"] =  "";
	$arService["service_required_fields"] =  "apiurl APIURL|apikey APIKEY|notification_email EMAIL";
	
	$arReturn[1] = $arService;
	
	return qs_cleanup($arReturn, $action);

	}
	
// sends a encrypted message to straker Services and decrypts the received data
function qs_sendTranslation($sl,$tl,$content,$post_title,$post_excerpt){

global $q_config, $qs_public_key, $qs_error_messages;
		
if($q_config['straker_services']) { 
		$service_settings = get_option('qs_service_settings');
		$services = qs_queryQS(QS_GET_SERVICES);
		$orders = get_option('qs_orders');
	}
		
	
		
$uri = $service_settings[1]['apiurl'];
$apiKey = $service_settings[1]['apikey']; // change this to your API key
$content = $content; // this is the source text
$sl = $sl; // this is the source language code for English
$tl = $tl; // this is the target language code for Spanish
// $token // you can pass in a token to identify this translation in your system, for example it may be the ID of the content object in your CMS or application
$notification_email = $service_settings[1]['notification_email']; // email for translation notifications
$title = $post_title; // A friendly name that for the translation job. Specify this to easily identify jobs later
$description = $post_excerpt;
header('Content-type: text/html; charset=UTF-8');
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL, $uri);
$data = array(
'api_key' => $apiKey,
'sl' => $sl,
'tl' => $tl,
// 'token' => $token,
'content' => $content,
'description' => $description,
'notification_email' => $notification_email,
'title' => $title,
);


curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($ch);


return json_decode($result);



}

function qs_getTranslation($order_id,$lang,$translation_id){

	global $q_config, $qs_public_key, $qs_error_messages;
		
	if($q_config['straker_services']) { 
		$service_settings = get_option('qs_service_settings');
	}
	   
	
			
    $uri    = $service_settings[1]['apiurl']."?apiKey=".$service_settings[1]['apikey']."&id=".$order_id."&tl=".$lang."&translation_id=".$translation_id;
    
    
    
    header('Content-type: text/html; charset=UTF-8');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $uri);
    
    
    $result = curl_exec($ch);	

         

	return json_decode($result);		

}




function qs_clean_uri($clean_uri) {
	return preg_replace("/&(qs_delete|qs_cron)=[^&#]*/i","",$clean_uri);
}

function qs_translateButtons($available_languages, $missing_languages) {
	global $q_config, $post;
	if(sizeof($missing_languages)==0) return;
	$missing_languages_name = array();
	foreach($missing_languages as $language) {
		$missing_languages_name[] = '<a href="edit.php?page=straker_services&post='.$post->ID.'&target_language='.$language.'">'.$q_config['language_name'][$language].'</a>';
	}
	$missing_languages_names = join(', ', $missing_languages_name);
	printf(__('<div>Translate to %s</div>', 'straker') ,$missing_languages_names);
}

function qs_css() {
	echo "#qs_content_preview { width:100%; height:200px }";
	echo ".service_description { margin-left:20px; margin-top:0 }";
	echo "#straker-services h4 { margin-top:0 }";
	echo "#straker-services h5 { margin-bottom:0 }";
	echo "#straker-services .description { font-size:11px }";
	echo "#qtrans_select_translate { margin-right:11px }";
	echo ".qs_status { border:0 }";
	echo ".qs_no-bottom-border { border-bottom:0 !important }";
}

function qs_load() {
	global $q_config, $qs_public_key;
	$straker_services = get_option('straker_straker_services');
	$straker_services = qtrans_validateBool($straker_services, $q_config['straker_services']);
	$q_config['straker_services'] = $straker_services && function_exists('openssl_get_publickey');
	if($q_config['straker_services'] && is_string($qs_public_key)) {
		$qs_public_key = openssl_get_publickey(join("\n",explode("|",$qs_public_key)));
	}
}

function qs_init() {
	global $q_config;
	if($q_config['straker_services']) {
	/* disabled for meta box
		add_filter('straker_toolbar',			'qs_toobar');
		add_filter('straker_modify_editor_js',	'qs_editor_js');
	*/
		//add_meta_box('translatediv', __('Translate to','straker'), 'qs_translate_box', 'post', 'side', 'core');
		//add_meta_box('translatediv', __('Translate to','straker'), 'qs_translate_box', 'page', 'side', 'core');
		
		add_action('straker_languageColumn',			'qs_translateButtons', 10, 2);
		
		// add plugin page without menu link for users with permission
		if(current_user_can('edit_published_posts')) {
			//add_posts_page(__('Translate','straker'), __('Translate','straker'), 'edit_published_posts', 'straker_services', 'qs_service');
			global $_registered_pages;
			$hookname = get_plugin_page_hookname('straker_services', 'edit.php');
			add_action($hookname, 'qs_service');
			$_registered_pages[$hookname] = true;
		}
	}
}

function qs_save() {
	global $q_config;
	if($q_config['straker_services'])
		update_option('straker_straker_services', '1');
	else
		update_option('straker_straker_services', '0');
}

function qs_cleanup($var, $action) {
	switch($action) {
		case QS_GET_SERVICES:
			foreach($var as $service_id => $service) {
				// make array out ouf serialized field
				$fields = array();
				$required_fields = explode('|',$service['service_required_fields']);
				foreach($required_fields as $required_field) {
					if(strpos($required_field, " ")!==false) {
						list($fieldname, $title) = explode(' ', $required_field, 2);
						if($fieldname!='') {
							$fields[] = array('name' => $fieldname, 'value' => '', 'title' => $title);
						}
					}
				}
				$var[$service_id]['service_required_fields'] = $fields;
			}
		break;
	}
	if(isset($var['error']) && $var['error'] == QS_DEBUG) {
		echo "<pre>Debug message received from Server: \n";
		var_dump($var['message']);
		echo "</pre>";
	}
	return $var;
}

function qs_config_pre_hook($message) {
	global $q_config;
	if(isset($_POST['default_language'])) {
		qtrans_checkSetting('straker_services', true, QT_BOOLEAN);
		qs_load();
		if($q_config['straker_services']) {
			$services = qs_queryQS(QS_GET_SERVICES);
			$service_settings = get_option('qs_service_settings');
			if(!is_array($service_settings)) $service_settings = array();
			
			foreach($services as $service_id => $service) {
				// check if there are already settings for the field
				if(!isset($service_settings[$service_id])||!is_array($service_settings[$service_id])) $service_settings[$service_id] = array();
				
				// update fields
				foreach($service['service_required_fields'] as $field) {
					if(isset($_POST['qs_'.$service_id.'_'.$field['name']])) {
						// skip empty passwords to keep the old value
						if($_POST['qs_'.$service_id.'_'.$field['name']]=='' && $field['name']=='password') continue;
						$service_settings[$service_id][$field['name']] = $_POST['qs_'.$service_id.'_'.$field['name']];
					}
				}
			}
			update_option('qs_service_settings', $service_settings);
		}
	}
	if(isset($_GET['qs_delete'])) {
		$_GET['qs_delete'] = intval($_GET['qs_delete']);
		$orders = get_option('qs_orders');
		if(is_array($orders)) {
			foreach($orders as $key => $order) {
				if($orders[$key]['order']['order_id'] == $_GET['qs_delete']) {
					unset($orders[$key]);
					update_option('qs_orders',$orders);
				}
			}
		}
		$message = __('Order deleted.','straker');
	}
	if(isset($_GET['qs_cron'])) {
		qs_cron();
		$message = __('Status updated for all open orders.','straker');
	}
	return $message;
}

function qs_translate_box($post) {
	global $q_config;
	$languages = qtrans_getSortedLanguages();
?>
	<ul>
<?php
	foreach($languages as $language) {
		if(isset($_REQUEST['post'])) {
?>
			<li><img src="<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$language]; ?>" alt="<?php echo $q_config['language_name'][$language]; ?>"> <a href="edit.php?page=straker_services&post=<?php echo intval($_REQUEST['post']); ?>&target_language=<?php echo $language; ?>"><?php echo $q_config['language_name'][$language]; ?></a></li>
<?php
		} else {
			echo '<li>'.__('Please save your post first.','straker').'</li>';
		}
	}
?>
	</ul>
<?php
}

function qs_order_columns($columns) {
	return array(
				'title' => __('Post Title', 'straker'),
				'service' => __('Service', 'straker'),
				'source_language' => __('Source Language', 'straker'),
				'target_language' => __('Target Language', 'straker'),
				'action' => __('Action', 'straker')
				);
}

function qs_config_hook($request_uri) {
	global $q_config;
?>
<h3><?php _e('straker Services Settings', 'straker') ?><span id="straker-show-services" style="display:none"> (<a name="straker_service_settings" href="#straker_service_settings" onclick="showServices();"><?php _e('Show', 'straker'); ?></a>)</span></h3>
<table class="form-table" id="straker-services">
	<tr>
		<th scope="row"><?php _e('straker Services', 'straker') ?></th>
		<td>
			<?php if(!function_exists('openssl_get_publickey')) { printf(__('<div id="message" class="error fade"><p>straker Services could not load <a href="%s">OpenSSL</a>!</p></div>'), 'http://www.php.net/manual/book.openssl.php'); } ?>
			<label for="straker_services"><input type="checkbox" name="straker_services" id="straker_services" value="1"<?php echo ($q_config['straker_services'])?' checked="checked"':''; ?>/> <?php _e('Enable Straker Translation Services', 'straker'); ?></label>
			<br/>
			<?php _e('With Straker Translation Services, you will be able to use professional human translation services with a few clicks.', 'straker'); ?><br />
			<?php _e('Save after enabling to see more Configuration options.', 'straker'); ?>
		</td>
	</tr>
<?php 
	if($q_config['straker_services']) { 
		$service_settings = get_option('qs_service_settings');
		$services = qs_queryQS(QS_GET_SERVICES);
		$orders = get_option('qs_orders');
		


/*
include_once("dBug.php");
new dBug($orders);
exit();

*/


?>
	<tr valign="top">
		<th scope="row"><h4><?php _e('Open Orders', 'straker'); ?></h4></th>
		<td>
<?php if(is_array($orders) && sizeof($orders)>0) { ?>
			<table class="widefat">
				<thead>
				<tr>
<?php print_column_headers('order'); ?>
				</tr>
				</thead>

				<tfoot>
				<tr>
<?php print_column_headers('order', false); ?>
				</tr>
				</tfoot>
<?php
		foreach($orders as $order) { 
			$post = &get_post($order['post_id']);
			if(!$post) continue;
			$post->post_title = wp_specialchars(qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($post->post_title));
?>
				<tr>
					<td class="qs_no-bottom-border"><a href="post.php?action=edit&post=<?php echo $order['post_id']; ?>" title="<?php printf(__('Edit %s', 'straker'),$post->post_title); ?>"><?php echo $post->post_title; ?></a></td>
					<td class="qs_no-bottom-border"><a href="<?php echo $services[$order['service_id']]['service_url']; ?>" title="<?php _e('Website', 'straker'); ?>"><?php echo $services[$order['service_id']]['service_name']; ?></a></td>
					<td class="qs_no-bottom-border"><?php echo $q_config['language_name'][$order['source_language']]; ?></td>
					<td class="qs_no-bottom-border"><?php echo $q_config['language_name'][$order['target_language']]; ?></td>
					<td class="qs_no-bottom-border"><a class="delete" href="<?php echo add_query_arg('qs_delete', $order['order']['order_id'], $request_uri); ?>#straker_service_settings">Delete</a></td>
				</tr>
<?php 
			if(isset($order['status'])) {
?>
				<tr class="qs_status">
					<td colspan="5">
						<?php printf(__('Current Status: %s','straker'), $order['status']); ?>
					</td>
				</tr>
<?php
			}
		}
?>
			</table>
			<p><?php printf(__('straker Services will automatically check every hour whether the translations are finished and update your posts accordingly. You can always <a href="%s">check manually</a>.','straker'),'options-general.php?page=straker&qs_cron=true#straker_service_settings'); ?></p>
			<p><?php _e('Deleting an open order doesn\'t cancel it. You will have to logon to the service homepage and cancel it there.','straker'); ?></p>
<?php } else { ?>
			<p><?php _e('No open orders.','straker'); ?></p>
<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h4><?php _e('Service Configuration', 'straker');?></h4>
			<p class="description"><?php _e('Below, you will find configuration settings for straker Service Providers, which are required for them to operate.', 'straker'); ?></p>
		</th>
	</tr>
<?php

		foreach($services as $service) {
			if(sizeof($service['service_required_fields'])>0) {
?>
	<tr valign="top">
		<th scope="row" colspan="2">
			<h5><?php _e($service['service_name']);?> ( <a name="qs_service_<?php echo $service['service_id']; ?>" href="<?php echo $service['service_url']; ?>"><?php _e('Website', 'straker'); ?></a> )</h5>
			<p class="description"><?php _e($service['service_description']); ?></p>
		</th>
	</tr>
<?php




				foreach($service['service_required_fields'] as $field) {

				

?>
	<tr valign="top">
		<th scope="row"><?php echo $field['title']; ?></th>
		<td>
			<input type="<?php echo ($field['name']=='password')?'password':'text';?>" name="<?php echo 'qs_'.$service['service_id']."_".$field['name']; ?>" value="<?php echo (isset($service_settings[$service['service_id']][$field['name']])&&$field['name']!='password')?$service_settings[$service['service_id']][$field['name']]:''; ?>" style="width:100%"/>
		</td>
	</tr>
<?php
				}
			}
		}
	}
?>
</table>
<script type="text/javascript">
// <![CDATA[
	function showServices() {
		document.getElementById('straker-services').style.display='block';
		document.getElementById('straker-show-services').style.display='none';
		return false;
	}
	
	if(location.hash!='#straker_service_settings') {
	document.getElementById('straker-show-services').style.display='inline';
	document.getElementById('straker-services').style.display='none';
	}
// ]]>
</script>
<?php
}

function qs_cron() {
	global $wpdb;
	// poll translations
	$orders = get_option('qs_orders');
	if(!is_array($orders)) return;
	foreach($orders as $key => $order) {
		qs_UpdateOrder($order['order']['order_id']);
	}
}

function qs_UpdateOrder($order_id) {
	global $wpdb;
	$orders = get_option('qs_orders');
	if(!is_array($orders)) return false;
			


	foreach($orders as $key => $order) {

		
		// search for wanted order
		if($order['order']['order_id']!=$order_id) continue;
		
		
		// query server for updates
		$order['order']['order_url'] = get_option('home');
		$order['order']['order_url'] = get_option('home');
		

   


		$response = qs_getTranslation($order['order']['order_id'],$order['target_language'],$order['order']['translation_id']);
/*
			include_once("dBug.php");
new dBug($order['order']['translation_id']);
exit();
*/


		if(isset($response->DATA->content[0])){
			$result['content'] = $response->DATA->content[0];
			$result['title'] = $response->DATA->title[0];
			$result['excerpt'] = $response->DATA->description[0];
			
			if( $response->DATA->translation_status[0] == 'SAVED'  ){
			$result['order_comment'] = "TRANSLATION NOT STARTED";
			$result['order_status'] = "not started";
			}
			
			if( $response->DATA->translation_status[0] == 'TRANSLATION_SENT'  ){
			$result['order_comment'] = "TRANSLATION IN PROGRESS";
			$result['order_status'] = "in progress";
			}
			
				if( $response->DATA->translation_status[0] == 'TRANSLATION_PUBLISHED'  ){
			$result['order_comment'] = "Returned OK";
			$result['order_status'] = "closed";
			}
			
			
		}
			
		
		if(isset($result['order_comment'])) $orders[$key]['status'] = $result['order_comment'];
		
		
		// update db if post is updated
		if(isset($result['order_status']) && $result['order_status']==QS_STATE_CLOSED) {
		
		
			$order['post_id'] = intval($order['post_id']);
			$post = &get_post($order['post_id']);
			$title = qtrans_split($post->post_title);
			$content = qtrans_split($post->post_content);
			$title[$order['target_language']] = $result['title'];
			$content[$order['target_language']] = $result['content'];
			$excerpt[$order['target_language']] = $result['excerpt'];
			$post->post_title = qtrans_join($title);
			$post->post_excerpt = qtrans_join($excerpt);
			$post->post_content = qtrans_join($content);
			$wpdb->show_errors();
			$wpdb->query('UPDATE '.$wpdb->posts.' SET post_title="'.mysql_escape_string($post->post_title).'",post_excerpt = "'.mysql_escape_string($post->post_excerpt).'", post_content = "'.mysql_escape_string($post->post_content).'" WHERE ID = "'.$post->ID.'"');
			wp_cache_add($post->ID, $post, 'posts');
			

			//unset($orders[$key]);
		}
		

		update_option('qs_orders',$orders);
		return true;
	}
	return false;
}

function qs_service() {
	global $q_config, $qs_public_key, $qs_error_messages;
	if(!isset($_REQUEST['post'])) {
		echo '<script type="text/javascript">document.location="edit.php";</script>';
		printf(__('To translate a post, please go to the <a href="%s">edit posts overview</a>.','straker'), 'edit.php');
		exit();
	}
	$post_id = intval($_REQUEST['post']);
	$translate_from  = '';
	if(isset($_REQUEST['source_language'])&&qtrans_isEnabled($_REQUEST['source_language']))
		$translate_from = $_REQUEST['source_language'];
	if(isset($_REQUEST['target_language'])&&qtrans_isEnabled($_REQUEST['target_language']))
		$translate_to = $_REQUEST['target_language'];
		
	/*
		if($_REQUEST['target_language'] == 'zh_CN'){
			//$translate_to = $q_config['locale'][$_REQUEST['target_language']];
			$translate_to = 'zh';
		}
*/

		
	if($translate_to == $translate_from) $translate_to = '';
	$post = &get_post($post_id);
	if(!$post) {
		printf(__('Post with id "%s" not found!','straker'), $post_id);
		return;
	}
	$default_service = intval(get_option('qs_default_service'));
	
	$service_settings = get_option('qs_service_settings');
	
		

	// Detect available Languages and possible target languages
	$available_languages = qtrans_getAvailableLanguages($post->post_content);
	if(sizeof($available_languages)==0) {
		$error = __('The requested Post has no content, no Translation possible.', 'straker');
	}
	
	// try to guess source and target language
	if(!in_array($translate_from, $available_languages)) $translate_from = '';
	$missing_languages = array_diff($q_config['enabled_languages'], $available_languages);
	if(empty($translate_from) && in_array($q_config['default_language'], $available_languages) && $translate_to!=$q_config['default_language']) $translate_from = $q_config['default_language'];
	if(empty($translate_to) && sizeof($missing_languages)==1) $translate_to = $missing_languages[0];
	if(in_array($translate_to, $available_languages)) {
		$message = __('The Post already has content for the selected target language. If a translation request is send, the current text for the target language will be overwritten.','straker');
	}
	if(sizeof($available_languages)==1) {
		if($available_languages[0] == $translate_to) {
			unset($translate_to);
		}
		$translate_from = $available_languages[0];
	} elseif($translate_from == '' && sizeof($available_languages) > 1) {
		$languages = qtrans_getSortedLanguages();
		foreach($languages as $language) {
			if($language != $translate_to && in_array($language, $available_languages)) {
				$translate_from = $language;
				break;
			}
		}
	}
	


	
	// link to current page with get variables
	$url_link = add_query_arg('post', $post_id);
	if(!empty($translate_to)) $url_link = add_query_arg('target_language', $translate_to, $url_link);
	if(!empty($translate_from)) $url_link = add_query_arg('source_language', $translate_from, $url_link);
	
	// get correct title and content
	$post_title = qtrans_use($translate_from,$post->post_title);
	$post_content = qtrans_use($translate_from,$post->post_content);
	$post_excerpt = qtrans_use($translate_from,$post->post_excerpt);

	
	if(isset($translate_from) && isset($translate_to)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s to %3$s', htmlspecialchars($post_title), $q_config['language_name'][$translate_from], $q_config['language_name'][$translate_to]);
	} elseif(isset($translate_from)) {
		$title = sprintf('Translate &quot;%1$s&quot; from %2$s', htmlspecialchars($post_title), $q_config['language_name'][$translate_from]);
	} else {
		$title = sprintf('Translate &quot;%1$s&quot;', htmlspecialchars($post_title));
	}
	
	// Check data
	
	if(isset($_POST['service_id'])) {
		$service_id = intval($_POST['service_id']);
		$default_service = $service_id;
		update_option('qs_default_service', $service_id);
		$order_key = substr(md5(time().AUTH_KEY),0,20);
		$request = array(
				'order_service_id' => $service_id,
				'order_url' => get_option('home'),
				'order_key' => $order_key,
				'order_title' => $post_title,
				'order_text' => $post_content,
				'order_description' => $post_excerpt,
				'order_source_language' => $translate_from,
				'order_source_locale' => $q_config['locale'][$translate_from],
				'order_target_language' => $translate_to,
				'order_target_locale' => $q_config['locale'][$translate_to]
			);
			
	
		// check for additional fields
		if(is_array($service_settings[$service_id])) {
			$request['order_required_field'] = array();
			foreach($service_settings[$service_id] as $setting => $value) {
				$request['order_required_field'][$setting] = $value;
			}
		}


	
		// send content for translation
		$results =  qs_sendTranslation($translate_from,$translate_to,$post_content,$post_title,$post_excerpt);
		


		
		// get the order id that is returned 
		$answer['order_id'] = $results->DATA->id[0];
		$answer['translation_id'] = $results->DATA->translation_id[0];
		

		
		if(isset($answer['error'])) {
			$error = sprintf(__('An error occured: %s', 'straker'), $qs_error_messages[$answer['error']]);
			if($answer['message']!='') {
				$error.='<br />'.sprintf(__('Additional information: %s', 'straker'), qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($answer['message']));
			}
		}
		if(isset($answer['order_id'])) {
			$orders = get_option('qs_orders');
			if(!is_array($orders)) $orders = array();
			$orders[] = array('post_id'=>$post_id, 'service_id' => $service_id, 'source_language'=>$translate_from, 'target_language'=>$translate_to, 'order' => array('order_key' => $order_key, 'order_id' => $answer['order_id'], 'translation_id' => $answer['translation_id']));
			
			

			update_option('qs_orders', $orders);
			
			if(empty($answer['message'])) {
				$order_completed_message = '';
			} else {
				$order_completed_message = htmlspecialchars($answer['message']);
			}
			

		
			qs_UpdateOrder($answer['order_id']);
		}
		

		
		
	}
	if(isset($error)) {
?>
<div class="wrap">
<h2><?php _e('straker Services', 'straker'); ?></h2>
<div id="message" class="error fade"><p><?php echo $error; ?></p></div>
<p><?php printf(__('An serious error occured and straker Services cannot proceed. For help, please visit the <a href="%s">Support Forum</a>','straker'), 'http://www.strakersoftware.com');?></p>
</div>
<?php
	return;
	}
	if(isset($order_completed_message)) {
?>
<div class="wrap">
<h2><?php _e('straker Services', 'straker'); ?></h2>
<div id="message" class="updated fade"><p><?php _e('Order successfully sent.', 'straker'); ?></p></div>
<p><?php _e('Your translation order has been successfully transfered to the selected service.','straker'); ?></p>
<?php
		if(!empty($order_completed_message)) {
?>
<p><?php printf(__('The service returned this message: %s','straker'), $order_completed_message);?></p>
<?php
		}
?>
<p><?php _e('Feel free to choose an action:', 'straker'); ?></p>
<ul>
	<li><a href="<?php echo add_query_arg('target_language', null, $url_link); ?>"><?php _e('Translate this post to another language.', 'straker'); ?></a></li>
	<li><a href="edit.php"><?php _e('Translate a different post.', 'straker'); ?></a></li>
	<li><a href="options-general.php?page=straker#straker_service_settings"><?php _e('View all open orders.', 'straker'); ?></a></li>
	<li><a href="options-general.php?page=straker&qs_cron=true#straker_service_settings"><?php _e('Let straker Services check if any open orders are finished.', 'straker'); ?></a></li>
	<li><a href="<?php echo get_permalink($post_id); ?> "><?php _e('View this post.', 'straker'); ?></a></li>
</ul>
</div>
<?php
		return;
	}
?>
<div class="wrap">
<h2><?php _e('straker Services', 'straker'); ?></h2>
<?php
if(!empty($message)) {
?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php
}
?>
<h3><?php echo $title;?></h3>
<form action="edit.php?page=straker_services" method="post" id="straker-services-translate">
<p><?php
	if(sizeof($available_languages)>1) {
		$available_languages_name = array();
		foreach(array_diff($available_languages,array($translate_from)) as $language) {
			$available_languages_name[] = '<a href="'.add_query_arg('source_language',$language, $url_link).'">'.$q_config['language_name'][$language].'</a>';
		}
		$available_languages_names = join(", ", $available_languages_name);
		printf(__('Your article is available in multiple languages. If you do not want to translate from %1$s, you can switch to one of the following languages: %2$s', 'straker'),$q_config['language_name'][$translate_from],$available_languages_names);
	}
?></p>
<input type="hidden" name="post" value="<?php echo $post_id; ?>"/>
<input type="hidden" name="source_language" value="<?php echo $translate_from; ?>"/>
<?php
	if(empty($translate_to)) {
?>
<p><?php _e('Please choose the language you want to translate to:', 'straker');?></p>
<ul>
<?php 
		foreach($q_config['enabled_languages'] as $language) {
			if($translate_from == $language) continue;
?>
	<li><label><input type="radio" name="target_language" value="<?php echo $language;?>" /> <?php echo $q_config['language_name'][$language]; ?></li>
<?php
		}
?>
</ul>
	<p class="submit">
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Continue', 'straker') ?>" />
	</p>
<?php
	} else {
?>
<p><?php printf(__('Please review your article and <a href="%s">edit</a> it if needed.', 'straker'),'post.php?action=edit&post='.$post_id); ?></p>
<textarea name="qs_content_preview" id="qs_content_preview" readonly="readonly"><?php echo $post_content; ?></textarea>
<?php
		$timestamp = time();
		

		
		if($timestamp != qs_queryQS(QS_VERIFY, $timestamp)) {
?>
<p class="error"><?php _e('ERROR: Could not connect to straker Services. Please try again later.', 'straker');?></p>
<?php
			return;
		}
	
?>
<h4><?php _e('Use the following Translation Service:', 'straker'); ?></h4>
<ul>
<?php
		if($services = qs_queryQS(QS_GET_SERVICES)) {
			foreach($services as $service_id => $service) {
				// check if we have data for all required fields
				$requirements_matched = true;
				foreach($service['service_required_fields'] as $field) {
					if(!isset($service_settings[$service_id][$field['name']]) || $service_settings[$service_id][$field['name']] == '') $requirements_matched = false;
				}
				if(!$requirements_matched) {
?>
<li>
	<label><input type="radio" name="service_id" disabled="disabled" /> <b><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'straker'); ?></a> )</label>
	<p class="error"><?php printf(__('Cannot use this service, not all <a href="%s">required fields</a> filled in for this service.','straker'), 'options-general.php?page=straker#qs_service_'.$service_id); ?></p>
	<p class="service_description"><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_description']); ?></p>
</li>
<?php
				} else {
?>
<li><label><input type="radio" name="service_id" <?php if($default_service==$service['service_id']) echo 'checked="checked"';?> value="<?php echo $service['service_id'];?>" /> <b><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_name']); ?></b> ( <a href="<?php echo $service['service_url']; ?>" target="_blank"><?php _e('Website', 'straker'); ?></a> )</label><p class="service_description"><?php echo qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($service['service_description']); ?></p></li>
<?php
				}
			}
?>
</ul>
<p><?php _e('Your article will be SSL encrypted and securly sent to straker Services, which will forward your text to the chosen Translation Service. Once straker Services receives the translated text, it will automatically appear on your blog.', 'straker'); ?></p>
	<p class="submit">
		<input type="hidden" name="target_language" value="<?php echo $translate_to; ?>"/>
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Request Translation', 'straker') ?>" />
	</p>
<?php
		}
	}
?>
</div>
</form>
<?php
}

function qs_toobar($content) {
	// Create Translate Button 
	$content .= qtrans_createEditorToolbarButton('translate', 'translate', 'init_qs', __('Translate'));
	return $content;
}

function qs_editor_js($content) {
	$content .= "
		init_qs = function(action, id) {
			document.location.href = 'edit.php?page=straker_services&post=".intval($_REQUEST['post'])."';
		}
		";
	return $content;
}

?>