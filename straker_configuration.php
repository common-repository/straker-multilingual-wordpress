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

/* straker Management Interface */
function qtrans_adminMenu() {
	global $menu, $submenu, $q_config;
	
	/* Configuration Page */
	add_options_page(__('Language Management', 'straker'), __('Languages', 'straker'), 'manage_options', 'straker', 'straker_conf');
	
	/* Language Switcher for Admin */
	
	// don't display menu if there is only 1 language active
	if(sizeof($q_config['enabled_languages']) <= 1) return;
	
	// generate menu with flags for every enabled language
	foreach($q_config['enabled_languages'] as $id => $language) {
		$link = add_query_arg('lang', $language);
		$link = (strpos($link, "wp-admin/") === false) ? preg_replace('#[^?&]*/#i', '', $link) : preg_replace('#[^?&]*wp-admin/#i', '', $link);
		if(strpos($link, "?")===0||strpos($link, "index.php?")===0) {
			if(current_user_can('manage_options')) 
				$link = 'options-general.php?page=straker&godashboard=1&lang='.$language; 
			else
				$link = 'edit.php?lang='.$language;
		}
		add_menu_page(__($q_config['language_name'][$language], 'straker'), __($q_config['language_name'][$language], 'straker'), 'read', $link, NULL, trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$language]);
	}
}

function straker_language_form($lang = '', $language_code = '', $language_name = '', $language_locale = '', $language_date_format = '', $language_time_format = '', $language_flag ='', $language_na_message = '', $language_default = '', $original_lang='') {
	global $q_config;
?>
<input type="hidden" name="original_lang" value="<?php echo $original_lang; ?>" />

<div class="form-field">
	<label for="language_code"><?php _e('Language Code', 'straker') ?></label>
	<input name="language_code" id="language_code" type="text" value="<?php echo $language_code; ?>" size="2" maxlength="2"/>
	<p><?php _e('2-Letter <a href="http://www.w3.org/WAI/ER/IG/ert/iso639.htm#2letter">ISO Language Code</a> for the Language you want to insert. (Example: en)', 'straker'); ?></p>
</div>
<div class="form-field">
	<label for="language_flag"><?php _e('Flag', 'straker') ?></label>
	<?php 
	$files = array();
	if($dir_handle = @opendir(trailingslashit(WP_CONTENT_DIR).$q_config['flag_location'])) {
		while (false !== ($file = readdir($dir_handle))) {
			if(preg_match("/\.(jpeg|jpg|gif|png)$/i",$file)) {
				$files[] = $file;
			}
		}
		sort($files);
	}
	if(sizeof($files)>0){
	?>
	<select name="language_flag" id="language_flag" onchange="switch_flag(this.value);"  onclick="switch_flag(this.value);" onkeypress="switch_flag(this.value);">
	<?php
		foreach ($files as $file) {
	?>
		<option value="<?php echo $file; ?>" <?php echo ($language_flag==$file)?'selected="selected"':''?>><?php echo $file; ?></option>
	<?php
		}
	?>
	</select>
	<img src="" alt="Flag" id="preview_flag" style="vertical-align:middle; display:none"/>
	<?php
	} else {
		_e('Incorrect Flag Image Path! Please correct it!', 'straker');
	}
	?>
	<p><?php _e('Choose the corresponding country flag for language. (Example: gb.png)', 'straker'); ?></p>
</div>
<script type="text/javascript">
//<![CDATA[
	function switch_flag(url) {
		document.getElementById('preview_flag').style.display = "inline";
		document.getElementById('preview_flag').src = "<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'];?>" + url;
	}
	
	switch_flag(document.getElementById('language_flag').value);
//]]>
</script>
<div class="form-field">
	<label for="language_name"><?php _e('Name', 'straker') ?></label>
	<input name="language_name" id="language_name" type="text" value="<?php echo $language_name; ?>"/>
	<p><?php _e('The Name of the language, which will be displayed on the site. (Example: English)', 'straker'); ?></p>
</div>
<div class="form-field">
	<label for="language_locale"><?php _e('Locale', 'straker') ?></label>
	<input name="language_locale" id="language_locale" type="text" value="<?php echo $language_locale; ?>"  size="5" maxlength="5"/>
	<p>
		<?php _e('PHP and Wordpress Locale for the language. (Example: en_US)', 'straker'); ?><br />
		<?php _e('You will need to install the .mo file for this language.', 'straker'); ?>
	</p>
</div>
<div class="form-field">
	<label for="language_date_format"><?php _e('Date Format', 'straker') ?></label>
	<input name="language_date_format" id="language_date_format" type="text" value="<?php echo $language_date_format; ?>"/>
	<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> (use %q for day suffix (st,nd,rd,th)) or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %A %B %e%q, %Y)', 'straker'); ?></p>
</div>
<div class="form-field">
	<label for="language_time_format"><?php _e('Time Format', 'straker') ?></label>
	<input name="language_time_format" id="language_time_format" type="text" value="<?php echo $language_time_format; ?>"/>
	<p><?php _e('Depending on your Date / Time Conversion Mode, you can either enter a <a href="http://www.php.net/manual/function.strftime.php">strftime</a> or <a href="http://www.php.net/manual/function.date.php">date</a> format. This field is optional. (Example: %I:%M %p)', 'straker'); ?></p>
</div>
<div class="form-field">
	<label for="language_na_message"><?php _e('Not Available Message', 'straker') ?></label>
	<input name="language_na_message" id="language_na_message" type="text" value="<?php echo $language_na_message; ?>"/>
	<p>
		<?php _e('Message to display if post is not available in the requested language. (Example: Sorry, this entry is only available in %LANG:, : and %.)', 'straker'); ?><br />
		<?php _e('%LANG:&lt;normal_seperator&gt;:&lt;last_seperator&gt;% generates a list of languages seperated by &lt;normal_seperator&gt; except for the last one, where &lt;last_seperator&gt; will be used instead.', 'straker'); ?><br />
	</p>
</div>
<?php
}

function qtrans_checkSetting($var, $updateOption = false, $type = QT_STRING) {
	global $q_config;
	switch($type) {
		case QT_URL:
			$_POST[$var] = trailingslashit($_POST[$var]);
		case QT_LANGUAGE:
		case QT_STRING:
			if(isset($_POST['submit']) && isset($_POST[$var])) {
				if($type != QT_LANGUAGE || qtrans_isEnabled($_POST[$var])) {
					$q_config[$var] = $_POST[$var];
				}
				if($updateOption) {
					update_option('straker_'.$var, $q_config[$var]);
				}
				return true;
			} else {
				return false;
			}
			break;
		case QT_BOOLEAN:
			if(isset($_POST['submit'])) {
				if(isset($_POST[$var])&&$_POST[$var]==1) {
					$q_config[$var] = true;
				} else {
					$q_config[$var] = false;
				}
				if($updateOption) {
					if($q_config[$var]) {
						update_option('straker_'.$var, '1');
					} else {
						update_option('straker_'.$var, '0');
					}
				}
				return true;
			} else {
				return false;
			}
			break;
		case QT_INTEGER:
			if(isset($_POST['submit']) && isset($_POST[$var])) {
				$q_config[$var] = intval($_POST[$var]);
				if($updateOption) {
					update_option('straker_'.$var, $q_config[$var]);
				}
				return true;
			} else {
				return false;
			}
			break;
	}
	return false;
}

function qtrans_language_columns($columns) {
	return array(
				'flag' => 'Flag',
				'name' => __('Name', 'straker'),
				'status' => __('Action', 'straker'),
				'status2' => '',
				'status3' => ''
				);
}

function straker_conf() {
	global $q_config, $wpdb;
	
	// do redirection for dashboard
	if(isset($_GET['godashboard'])) {
		echo '<h2>'.__('Switching Language', 'straker').'</h2>'.sprintf(__('Switching language to %1$s... If the Dashboard isn\'t loading, use this <a href="%2$s" title="Dashboard">link</a>.','straker'),$q_config['language_name'][qtrans_getLanguage()],admin_url()).'<script type="text/javascript">document.location="'.admin_url().'";</script>';
		exit();
	}
	
	// init some needed variables
	$error = '';
	$original_lang = '';
	$language_code = '';
	$language_name = '';
	$language_locale = '';
	$language_date_format = '';
	$language_time_format = '';
	$language_na_message = '';
	$language_flag = '';
	$language_default = '';
	$altered_table = false;
	
	$message = apply_filters('straker_configuration_pre','');
	
	// check for action
	if(isset($_POST['straker_reset']) && isset($_POST['straker_reset2'])) {
		$message = __('straker has been reset.', 'straker');
	} elseif(isset($_POST['default_language'])) {
		// save settings
		qtrans_checkSetting('default_language',			true, QT_LANGUAGE);
		qtrans_checkSetting('flag_location',			true, QT_URL);
		qtrans_checkSetting('ignore_file_types',		true, QT_STRING);
		qtrans_checkSetting('detect_browser_language',	true, QT_BOOLEAN);
		qtrans_checkSetting('hide_untranslated',		true, QT_BOOLEAN);
		qtrans_checkSetting('use_strftime',				true, QT_INTEGER);
		qtrans_checkSetting('url_mode',					true, QT_INTEGER);
		qtrans_checkSetting('auto_update_mo',			true, QT_BOOLEAN);
		qtrans_checkSetting('hide_default_language',	true, QT_BOOLEAN);
		if(isset($_POST['update_mo_now']) && $_POST['update_mo_now']=='1' && qtrans_updateGettextDatabases(true))
			$message = __('Gettext databases updated.', 'straker');
	}
	
	if(isset($_POST['original_lang'])) {
		// validate form input
		if($_POST['language_na_message']=='')		$error = __('The Language must have a Not-Available Message!', 'straker');
		if(strlen($_POST['language_locale'])<2)		$error = __('The Language must have a Locale!', 'straker');
		if($_POST['language_name']=='')				$error = __('The Language must have a name!', 'straker');
		if(strlen($_POST['language_code'])!=2)		$error = __('Language Code has to be 2 characters long!', 'straker');
		if($_POST['original_lang']==''&&$error=='') {
			// new language
			if(isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the same Language Code!', 'straker');
			} 
		} 
		if($_POST['original_lang']!=''&&$error=='') {
			// language update
			if($_POST['language_code']!=$_POST['original_lang']&&isset($q_config['language_name'][$_POST['language_code']])) {
				$error = __('There is already a language with the same Language Code!', 'straker');
			} else {
				// remove old language
				unset($q_config['language_name'][$_POST['original_lang']]);
				unset($q_config['flag'][$_POST['original_lang']]);
				unset($q_config['locale'][$_POST['original_lang']]);
				unset($q_config['date_format'][$_POST['original_lang']]);
				unset($q_config['time_format'][$_POST['original_lang']]);
				unset($q_config['not_available'][$_POST['original_lang']]);
				if(in_array($_POST['original_lang'],$q_config['enabled_languages'])) {
					// was enabled, so set modified one to enabled too
					for($i = 0; $i < sizeof($q_config['enabled_languages']); $i++) {
						if($q_config['enabled_languages'][$i] == $_POST['original_lang']) {
							$q_config['enabled_languages'][$i] = $_POST['language_code'];
						}
					}
				}
				if($_POST['original_lang']==$q_config['default_language'])
					// was default, so set modified the default
					$q_config['default_language'] = $_POST['language_code'];
			}
		}
		if(get_magic_quotes_gpc()) {
				if(isset($_POST['language_date_format'])) $_POST['language_date_format'] = stripslashes($_POST['language_date_format']);
				if(isset($_POST['language_time_format'])) $_POST['language_time_format'] = stripslashes($_POST['language_time_format']);
		}
		if($error=='') {
			// everything is fine, insert language
			$q_config['language_name'][$_POST['language_code']] = $_POST['language_name'];
			$q_config['flag'][$_POST['language_code']] = $_POST['language_flag'];
			$q_config['locale'][$_POST['language_code']] = $_POST['language_locale'];
			$q_config['date_format'][$_POST['language_code']] = $_POST['language_date_format'];
			$q_config['time_format'][$_POST['language_code']] = $_POST['language_time_format'];
			$q_config['not_available'][$_POST['language_code']] = $_POST['language_na_message'];
		}
		if($error!=''||isset($_GET['edit'])) {
			// get old values in the form
			$original_lang = $_POST['original_lang'];
			$language_code = $_POST['language_code'];
			$language_name = $_POST['language_name'];
			$language_locale = $_POST['language_locale'];
			$language_date_format = $_POST['language_date_format'];
			$language_time_format = $_POST['language_time_format'];
			$language_na_message = $_POST['language_na_message'];
			$language_flag = $_POST['language_flag'];
			$language_default = $_POST['language_default'];
		}
	} elseif(isset($_GET['convert'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors();
		foreach($q_config['enabled_languages'] as $lang) {
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_title = REPLACE(post_title, "[/lang_'.$lang.']","<!--:-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[lang_'.$lang.']","<!--:'.$lang.'-->")');
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = REPLACE(post_content, "[/lang_'.$lang.']","<!--:-->")');
		}
		$message = "Database Update successful!";
	} elseif(isset($_GET['markdefault'])){
		// update language tags
		global $wpdb;
		$wpdb->show_errors();
		$result = $wpdb->get_results('SELECT ID, post_title, post_content FROM '.$wpdb->posts.' WHERE NOT (post_content LIKE "%<!--:-->%" OR post_title LIKE "%<!--:-->%")');
		foreach($result as $post) {
			$content = qtrans_split($post->post_content);
			$title = qtrans_split($post->post_title);
			foreach($q_config['enabled_languages'] as $language) {
				if($language != $q_config['default_language']) {
					$content[$language] = "";
					$title[$language] = "";
				}
			}
			$content = qtrans_join($content);
			$title = qtrans_join($title);
			$wpdb->query('UPDATE '.$wpdb->posts.' set post_content = "'.mysql_escape_string($content).'", post_title = "'.mysql_escape_string($title).'" WHERE ID='.$post->ID);
		}
		$message = "All Posts marked as default language!";
	} elseif(isset($_GET['edit'])){
		$original_lang = $_GET['edit'];
		$language_code = $_GET['edit'];
		$language_name = $q_config['language_name'][$_GET['edit']];
		$language_locale = $q_config['locale'][$_GET['edit']];
		$language_date_format = $q_config['date_format'][$_GET['edit']];
		$language_time_format = $q_config['time_format'][$_GET['edit']];
		$language_na_message = $q_config['not_available'][$_GET['edit']];
		$language_flag = $q_config['flag'][$_GET['edit']];
	} elseif(isset($_GET['delete'])) {
		// validate delete (protect code)
		if($q_config['default_language']==$_GET['delete'])
			$error = 'Cannot delete Default Language!';
		if(!isset($q_config['language_name'][$_GET['delete']])||strtolower($_GET['delete'])=='code')
			$error = 'No such language!';
		if($error=='') {
			// everything seems fine, delete language
			qtrans_disableLanguage($_GET['delete']);
			unset($q_config['language_name'][$_GET['delete']]);
			unset($q_config['flag'][$_GET['delete']]);
			unset($q_config['locale'][$_GET['delete']]);
			unset($q_config['date_format'][$_GET['delete']]);
			unset($q_config['time_format'][$_GET['delete']]);
			unset($q_config['not_available'][$_GET['delete']]);
		}
	} elseif(isset($_GET['enable'])) {
		// enable validate
		if(!qtrans_enableLanguage($_GET['enable'])) {
			$error = __('Language is already enabled or invalid!', 'straker');
		}
	} elseif(isset($_GET['disable'])) {
		// enable validate
		if($_GET['disable']==$q_config['default_language'])
			$error = __('Cannot disable Default Language!', 'straker');
		if(!qtrans_isEnabled($_GET['disable']))
		if(!isset($q_config['language_name'][$_GET['disable']]))
			$error = __('No such language!', 'straker');
		// everything seems fine, disable language
		if($error=='' && !qtrans_disableLanguage($_GET['disable'])) {
			$error = __('Language is already disabled!', 'straker');
		}
	} elseif(isset($_GET['moveup'])) {
		$languages = qtrans_getSortedLanguages();
		$message = __('No such language!', 'straker');
		foreach($languages as $key => $language) {
			if($language==$_GET['moveup']) {
				if($key==0) {
					$message = __('Language is already first!', 'straker');
					break;
				}
				$languages[$key] = $languages[$key-1];
				$languages[$key-1] = $language;
				$q_config['enabled_languages'] = $languages;
				$message = __('New order saved.', 'straker');
				break;
			}
		}
	} elseif(isset($_GET['movedown'])) {
		$languages = qtrans_getSortedLanguages();
		$message = __('No such language!', 'straker');
		foreach($languages as $key => $language) {
			if($language==$_GET['movedown']) {
				if($key==sizeof($languages)-1) {
					$message = __('Language is already last!', 'straker');
					break;
				}
				$languages[$key] = $languages[$key+1];
				$languages[$key+1] = $language;
				$q_config['enabled_languages'] = $languages;
				$message = __('New order saved.', 'straker');
				break;
			}
		}
	}
	
	$everything_fine = ((isset($_POST['submit'])||isset($_GET['delete'])||isset($_GET['enable'])||isset($_GET['disable'])||isset($_GET['moveup'])||isset($_GET['movedown']))&&$error=='');
	if($everything_fine) {
		// settings might have changed, so save
		qtrans_saveConfig();
		if(empty($message)) {
			$message = __('Options saved.', 'straker');
		}
	}
	if($q_config['auto_update_mo']) {
		if(!is_dir(WP_LANG_DIR) || !$ll = @fopen(trailingslashit(WP_LANG_DIR).'straker.test','a')) {
			$error = sprintf(__('Could not write to "%s", Gettext Databases could not be downloaded!', 'straker'), WP_LANG_DIR);
		} else {
			@fclose($ll);
			@unlink(trailingslashit(WP_LANG_DIR).'straker.test');
		}
	}
	// don't accidently delete/enable/disable twice
	$clean_uri = preg_replace("/&(delete|enable|disable|convert|markdefault|moveup|movedown)=[^&#]*/i","",$_SERVER['REQUEST_URI']);
	$clean_uri = apply_filters('straker_clean_uri', $clean_uri);

// Generate XHTML

	?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
<?php endif; ?>
<?php if ($error!='') : ?>
<div id="message" class="error fade"><p><strong><?php echo $error; ?></strong></p></div>
<?php endif; ?>

<?php if(isset($_GET['edit'])) { ?>
<div class="wrap">
<h2><?php _e('Edit Language', 'straker'); ?></h2>
<form action="" method="post" id="straker-edit-language">
<?php straker_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_na_message, $language_default, $original_lang); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Changes &raquo;', 'straker'); ?>" /></p>
</form>
</div>
<?php } else { ?>
<div class="wrap">
<h2><?php _e('Language Management (straker Configuration)', 'straker'); ?></h2> 
<div class="tablenav"><?php printf(__('For help on how to configure straker correctly, take a look at the <a href="%1$s">straker FAQ</a> and the <a href="%2$s">Support Forum</a>.', 'straker'), 'http://www.strakertranslations.com', 'http://www.strakertranslations.com'); ?></div>
	<form action="<?php echo $clean_uri;?>" method="post">
		<h3><?php _e('General Settings', 'straker') ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Default Language / Order', 'straker') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('Default Language', 'straker') ?></legend>
				<?php
					foreach ( qtrans_getSortedLanguages() as $key => $language ) {
						echo "\t<label title='" . $q_config['language_name'][$language] . "'><input type='radio' name='default_language' value='" . $language . "'";
						if ( $language == $q_config['default_language'] ) {
							echo " checked='checked'";
						}
						echo ' />';
						echo ' <a href="'.add_query_arg('moveup', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'arrowup.png" alt="up" /></a>';
						echo ' <a href="'.add_query_arg('movedown', $language, $clean_uri).'"><img src="'.WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'arrowdown.png" alt="down" /></a>';
						echo ' <img src="' . trailingslashit(WP_CONTENT_URL) .$q_config['flag_location'].$q_config['flag'][$language] . '" alt="' . $q_config['language_name'][$language] . '" /> ';
						echo ' '.$q_config['language_name'][$language] . "</label><br />\n";
					}

				?>
					</br>
					<?php printf(__('Choose the default language of your blog. This is the language which will be shown on %s. You can also change the order the languages by clicking on the arrows above.', 'straker'), get_bloginfo('url')); ?>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Hide Untranslated Content', 'straker');?></th>
				<td>
					<label for="hide_untranslated"><input type="checkbox" name="hide_untranslated" id="hide_untranslated" value="1"<?php echo ($q_config['hide_untranslated'])?' checked="checked"':''; ?>/> <?php _e('Hide Content which is not available for the selected language.', 'straker'); ?></label>
					<br/>
					<?php _e('When checked, posts will be hidden if the content is not available for the selected language. If unchecked, a message will appear showing all the languages the content is available in.', 'straker'); ?>
					<?php _e('This function will not work correctly if you installed straker on a blog with existing entries. In this case you will need to take a look at "Convert Database" under "Advanced Settings".', 'straker'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Detect Browser Language', 'straker');?></th>
				<td>
					<label for="detect_browser_language"><input type="checkbox" name="detect_browser_language" id="detect_browser_language" value="1"<?php echo ($q_config['detect_browser_language'])?' checked="checked"':''; ?>/> <?php _e('Detect the language of the browser and redirect accordingly.', 'straker'); ?></label>
					<br/>
					<?php _e('When the frontpage is visited via bookmark/external link/type-in, the visitor will be forwarded to the correct URL for the language specified by his browser.', 'straker'); ?>
				</td>
			</tr>
		</table>
		<h3><?php _e('Advanced Settings', 'straker') ?><span id="straker-show-advanced" style="display:none"> (<a name="advanced_settings" href="#advanced_settings" onclick="showAdvanced();"><?php _e('Show', 'straker'); ?></a>)</span></h3>
		<table class="form-table" id="straker-advanced">
			<tr>
				<th scope="row"><?php _e('URL Modification Mode', 'straker') ?></th>
				<td>
					<fieldset><legend class="hidden"><?php _e('URL Modification Mode', 'straker') ?></legend>
						<label title="Query Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_QUERY; ?>" <?php echo ($q_config['url_mode']==QT_URL_QUERY)?"checked=\"checked\"":""; ?> /> <?php _e('Use Query Mode (?lang=en)', 'straker'); ?></label><br />
						<label title="Pre-Path Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_PATH; ?>" <?php echo ($q_config['url_mode']==QT_URL_PATH)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Path Mode (Default, puts /en/ in front of URL)', 'straker'); ?></label><br />
						<label title="Pre-Domain Mode"><input type="radio" name="url_mode" value="<?php echo QT_URL_DOMAIN; ?>" <?php echo ($q_config['url_mode']==QT_URL_DOMAIN)?"checked=\"checked\"":""; ?> /> <?php _e('Use Pre-Domain Mode (uses http://en.yoursite.com)', 'straker'); ?></label><br />
					</fieldset><br/>
					<?php _e('Pre-Path and Pre-Domain mode will only work with mod_rewrite/pretty permalinks. Additional Configuration is needed for Pre-Domain mode!', 'straker'); ?><br/>
					<label for="hide_default_language"><input type="checkbox" name="hide_default_language" id="hide_default_language" value="1"<?php echo ($q_config['hide_default_language'])?' checked="checked"':''; ?>/> <?php _e('Hide URL language information for default language.', 'straker'); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Flag Image Path', 'straker');?></th>
				<td>
					<?php echo trailingslashit(WP_CONTENT_URL); ?><input type="text" name="flag_location" id="flag_location" value="<?php echo $q_config['flag_location']; ?>" style="width:50%"/>
					<br/>
					<?php _e('Path to the flag images under wp-content, with trailing slash. (Default: plugins/straker/flags/)', 'straker'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Ignore Links', 'straker');?></th>
				<td>
					<input type="text" name="ignore_file_types" id="ignore_file_types" value="<?php echo $q_config['ignore_file_types']; ?>" style="width:100%"/>
					<br/>
					<?php _e('Don\'t convert Links to files of the given file types. (Default: gif,jpg,jpeg,png,pdf,swf,tif,rar,zip,7z,mpg,divx,mpeg,avi,css,js)', 'straker'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Update Gettext Databases', 'straker');?></th>
				<td>
					<label for="auto_update_mo"><input type="checkbox" name="auto_update_mo" id="auto_update_mo" value="1"<?php echo ($q_config['auto_update_mo'])?' checked="checked"':''; ?>/> <?php _e('Automatically check for .mo-Database Updates of installed languages.', 'straker'); ?></label>
					<br/>
					<label for="update_mo_now"><input type="checkbox" name="update_mo_now" id="update_mo_now" value="1" /> <?php _e('Update Gettext databases now.', 'straker'); ?></label>
					<br/>
					<?php _e('straker will query the Wordpress Localisation Repository every week and download the latest Gettext Databases (.mo Files).', 'straker'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Date / Time Conversion', 'straker');?></th>
				<td>
					<label><input type="radio" name="use_strftime" value="<?php echo QT_DATE; ?>" <?php echo ($q_config['use_strftime']==QT_DATE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function.', 'straker'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_DATE_OVERRIDE; ?>" <?php echo ($q_config['use_strftime']==QT_DATE_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use emulated date function and replace formats with the predefined formats for each language.', 'straker'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_STRFTIME; ?>" <?php echo ($q_config['use_strftime']==QT_STRFTIME)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date.', 'straker'); ?></label><br />
					<label><input type="radio" name="use_strftime" value="<?php echo QT_STRFTIME_OVERRIDE; ?>" <?php echo ($q_config['use_strftime']==QT_STRFTIME_OVERRIDE)?' checked="checked"':''; ?>/> <?php _e('Use strftime instead of date and replace formats with the predefined formats for each language.', 'straker'); ?></label><br />
					<?php _e('Depending on the mode selected, additional customizations of the theme may be needed.', 'straker'); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Reset straker', 'straker');?></th>
				<td>
					<label for="straker_reset"><input type="checkbox" name="straker_reset" id="straker_reset" value="1"/> <?php _e('Check this box and click Save Changes to reset all straker settings.', 'straker'); ?></label>
					<br/>
					<label for="straker_reset2"><input type="checkbox" name="straker_reset2" id="straker_reset2" value="1"/> <?php _e('Yes, I really want to reset straker.', 'straker'); ?></label>
					<br/>
					<label for="straker_reset3"><input type="checkbox" name="straker_reset3" id="straker_reset3" value="1"/> <?php _e('Also delete Translations for Categories/Tags/Link Categories.', 'straker'); ?></label>
					<br/>
					<?php _e('If something isn\'t working correctly, you can always try to reset all straker settings. A Reset won\'t delete any posts but will remove all settings (including all languages added).', 'straker'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Convert Database', 'straker');?></th>
				<td>
					<?php printf(__('If you are updating from straker 1.x or Polyglot, <a href="%s">click here</a> to convert posts to the new language tag format.', 'straker'), $clean_uri.'&convert=true'); ?>
					<?php printf(__('If you have installed straker for the first time on a Wordpress with existing posts, you can either go through all your posts manually and save them in the correct language or <a href="%s">click here</a> to mark all existing posts as written in the default language.', 'straker'), $clean_uri.'&markdefault=true'); ?>
					<?php _e('Both processes are <b>irreversible</b>! Be sure to make a full database backup before clicking one of the links.', 'straker'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Debugging Information', 'straker');?></th>
				<td>
					<p><?php printf(__('If you encounter any problems and you are unable to solve them yourself, you can visit the <a href="%s">Support Forum</a>. Posting the following Content will help other detect any misconfigurations.', 'straker'), 'http://www.strakersoftware.com/straker/forum/'); ?></p>
					<textarea readonly="readonly" id="straker_debug"><?php
						$q_config_copy = $q_config;
						// remove information to keep data anonymous and other not needed things
						unset($q_config_copy['url_info']);
						unset($q_config_copy['js']);
						unset($q_config_copy['windows_locale']);
						unset($q_config_copy['pre_domain']);
						unset($q_config_copy['term_name']);
						echo htmlspecialchars(print_r($q_config_copy, true));
					?></textarea>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
		// <![CDATA[
			function showAdvanced() {
				document.getElementById('straker-advanced').style.display='block';
				document.getElementById('straker-show-advanced').style.display='none';
				return false;
			}
			
			if(location.hash!='#advanced_settings') {
					document.getElementById('straker-show-advanced').style.display='inline';
					document.getElementById('straker-advanced').style.display='none';
			}
		// ]]>
		</script>
<?php do_action('straker_configuration', $clean_uri); ?>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'straker') ?>" />
		</p>
	</form>

</div>
<div class="wrap">

<h2><?php _e('Languages', 'straker') ?></h2>
<div id="col-container">

<div id="col-right">
<div class="col-wrap">

<table class="widefat">
	<thead>
	<tr>
<?php print_column_headers('language'); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers('language', false); ?>
	</tr>
	</tfoot>

	<tbody id="the-list" class="list:cat">
<?php foreach($q_config['language_name'] as $lang => $language){ if($lang!='code') { ?>
	<tr>
		<td><img src="<?php echo trailingslashit(WP_CONTENT_URL).$q_config['flag_location'].$q_config['flag'][$lang]; ?>" alt="<?php echo $language; ?> Flag"></td>
		<td><?php echo $language; ?></td>
		<td><?php if(in_array($lang,$q_config['enabled_languages'])) { ?><a class="edit" href="<?php echo $clean_uri; ?>&disable=<?php echo $lang; ?>"><?php _e('Disable', 'straker'); ?></a><?php  } else { ?><a class="edit" href="<?php echo $clean_uri; ?>&enable=<?php echo $lang; ?>"><?php _e('Enable', 'straker'); ?></a><?php } ?></td>
		<td><a class="edit" href="<?php echo $clean_uri; ?>&edit=<?php echo $lang; ?>"><?php _e('Edit', 'straker'); ?></a></td>
		<td><?php if($q_config['default_language']==$lang) { ?><?php _e('Default', 'straker'); ?><?php  } else { ?><a class="delete" href="<?php echo $clean_uri; ?>&delete=<?php echo $lang; ?>"><?php _e('Delete', 'straker'); ?></a><?php } ?></td>
	</tr>
<?php }} ?>
	</tbody>
</table>
<p><?php _e('Enabling a language will cause straker to update the Gettext-Database for the language, which can take a while depending on your server\'s connection speed.','straker');?></p>
</div>
</div><!-- /col-right -->

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">
<h3><?php _e('Add Language', 'straker'); ?></h3>
<form name="addcat" id="addcat" method="post" class="add:the-list: validate">
<?php straker_language_form($language_code, $language_code, $language_name, $language_locale, $language_date_format, $language_time_format, $language_flag, $language_default, $language_na_message); ?>
<p class="submit"><input type="submit" name="submit" value="<?php _e('Add Language &raquo;', 'straker'); ?>" /></p>
</form></div>
</div>
</div><!-- /col-left -->

</div><!-- /col-container -->
<?php
}
}
?>