<?php

/**
 * Plugin Name: Bye IE by Simplee Code
 * Plugin URI: http://simpleecode.com/bye-ie/
 * Author: Simplee Code
 * Author URI: http://www.simpleecode.com/
 * Description: Plugin allows you to disable access to your website in chosen Internet Explorer versions. It shows nice pop up encouraging to upgrade or change current browser to get better performance while surfing the Internet. Template is easily configurable if someone doesn't like the default one :)
 * License: GPL2
 * Version: 1.0
**/

/*  Copyright 2012 Simplee Code (email : info@simpleecode.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/** PLUGIN ACTIVATION HOOk **/

function simpleecode_bye_ie_init() {
	
	global $ie_options;
	
	$ie_options = array(
		
		'ie6' => array(
			'name' => 'IE 6'
		),
		'ie7' => array(
			'name' => 'IE 7'
		),
		'ie8' => array(
			'name' => 'IE 8'
		),
		'ie9' => array(
			'name' => 'IE 9'
		),
		'ie10' => array(
			'name' => 'IE 10'
		)

	);
	
}
add_action( 'init', 'simpleecode_bye_ie_init' );

/** PLUGIN ACTIVATION HOOK **/

function simpleecode_bye_ie_install() {

	$init = array ( 'ie6','ie7','ie8','ie9','ie10' );
	add_option('bye-ie',$init);

}

register_activation_hook(__FILE__,'simpleecode_bye_ie_install');

/** PLUGIN UNINSTALLATION HOOK **/

function simpleecode_bye_ie_uninstall() {
	
	/** REMOVE OPTION FROM PREFIX_OPTIONS TABLE **/
	delete_option( 'bye-ie' );
	
}
register_uninstall_hook(__FILE__,'simpleecode_bye_ie_uninstall');

/** ADD STYLESHEET AND JAVASCRIPT TO THE HEADER **/

if( ! function_exists('simpleecode_bye_ie_load') ) {

	function simpleecode_bye_ie_load() {
	
		// stylesheet
		wp_register_style('simpleecode_bye_ie_css',plugins_url('simpleecode-bye-ie.css',__FILE__),null,'1.0.0');
		wp_enqueue_style('simpleecode_bye_ie_css');
	
	}

}

/** GET TEMPLATE OF THE POP UP BOX **/

if( ! function_exists('simpleecode_bye_ie_template') ) {

	function simpleecode_bye_ie_template() {
		
		include trailingslashit( dirname( __FILE__ ) ) . "simpleecode-bye-ie-template.php";
	 
	}

}

/** CREATE TAB IN THE ADMIN PANEL **/
if( ! function_exists('simpleecode_bye_ie_admin') ) {

	function simpleecode_bye_ie_admin()
	{
		add_submenu_page( 'options-general.php', 'Bye IE', 'Bye IE', 'publish_posts', 'bye-ie', 'simpleecode_bye_ie_admin_backend' );
	}

	add_action('admin_menu', 'simpleecode_bye_ie_admin');
	
}

/** CUSTOMIZE BACK END PLUGIN OPTION PAGE **/
if( ! function_exists('simpleecode_bye_ie_admin_backend') ) {

	function simpleecode_bye_ie_admin_backend() {
		
		global $ie_options;
		
		/** FORM VALIDATION **/
		
		if(isset($_POST['submitted'])) {
			
			if ( wp_verify_nonce( $_POST['bye-ie-nonce'], plugin_basename( __FILE__ ) ) ) { 
			
				/** UPDATING DATABASE **/
				
				foreach( $ie_options as $key => $option ) {

					$save[$key] = $_POST['ie'][$key];

				}
				update_option( 'bye-ie', $save );
				
			} else {
			
				die("Security check failed.");
				
			}
		
		}
		
		$db = get_option( 'bye-ie' );
		
		$output .= '<div class="wrap">';
	
		/** SETTINGS FORM **/
		$output .= '<div id="icon-options-general" class="icon32"></div>';
		$output .= '<h2>Bye Internet Explorer</h2>';
		$output .= '<div style="width: 100%; height: auto; clear: both; display: block;"></div>';
		$output .= '<h3>Versions of IE to be blocked:</h3>';
		$output .= '<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">';
		
		$output .= wp_nonce_field( plugin_basename( __FILE__ ), 'bye-ie-nonce', null, true );
		
		$output .= '<ul>';
		
		foreach( $ie_options as $key => $value ) {
			
			$checked = ( $db[$key] == 'on' ) ? ' checked="checked"' : '';
			$output .= '<li><label><input class="ie_ver" type="checkbox" name="ie[' . $key . ']" '.$checked.'/>&nbsp'.$value['name'].'</label></li>';
		
		}
	
		$output .= '<li><input class="button-secondary" type="submit" value="Save Changes"/></li>';
		$output .= '</ul>';
		$output .= '<input type="hidden" name="submitted" value="true"/>';
		$output .= '</form>';
		
		/** CUSTOMIZE TIPS **/
		$output .= '<h3>Customize template</h3>';
		$output .= '<p>To customize pop up template please go to:</p>';
		$output .= '<p style="font-style: italic">HTML: /wp-content/plugins/simpleecode-bye-ie/simpleecode-bye-ie-template.php</p>';
		$output .= '<p style="font-style: italic">CSS: /wp-content/plugins/simpleecode-bye-ie.css</p>';
		
		/** NOTE **/
		$output .= '<h3>Note</h3>';
		$output .= '<p>I am looking forward to add some templates to this plugin so everyone can match pop up look to a website design. Stay with me :)</p>';
		
		$output .= '</div>';

		echo $output;
	
	}

}

/** TRIGGER THE PLUGIN IF BROWSER IS IE (depends on back end settings) **/

if( ! function_exists('simpleecode_bye_ie_trigger') ) {

	function simpleecode_bye_ie_trigger() {
	
		global $wpdb;

		$msie = ieversion();
		
		$options = get_option( 'bye-ie' );
		
		$active = array();
		
		foreach($options as $key => $opt) {
			
			if( $opt == 'on' && $key == 'ie'.$msie ) {
				
				add_action( 'wp_enqueue_scripts', 'simpleecode_bye_ie_load' );
			
				add_action( 'wp_footer', 'simpleecode_bye_ie_template' );
				
				break;
				
			}
		
		}
	}
	
	add_action('plugins_loaded','simpleecode_bye_ie_trigger');

}

/** CHECKS IE VERSION **/

function ieversion() {

	$match=preg_match('/MSIE ([0-9].[0-9])/',$_SERVER['HTTP_USER_AGENT'],$reg);
	
	if($match==0) {
	
		return -1;
		
	} else {
	
		return floatval($reg[1]);
		
	}
}


?>