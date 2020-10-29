<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Display After Post Content
 * Description: Allows insertion of content configured through admin panel to be displayed after the post content; works with shortcodes including Contact Form 7 and is multisite compatible.
 * Version: 1.2.0
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/display-after-post-content/
 * Text Domain: display-after-post-content
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_dapc');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */
// add actions
add_action('admin_menu', 'azrcrv_dapc_create_admin_menu');
add_action('admin_post_azrcrv_dapc_save_options', 'azrcrv_dapc_save_options');
add_action('network_admin_menu', 'azrcrv_dapc_create_network_admin_menu');
add_action('network_admin_edit_azrcrv_dapc_save_network_options', 'azrcrv_dapc_save_network_options');
add_action('wp_enqueue_scripts', 'azrcrv_dapc_load_css');
add_action('plugins_loaded', 'azrcrv_dapc_load_languages');

// add filters
add_filter('plugin_action_links', 'azrcrv_dapc_add_plugin_action_link', 10, 2);
add_filter ('the_content', 'azrcrv_dapc_display_after_post_content');
add_filter('codepotent_update_manager_image_path', 'azrcrv_dapc_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_dapc_custom_image_url');

// add shortcodes
add_shortcode('shortcode', 'shortcode_function');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('display-after-post-content', false, $plugin_rel_path);
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_load_css(){
	wp_enqueue_style('azrcrv-dapc', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Custom plugin image path.
 *
 * @since 1.2.0
 *
 */
function azrcrv_dapc_custom_image_path($path){
    if (strpos($path, 'azrcrv-display-after-post-content') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.2.0
 *
 */
function azrcrv_dapc_custom_image_url($url){
    if (strpos($url, 'azrcrv-display-after-post-content') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Get options including defaults.
 *
 * @since 1.2.0
 *
 */
function azrcrv_dapc_get_option($option_name){
 
	$defaults = array(
						'display_after_post_content' => '',
					);

	$options = get_option($option_name, $defaults);

	$options = wp_parse_args($options, $defaults);

	return $options;

}

/**
 * Add plugin action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-dapc').'"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'display-after-post-content').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Display After Post Content Settings", "display-after-post-content")
						,esc_html__("Display After Post Content", "display-after-post-content")
						,'manage_options'
						,'azrcrv-dapc'
						,'azrcrv_dapc_display_options');
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'display-after-post-content'));
    }
	
	// Retrieve plugin configuration options from database
	$options = azrcrv_dapc_get_option('azrcrv-dapc');
	?>
	<div id="azrcrv-dapc-general" class="wrap">
		<fieldset>
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php if(isset($_GET['settings-updated'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Settings have been saved.', 'display-after-post-content'); ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_dapc_save_options" />
				<input name="page_options" type="hidden" value="display_after_post_content" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-dapc', 'azrcrv-dapc-nonce'); ?>
				<table class="form-table">
				
				<tr><td>
					<p><?php esc_html_e('Enter the content which should be displayed after the post content; if left blank the network setting will be used.', 'display-after-post-content'); ?></p>
				</td></tr>
				
				<tr><td>
					<textarea name="display_after_post_content" rows="15" cols="50" id="display_after_post_content" class="large-text code"><?php echo esc_textarea(stripslashes($options['display_after_post_content'])) ?></textarea>
					<p class="description"><?php esc_html_e('The use of shortcodes (including those from other azurecurve plugins and Contact Form 7) is supported', 'display-after-post-content'); ?></em>
					</p>
				</td></tr>
				
				</table>
				<input type="submit" value="Save Changes" class="button-primary"/>
			</form>
		</fieldset>
	</div>
	<?php
}

/**
 * Save settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_save_options(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'display-after-post-content'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-dapc', 'azrcrv-dapc-nonce')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-dapc');
		
		$allowed = azrcrv_dapc_get_allowed_tags();
	
		$option_name = 'display_after_post_content';
		if (isset($_POST[$option_name])){
			$options[$option_name] = wp_kses(stripslashes($_POST[$option_name]), $allowed);
		}
		
		// Store updated options array to database
		update_option('azrcrv-dapc', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-dapc&settings-updated', admin_url('admin.php')));
		exit;
	}
}

/**
 * Get allowed tags.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_get_allowed_tags() {
	
    $allowed_tags = wp_kses_allowed_html();
	
    $allowed_tags['table']['class'] = 1;
    $allowed_tags['table']['style'] = 1;
    $allowed_tags['tr']['class'] = 1;
    $allowed_tags['tr']['style'] = 1;
    $allowed_tags['th']['class'] = 1;
    $allowed_tags['th']['style'] = 1;
    $allowed_tags['td']['class'] = 1;
    $allowed_tags['td']['style'] = 1;
    $allowed_tags['p']['class'] = 1;
    $allowed_tags['p']['style'] = 1;
    $allowed_tags['ul']['class'] = 1;
    $allowed_tags['ul']['style'] = 1;
    $allowed_tags['ol']['class'] = 1;
    $allowed_tags['ol']['style'] = 1;
    $allowed_tags['li']['class'] = 1;
    $allowed_tags['li']['style'] = 1;
	
    return $allowed_tags;
}

/**
 * Add to Network menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_create_network_admin_menu(){
	if (function_exists('is_multisite') && is_multisite()){
		add_submenu_page(
						'settings.php'
						,esc_html__("Display After Post Content Settings", "display-after-post-content")
						,esc_html__("Display After Post Content", "display-after-post-content")
						,'manage_network_options'
						,'azrcrv-dapc'
						,'azrcrv_dapc_network_settings'
						);
	}
}

/**
 * Display network settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_network_settings(){
	if(!current_user_can('manage_network_options')) wp_die(esc_html__('You do not have permissions to perform this action', 'display-after-post-content'));
	$options = get_site_option('azrcrv-dapc');

	?>
	<div id="azrcrv-dapc-general" class="wrap">
		<fieldset>
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_dapc_save_network_options" />
				<input name="page_options" type="hidden" value="suffix" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-dapc', 'azrcrv-dapc-nonce'); ?>
				<table class="form-table">
				<tr><td>
					<p><?php esc_html_e('Enter the content which should be displayed after the post content.', 'display-after-post-content'); ?></p>
				</td></tr>
				<tr><td>
					<textarea name="display_after_post_content" rows="15" cols="50" id="display_after_post_content" class="large-text code"><?php echo esc_textarea(stripslashes($options['display_after_post_content'])) ?></textarea>
					<p class="description"><?php esc_html_e('The use of shortcodes (including those from other azurecurve plugins and Contact Form 7) is supported', 'display-after-post-content'); ?></em>
					</p>
				</td></tr>
				</table>
				<input type="submit" value="<?php esc_html_e('Submit', 'display-after-post-content'); ?>" class="button-primary"/>
			</form>
		</fieldset>
	</div>
	<?php
}

/**
 * Save network settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_save_network_options(){     
	if(!current_user_can('manage_network_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'display-after-post-content'));
	}
	
	if (! empty($_POST) && check_admin_referer('azrcrv-dapc', 'azrcrv-dapc-nonce')){
		// Retrieve original plugin options array
		$options = get_site_option('azrcrv-dapc');
		
		$allowed = azrcrv_dapc_get_allowed_tags();
	
		$option_name = 'display_after_post_content';
		if (isset($_POST[$option_name])){
			$options[$option_name] = wp_kses(stripslashes($_POST[$option_name]), $allowed);
		}
		
		update_site_option('azrcrv-dapc', $options);

		wp_redirect(network_admin_url('settings.php?page=azrcrv-dapc&settings-updated'));
		exit;  
	}
}

/**
 * Display after post content.
 *
 * @since 1.0.0
 *
 */
function azrcrv_dapc_display_after_post_content($content){
	if(!is_feed() && !is_home() && is_single()){
			$options = azrcrv_dapc_get_option('azrcrv-dapc');
			
			$display_after_post_content = '';
			if (strlen($options['display_after_post_content']) > 0){
				$display_after_post_content = stripslashes($options['display_after_post_content']);
			}else{
				$network_options = get_site_option('display_after_post_content');
				if (strlen($network_options['display_after_post_content']) > 0){
					$display_after_post_content = stripslashes($network_options['display_after_post_content']);
				}
			}
			if (strlen($display_after_post_content) > 0){
				$content .= "<div class='azc_dapc'>".$display_after_post_content."</div>";
			}
	}
	return $content;
}

?>