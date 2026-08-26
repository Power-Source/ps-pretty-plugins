<?php
/*
Plugin Name: PS Pretty Plugins
Plugin URI: https://psource.eimen.net/wiki/ps-pretty-plugins/
Description: Verleihe Deiner Multisite-Plugin-Seite einen modernen App-Store-Look – mit Bildern, Kategorien, Suche und einer übersichtlichen Darstellung Deiner Plugins.
Author: PSOURCE
Author URI:https://psource.eimen.net/
Network: true
Text Domain: wmd_prettyplugins
Domain Path: languages
Version: 1.0.2
*/

/*
Copyright 2018-2026 PSOURCE (https://psource.eimen.net/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.
*/


define( 'PLUGLOOK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

include_once(PLUGLOOK_PLUGIN_DIR.'includes/functions.php');

class WMD_PrettyPlugins extends WMD_PrettyPlugins_Functions {

	var $plugin_main_file;
	var $plugin_dir_url;
	var $plugin_dir;
	var $plugin_basename;
	var $plugin_rel;
	var $plugin_dir_custom;
	var $plugin_dir_url_custom;

	var $blog_id;
	var $pro_site_plugin_active;
	var $pro_site_settings;

	var $plugins_data;
	var $plugins_custom_data;
	var $plugins_custom_data_config;
	var $plugins_categories;
	var $plugins_categories_config;

	var $plugins_config_version;

	var $default_options;
	var $options;
	var $current_theme_details;

	function __construct() {

		//plugin only works on admin
		if(is_admin()) {
			$this->init_vars();

			register_activation_hook($this->plugin_main_file, array($this, 'do_activation'));

			//if in setup mode, disable everything for other sites then main.
			if( isset($this->options['setup_mode']) && ($this->options['setup_mode'] == 0 || ($this->blog_id == 1 && $this->options['setup_mode'] == 1)) ) {
				add_action('plugins_loaded', array($this,'plugins_loaded'));

				add_action('admin_enqueue_scripts', array($this,'register_scripts_styles_admin'));

				add_action('admin_init', array($this,'admin_init'));
				add_action('init', array($this,'init'));

				add_action('admin_menu', array($this,'admin_page'), 20);
				add_action('network_admin_menu', array($this,'network_admin_page'), 20);
				add_action('load-settings_page_pretty-plugins', array($this, 'network_plugins_help'));
				add_action( 'network_admin_notices', array($this,'options_page_validate_save_notices') );
				add_action( 'all_admin_notices', array($this,'plugin_page_notice'), 11 );
				add_filter('network_admin_plugin_action_links', array($this,'network_admin_plugin_action_links'), 10, 3);
				add_filter('all_plugins', array($this,'network_filter_all_plugins'));

				add_action('admin_footer-plugins.php', array($this,'prettyplugins_edit_html'));

				add_action('wp_ajax_prettyplugins_add_category_ajax', array($this,'add_category_ajax'));
				add_action('wp_ajax_prettyplugins_save_category_ajax', array($this,'save_category_ajax'));
				add_action('wp_ajax_prettyplugins_save_plugin_details_ajax', array($this,'save_plugin_details_ajax'));

				
			}
		}
	}

	function init_vars() {
		$this->blog_id = get_current_blog_id();

		$this->plugin_main_file = __FILE__;
		$this->plugin_dir = PLUGLOOK_PLUGIN_DIR;
		$this->plugin_dir_url = plugin_dir_url($this->plugin_main_file);
		$this->plugin_basename = plugin_basename($this->plugin_main_file);
		$this->plugin_rel = dirname($this->plugin_basename).'/';

		$wp_upload_dir = wp_upload_dir();
		if($this->blog_id != 1) {
                    switch_to_blog( 1 );
                    $wp_upload_dir = wp_upload_dir();
                    restore_current_blog();
                }
		$this->plugin_dir_custom = $wp_upload_dir['basedir'].'/prettyplugins/';
		$this->plugin_dir_url_custom = $wp_upload_dir['baseurl'].'/prettyplugins/';

		$this->plugins_config_version = apply_filters('prettyplugins_config_version', 0);

		$this->default_options = array(
			'setup_mode' => '1',
			'theme' => 'standard/quick-sand',
			'plugins_links' => 'plugin_cutom_url',
			'plugins_auto_screenshots' => '0',
			'plugins_auto_screenshots_by_name' => '0',
			'plugins_auto_screenshots_wp' => '0',
			'plugins_hide_descriptions' => '0',
			'plugins_page_title' => __('Plugins', 'wmd_prettyplugins'),
			'plugins_page_description' => __('Mit Plugins kannst Du zusätzliche Funktionen auf Deiner Webseite aktivieren! Hier ist eine Liste dessen, was wir für Dich zur Verfügung haben.', 'wmd_prettyplugins'),
			'plugins_link_label' => __('Mehr Information', 'wmd_prettyplugins')
		);

		//load options
		$this->options = get_site_option('wmd_prettyplugins_options', $this->default_options);
	}

	function do_activation() {
		if(!is_multisite())
			trigger_error(sprintf(__('PS-Pretty Plugins funktioniert nur in Multisite-Konfigurationen. Wie Du Multisite konfigurierst und benutzt erfährst Du <a href="%s" target="_blank">hier</a>.', 'wmd_prettyplugins'), 'http://codex.wordpress.org/Create_A_Network'),E_USER_ERROR);
		else {
			//create folder for custom themes
			if (!$this->plugins_config_version) {
				if(!is_dir($this->plugin_dir_custom))
					mkdir($this->plugin_dir_custom);
				if(!is_dir($this->plugin_dir_custom.'themes/'))
					mkdir($this->plugin_dir_custom.'themes/');
				if(!is_dir($this->plugin_dir_custom.'screenshots/'))
					mkdir($this->plugin_dir_custom.'screenshots/');
			}

			//save default options
			if(get_site_option('wmd_prettyplugins_options', 0) == 0)
				update_site_option('wmd_prettyplugins_options', $this->default_options);

		}
	}

	function plugins_loaded() {
		//delete_site_option( 'wmd_prettyplugins_options');
		load_plugin_textdomain( 'wmd_prettyplugins', false, $this->plugin_rel.'languages/' );
	}

	function init(){
		global $pagenow;

		//load stuff when on correct page
		if($this->is_prettyplugin_data_required()) {
			$this->set_custom_plugin_data();

		//Check if prosite and plugin module is active
		$this->pro_site_settings = get_site_option( 'psts_settings' );
		if(function_exists('is_pro_site') && isset($this->pro_site_settings['modules_enabled']) && in_array('ProSites_Module_Plugins', $this->pro_site_settings['modules_enabled']))
			$this->pro_site_plugin_active = true;
		else {
			$this->pro_site_plugin_active = false;
			$this->pro_site_settings = false;
		}
		}

		//controlls welcome/setup notice
		if(current_user_can('manage_network_options') && (!isset($_POST['wmd_prettyplugins_options']['setup_mode']) && $this->options['setup_mode'] == 1 || (isset($_POST['wmd_prettyplugins_options']['setup_mode']) && $_POST['wmd_prettyplugins_options']['setup_mode'] != 0)) && $this->is_prettyplugin_data_required())
			add_action( 'all_admin_notices', array( $this, 'setup_mode_welcome_notice' ), 12 );

		//check if stuff are being exported
		if(isset($_REQUEST['prettyplugins_action']) && $_REQUEST['prettyplugins_action'] == 'export')
			add_action('wp_loaded', array($this,'export_data_settings'), 1);
	}

	function set_custom_plugin_data() {
		$this->plugins_custom_data = get_site_option('wmd_prettyplugins_plugins_custom_data', array());
		$this->current_theme_details = $this->get_current_theme_details();

		//load config file if exists
		if($this->plugins_config_version) {
			//check last modified time, load file if new
			$config_file_m_time = $this->plugins_config_version;
			if($config_file_m_time != get_site_option('wmd_prettyplugins_last_config_file_m_time', 0) && file_exists($this->plugin_dir_custom.'config.xml')) {
				update_site_option('wmd_prettyplugins_last_config_file_m_time', $config_file_m_time);
				$this->import_xml_data_setting_file($this->plugin_dir_custom.'config.xml', 1);
			}
			else {
				$this->plugins_categories_config = get_site_option('wmd_prettyplugins_plugins_categories_config', array());
				$this->plugins_custom_data_config = get_site_option('wmd_prettyplugins_plugins_custom_data_config', array());
			}
		}
		else {
			$this->plugins_categories_config = array();
			$this->plugins_custom_data_config = array();
		}
		//load data
		$this->plugins_categories = get_site_option('wmd_prettyplugins_plugins_categories', array());
	}

	function admin_init(){
		global $pagenow;

		//controls activate/deactivate actions for plugins
		$prettyplugins = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : 0;
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
		if ( $action && $prettyplugins == 'pretty-plugins.php' && $pagenow == 'admin.php' && $plugin ) {
			switch ( $action ) {
				case 'activate':
					if ( ! current_user_can('activate_plugins') )
						wp_die(__('Du hast nicht genügend Berechtigungen, um Plugins für diese Seite zu aktivieren.'));

					if ( is_multisite() && ! is_network_admin() && is_network_only_plugin( $plugin ) ) {
						wp_redirect( self_admin_url("admin.php?page=pretty-plugins.php&plugin_status=$status") );
						exit;
					}

					check_admin_referer('activate-plugin_'.$plugin);

					$result = activate_plugin($plugin, self_admin_url('admin.php?page=pretty-plugins.php&activate=true&plugin=' . $plugin), is_network_admin() );
					if ( is_wp_error( $result ) ) {
						if ( 'unexpected_output' == $result->get_error_code() ) {
							$redirect = self_admin_url('admin.php?page=pretty-plugins.php&error=true&charsout=' . strlen($result->get_error_data()) . '&plugin=' . $plugin );
							wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect));
							exit;
						} else {
							wp_die($result);
						}
					}

					if ( ! is_network_admin() ) {
						$recent = (array) get_option( 'recently_activated' );
						unset( $recent[ $plugin ] );
						update_option( 'recently_activated', $recent );
					}

					exit;
					break;
				case 'deactivate':
					if ( ! current_user_can('activate_plugins') )
						wp_die(__('Du hast nicht genügend Berechtigungen, um Plugins für diese Seite zu deaktivieren.'));

					check_admin_referer('deactivate-plugin_'.$plugin);

					if ( ! is_network_admin() && is_plugin_active_for_network( $plugin ) ) {
						wp_redirect( self_admin_url("admin.php?page=pretty-plugins.php") );
						exit;
					}

					deactivate_plugins( $plugin, false, is_network_admin() );
					if ( ! is_network_admin() )
						update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated' ) );
					if ( headers_sent() )
						echo "<meta http-equiv='refresh' content='" . esc_attr( "0;url=admin.php?page=pretty-plugins.php&deactivate=true" ) . "' />";
					else
						wp_redirect( self_admin_url("admin.php?page=pretty-plugins.php&deactivate=true") );
					exit;
					break;
			}
		}

		if($this->pro_site_plugin_active) {
			global $plugin_page;
			if ( isset($plugin_page) && $plugin_page == 'pretty-premium-plugins' )
				wp_redirect( admin_url('admin.php?page=pretty-plugins.php&category=premium') );
		}
	}

	function register_scripts_styles_admin($hook) {
		global $wp_version;

		//register scripts and styles for plugin page
		if( $hook == 'toplevel_page_pretty-plugins' ) {
			wp_register_style('wmd-prettyplugins-theme', $this->current_theme_details['dir_url'].'style.css', array(), '7');
			wp_enqueue_style('wmd-prettyplugins-theme');

			wp_register_script('wmd-prettyplugins-theme', $this->current_theme_details['dir_url'].'theme.js', array('jquery', 'backbone', 'wp-backbone'), '9', true);
			wp_enqueue_script('wmd-prettyplugins-theme');

			//used on old theme only?
			$show_category = isset( $_GET['category'] ) ? sanitize_key( wp_unslash( $_GET['category'] ) ) : 'all';
			$params = array(
				'show_category' => $show_category,
				'show_status' => 'all'
			);
			wp_localize_script( 'wmd-prettyplugins-theme', 'wmd_pl_a', $params );

			wp_reset_vars( array( 'theme', 'search' ) );
			$this->enqueue_plugin_showcase_script_data();
		}
		//register scripts and styles for network plugin page
		elseif($hook == 'plugins.php' && is_network_admin() && (!isset($_GET['plugin_status']) || (isset($_GET['plugin_status']) && $_GET['plugin_status'] != 'mustuse' && $_GET['plugin_status'] != 'dropins'))) {
			wp_register_style('wmd-prettyplugins-network-admin', $this->plugin_dir_url.'css/network-admin.css', array(), 2);
			wp_enqueue_style('wmd-prettyplugins-network-admin');

			wp_register_script('wmd-prettyplugins-network-admin', $this->plugin_dir_url.'js/network-admin.js', array(), 3);
			wp_enqueue_script('wmd-prettyplugins-network-admin');

			$network_only_plugins = array();
			if(!apply_filters('wmd_prettyplugins_allow_network_plugins_details_editing', false)) {
			$plugins = apply_filters('all_plugins', get_plugins());
			foreach ($plugins as $path => $plugin)
				if($plugin['Network'])
					$network_only_plugins[] = $path;
			}

			$plugins_custom_data_ready = $this->get_converted_plugins_data_for_js($this->get_merged_plugins_custom_data());

			$plugins_categories_ready = $this->get_merged_plugins_categories();
			$protocol = is_ssl() ? 'https://' : 'http://'; //This is used to set correct adress if secure protocol is used so ajax calls are working
			$params = array(
				'js_wp_version' => version_compare($wp_version, '4.6', '>=') ? 2 : 1,
				'ajax_url' => admin_url( 'admin-ajax.php', $protocol ),
				'admin_url' => admin_url( '', $protocol ),
				'prettyplugins_url' => $this->plugin_dir_url,
				'theme_url' => $this->current_theme_details['dir_url'],
				'image' => __('Bild', 'wmd_prettyplugins'),
				'edit_code' => __('Code bearbeiten', 'wmd_prettyplugins'),
				'orginal_description' => __('Ursprüngliche Beschreibung ein-/ausblenden', 'wmd_prettyplugins'),
				'edit_details' => __('Details bearbeiten', 'wmd_prettyplugins'),
				'edit_details_a_title' => __('Bearbeite Plugin-Details wie Titel, Beschreibung, Bild und Kategorien', 'wmd_prettyplugins'),
				'visit_help' => __('Besuche die Hilfeseite des Plugins', 'wmd_prettyplugins'),
				'categories' => __('Kategorien', 'wmd_prettyplugins'),
				'choose_screenshot' => __('Wähle ein Bild für den Plugin-Screenshot (empfohlene Größe: 600px auf 450px)', 'wmd_prettyplugins'),
				'select_image' => __('Bild auswählen', 'wmd_prettyplugins'),
				'edit' => __('Bearbeiten', 'wmd_prettyplugins'),
				'plugin_details' => $plugins_custom_data_ready,
				'plugin_categories' => $plugins_categories_ready,
				'network_only_plugins' => $network_only_plugins,
				'filter_by' => __('Nach Kategorie filtern', 'wmd_prettyplugins'),
				'current_category' => isset( $_GET['category'] ) ? sanitize_key( wp_unslash( $_GET['category'] ) ) : ''
			);
			wp_localize_script( 'wmd-prettyplugins-network-admin', 'wmd_pl_na', $params );

			wp_enqueue_media();
		}

		//mp6 icon load
		if ($wp_version >= 3.8) {
			wp_register_style( 'wmd-prettyplugins-mp6', $this->plugin_dir_url . 'css/mp6.css');
			wp_enqueue_style('wmd-prettyplugins-mp6');
		}
	}

	function enqueue_plugin_showcase_script_data($script_name = 'wmd-prettyplugins-theme', $root = 'admin', $force_all = false, $plugins = false, $fe = false) {
		if(!current_user_can( 'activate_plugins' ) && !$force_all)
			return false;
			
		$plugins_categories = $this->get_merged_plugins_categories();

		$plugins_orginal = $this->get_merged_plugins_all_data($plugins);

		$count = 0;
		$this->plugins_data = array(); //this is being set globally because it later can be reused by themes that need variable
		foreach($plugins_orginal as $plugin_path => $plugin) {
			//lets see if it is valid plugin
			if(!apply_filters('wmd_prettyplugins_force_plugin_on_list', false, $plugin, $plugin_path, $plugins)) {
				if($plugins) {
					if(!in_array($plugin_path, $plugins))
						continue;
				}
				elseif(
				(isset($plugin['Network']) && $plugin['Network']) || 
				$plugin_path == 'pretty-plugins/pretty-plugins.php' || 
				!isset($plugin['Name']) || 
					is_plugin_active_for_network($plugin_path)
			)
				continue;
			}

			$plugin_prepare = $plugin;

			$plugin_prepare['Actions'] = array();

			$count++;
			$plugin_prepare['id'] = $count;
			$plugin_prepare['ListID'] = $count;

			$plugin_prepare['isActive'] = is_plugin_active( $plugin_path );

			//set correct screenshot
			$plugin_prepare['ScreenShot'] = $this->get_screenshot_url(isset($plugin_prepare['ScreenShot']) ? $plugin_prepare['ScreenShot'] : '', $plugin_path);

			$plugin_prepare['isAvailable'] = ($this->pro_site_plugin_active) ? $this->prosite_plugin_available($plugin_path) : true;

			if(!$plugin_prepare['isAvailable']) {
				$plugin_prepare['ActionLinkText'] = sprintf( __('%s Only', 'wmd_prettyplugins'), $this->prosite_plugin_required_level_name($plugin_path));
				$checkout_url = isset($this->pro_site_settings['checkout_url']) ? $this->pro_site_settings['checkout_url'] : '';
				if(apply_filters('psts_force_ssl', false))
					$checkout_url = str_replace('http://', 'https://', $checkout_url);
				$checkout_url_orginal = add_query_arg(array('bid' => $this->blog_id), $checkout_url);
				$checkout_url = add_query_arg(apply_filters('prettyplugins_checkout_args', array('bid' => $this->blog_id), $plugin_prepare), $checkout_url);
				$plugin_prepare['ActionLink'] = $checkout_url;
				$plugin_prepare['ActionLinkClass'] = 'upgrade';
			}
			elseif($plugin_prepare['isActive']) {
				$plugin_prepare['ActionLinkText'] = __('Deaktivieren', 'wmd_prettyplugins');
				$plugin_prepare['ActionLink'] = wp_nonce_url('admin.php?page=pretty-plugins.php&amp;action=deactivate&amp;plugin='.$plugin_path, 'deactivate-plugin_'.$plugin_path);
				$plugin_prepare['ActionLinkClass'] = 'deactivate';
			}
			else {
				$plugin_prepare['ActionLinkText'] = __('Aktivieren', 'wmd_prettyplugins');
				$plugin_prepare['ActionLink'] = wp_nonce_url('admin.php?page=pretty-plugins.php&amp;action=activate&amp;plugin='.$plugin_path, 'activate-plugin_'.$plugin_path);
				$plugin_prepare['ActionLinkClass'] = 'activate';
			}

			$plugin_prepare['ActionLinkText'] = apply_filters('prettyplugins_action_link_text', $plugin_prepare['ActionLinkText'], $plugin_prepare['isAvailable'], $plugin_prepare['isActive']);

			//set up ribbon
			if($plugin_prepare['isActive'])
				$plugin_prepare['Ribbon'] = '<div class="ribbon"><img src="'.$this->current_theme_details['dir_url'].'images/ribbon_active.png'.'" alt="'.__('Aktiv', 'wmd_prettyplugins').'"></div>';
			elseif($this->pro_site_plugin_active)
				if($plugin_prepare['isAvailable'])
					$plugin_prepare['Ribbon'] = '<div class="ribbon"><img src="'.$this->current_theme_details['dir_url'].'images/ribbon_available.png'.'" alt="'.__('Frei', 'wmd_prettyplugins').'"></div>';
				else
					$plugin_prepare['Ribbon'] = '<div class="ribbon"><img src="'.$this->current_theme_details['dir_url'].'images/ribbon_upgrade.png'.'" alt="'.__('Nur Pro', 'wmd_prettyplugins').'"></div>';
			else
				$plugin_prepare['Ribbon'] = '';

			$plugin_prepare['ShowMore'] = false;
			if(isset($plugin_prepare['Description'])) {
				$plugin_prepare['Description'] = stripslashes($plugin_prepare['Description']);
				if(strlen($plugin_prepare['Description']) > 120)
					$plugin_prepare['ShowMore'] = true;
			}

			if(isset($plugin_prepare['Categories']) && count($plugin_prepare['Categories']) > 0) {
				foreach ($plugin_prepare['Categories'] as $plugin_category_key)
				if ( isset( $plugins_categories[ $plugin_category_key ] ) ) {
					$plugin_prepare['CategoriesNames'][] = $plugins_categories[ $plugin_category_key ];
				}
			}

			if($plugin_prepare['ActionLinkClass'] == 'upgrade') {
				$plugin_prepare['Categories'][] = 'premium';
			}

			//Set up Plugin Link according to options settings
			if($this->options['plugins_links'] == 'plugin_url' && isset($plugin_prepare['PluginURI']) && !empty($plugin_prepare['PluginURI']))
				$plugin_prepare['PluginLink'] = $plugin_prepare['PluginURI'];
			elseif($this->options['plugins_links'] == 'plugin_cutom_url' && isset($plugin_prepare['CustomLink']) && !empty($plugin_prepare['CustomLink']))
				$plugin_prepare['PluginLink'] = $plugin_prepare['CustomLink'];
			elseif($this->options['plugins_links'] == 'plugin_url_or_cutom_url')
				if(isset($plugin_prepare['CustomLink']) && !empty($plugin_prepare['CustomLink']))
					$plugin_prepare['PluginLink'] = $plugin_prepare['CustomLink'];
				elseif(isset($plugin_prepare['PluginURI']) && !empty($plugin_prepare['PluginURI']))
					$plugin_prepare['PluginLink'] = $plugin_prepare['PluginURI'];

			//remove link added by prosites because we have better one:)
			global $ProSites_Module_Plugins;
			remove_filter( 'plugin_action_links', array($ProSites_Module_Plugins, 'action_links'), 10);
			
			//setup action links by plugins for plugins
			$plugin_prepare['Actions'] = apply_filters('plugin_action_links', array(), $plugin_path, $plugin_prepare, '');
			$plugin_prepare['Actions'] = apply_filters("plugin_action_links_$plugin_path", $plugin_prepare['Actions'], $plugin_path, $plugin_prepare, '');

			$plugin_prepare['ActionsValues'] = array_values($plugin_prepare['Actions']);

			$this->plugins_data[$plugin_path] = apply_filters('wmd_prettyplugins_plugins_data', $plugin_prepare);
		}

		uasort(
			$this->plugins_data,
			static function ( $a, $b ) {
				return strnatcasecmp(
					$a['Name'] ?? '',
					$b['Name'] ?? ''
				);
			}
		);

		$search = isset($_GET['search']) ? $_GET['search'] : (isset($_GET['plugin-search']) ? $_GET['plugin-search'] : '');
		$category = isset($_GET['category']) ? $_GET['category'] : (isset($_GET['plugin-category']) ? $_GET['plugin-category'] : '');
		$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
		
		if ($fe) {
			$categories = array_merge(array('all' => __( 'Alle', 'wmd_prettyplugins' )), $plugins_categories);
		} else {
			$categories = array_merge(array('all' => __( 'Alle', 'wmd_prettyplugins' ), 'active' => __( 'Aktiv', 'wmd_prettyplugins' ), 'inactive' => __( 'Inaktiv', 'wmd_prettyplugins' )), $plugins_categories);
		}

		wp_localize_script($script_name, '_wpPluginsSettings', array(
			'plugins'   => array_values($this->plugins_data),
			'categories'   => $categories,

			'settings' => array(
				'root'          => $root == 'admin' ? parse_url( admin_url( 'admin.php' ), PHP_URL_PATH ).'?page=pretty-plugins.php' : $root,
				'plugin'         => esc_html( $plugin ),
				'search'        => esc_html( $search ),
				'category'        => esc_html( $category ),
			),
			'l10n' => array(
				'search'  => __( 'Suche Installierte Plugins', 'wmd_prettyplugins' ),
				'searchFE'  => __( 'Suche nach verfügbaren Plugins:', 'wmd_prettyplugins' ),
				'searchPlaceholder' => __( 'Suche installierte Plugins ...', 'wmd_prettyplugins' ),
				'searchFEPlaceholder' => __( 'Suche nach verfügbaren Plugins ...', 'wmd_prettyplugins' ),
				'categories' => __( 'Kategorien:', 'wmd_prettyplugins' ),
			),
		));
	}

	//Replaces plugins page with custom
	function admin_page() {
		remove_menu_page('plugins.php');
		add_menu_page(stripslashes($this->options['plugins_page_title']), stripslashes($this->options['plugins_page_title']), 'activate_plugins', basename($this->plugin_main_file), array($this,'new_plugin_page'), $this->plugin_dir_url.'/images/icon.png', 65);

		if($this->pro_site_plugin_active) {
			remove_submenu_page('psts-checkout', 'premium-plugins');
			add_submenu_page('psts-checkout', $this->pro_site_settings['pp_name'], $this->pro_site_settings['pp_name'], 'activate_plugins', 'pretty-premium-plugins', array($this,'new_plugin_page') );
		}

		add_submenu_page( 'pretty-plugins.php', __('Plugins', 'wmd_prettyplugins'), __('Alle', 'wmd_prettyplugins'), 'activate_plugins', basename($this->plugin_main_file), array($this,'new_plugin_page') );

		$plugins_categories_ready = apply_filters('wmd_prettyplugins_admin_submenu_categories', $this->get_merged_plugins_categories());
		foreach ($plugins_categories_ready as $plugins_category_key => $plugins_category)
			add_submenu_page( 'pretty-plugins.php', $plugins_category, $plugins_category, 'activate_plugins', basename($this->plugin_main_file).'&category='.$plugins_category_key, array($this,'new_plugin_page') );
	}

	function network_plugins_help() {
		$screen = get_current_screen();
		
		if ($screen->id != 'settings_page_pretty-plugins-network') {
			return;
		}
	
		$screen->add_help_tab(array(
			'id'      => 'edit_details',
			'title'   => __('Plugin-Details bearbeiten', 'wmd_prettyplugins'),
			'content' => '
				<p>' . sprintf(__('Du kannst die Plugin-Details für jedes Plugin bearbeiten, indem Du auf "Details bearbeiten" klickst. Alle neuen Details werden sichtbar auf <a href="%s">der Plugins-Seite</a> und verfügbar für alle Netzwerkseiten. Es ist auch möglich, zusätzliche Einstellungen zu tätigen auf <a href="%s">dieser Seite</a>.', 'wmd_prettyplugins'), admin_url('admin.php?page=pretty-plugins.php'), admin_url('network/settings.php?page=pretty-plugins.php')) . '</p>
				<p>' . __('<strong>Name</strong> - Ersetze den Namen des Plugins durch einen Deiner Wahl. Lasse das Feld leer, um den ursprünglichen Namen zu verwenden.', 'wmd_prettyplugins') . '</p>
				<p>' . __('<strong>Eigene URL</strong> - Erstelle einen externen Plugin-Link zu einer beliebigen URL Deiner Wahl, z.B. zur Support-Dokumentation.', 'wmd_prettyplugins') . '</p>
				<p>' . __('<strong>Bild URL</strong> - Stelle das angezeigte Bild für dieses Plugin ein. Du kannst ein Bild aus Deiner Mediengalerie auswählen oder die URL der Datei kopieren, die sich unter "wp-content/uploads/prettyplugins/screenshots/" befindet. Alternativ wird eine Datei mit dem richtigen Namen automatisch geladen, auch wenn dieses Feld leer ist (Beispiel: Speicherort des Plugins -  "wp-content/plugins/akismet/akismet.php", image file - "akismet-akismet.png". Nur PNG Dateien funktionieren mit dieser Methode.). Die Einstellung "Screenshot mit korrektem Namen automatisch laden" muss auf "WAHR" gesetzt sein, damit sie funktioniert. Empfohlene Abmessungen sind 600px auf 450px.', 'wmd_prettyplugins') . '</p>
				<p>' . __('<strong>Kategorien</strong> - Hier kannst Du Kategorien festlegen, denen das Plugin zugewiesen wird. Nicht verwendete Kategorien werden automatisch gelöscht.', 'wmd_prettyplugins') . '</p>
				<p>' . __('<strong>Beschreibung</strong> - Ersetze die ursprüngliche Beschreibung des Plugins durch Deine eigene. Lasse das Feld leer, um das Original zu verwenden.', 'wmd_prettyplugins') . '</p>
			',
		));
	
		// load tooltips for admin plugins page
		if (!class_exists('PSource_HelpTooltipsDyn'))
			include($this->plugin_dir . 'external/psource-help-tooltips.php');
		$tips = new PSource_HelpTooltipsDyn();
		$tips->set_icon_url($this->plugin_dir_url . 'images/tooltip.png');
		$tips->set_use_notice(false);
	
		$tips->bind_tip(__('Ersetze den Namen des Plugins durch einen Deiner Wahl. Lasse das Feld leer, um den ursprünglichen Namen zu verwenden.', 'wmd_prettyplugins'), '#name_tooltip');
		$tips->bind_tip(__('Erstelle einen externen Plugin-Link zu einer beliebigen URL Deiner Wahl, z.B. zur Support-Dokumentation.', 'wmd_prettyplugins'), '#custom_url_tooltip');
		$tips->bind_tip(__('Stelle das angezeigte Bild für dieses Plugin ein. Empfohlene Abmessungen sind 600px zu 450px. Verwende die Registerkarte "Hilfe" (obere rechte Ecke), um Informationen zur erweiterten Verwendung zu erhalten.', 'wmd_prettyplugins'), '#image_url_tooltip');
		$tips->bind_tip(__('Hier kannst Du Kategorien festlegen, denen das Plugin zugewiesen wird. Nicht verwendete Kategorien werden automatisch gelöscht.', 'wmd_prettyplugins'), '#categories_tooltip');
		$tips->bind_tip(__('Ersetze die ursprüngliche Beschreibung des Plugins durch Deine eigene. Lasse das Feld leer, um das Original zu verwenden.', 'wmd_prettyplugins'), '#description_tooltip');
	}

	function setup_mode_welcome_notice() {
		echo '<div class="updated fade"><p>'.sprintf(__('PS-Pretty Plugins befindet sich derzeit im "Setup-Modus". Diese Erinnerung verschwindet, nachdem das Plugin konfiguriert und der Setup-Modus auf "AUS" gesetzt wurde. <a href="%s">hier</a>. Du kannst die Details für jedes Plugin in Deinem Netzwerk <a href="%s">hier</a>  ändern, und kontrolliere, wie die Seite "Plugins" für alle Netzwerkseiten aussieht <a href="%s">hier</a>.', 'wmd_prettyplugins'), admin_url('network/settings.php?page=pretty-plugins.php'), admin_url('network/plugins.php'), admin_url('admin.php?page=pretty-plugins.php'), add_query_arg('prettyplugins_action', 'remove_notice')).'</a></p></div>';
	}

	function plugin_page_notice() {
		global $pagenow;

		if ( $pagenow == 'admin.php' && isset($_REQUEST['page']) && $_REQUEST['page'] == 'pretty-plugins.php' && is_super_admin() && !is_network_admin() )
			echo '<div class="updated"><p>'.sprintf(__('Als Super Admin kannst Du beliebige Plugins für diese Seite aktivieren. Bitte beachte, dass auf die Standard-Plugin-Seite weiterhin unter <a href="%s">dieser URL</a> zugegriffen werden kann.', 'wmd_prettyplugins'), admin_url('plugins.php')).'</p></div>';
	}

	function network_admin_page() {
		add_submenu_page('settings.php', __( 'PS-Pretty Plugins', 'wmd_prettyplugins' ), __( 'PS-Pretty Plugins', 'wmd_prettyplugins' ), 'manage_network_options', basename($this->plugin_main_file), array($this,'network_option_page'));
	}

	function network_option_page() {
		include($this->plugin_dir.'includes/page-network-admin.php');
	}

	function network_admin_plugin_action_links($actions, $plugin_file, $plugin_data) {
		//adds edit details link
		if(((isset($plugin_data['Network']) && !$plugin_data['Network']) || !isset($plugin_data['Network']) || apply_filters('wmd_prettyplugins_allow_network_plugins_details_editing', false)) && (!isset($_GET['plugin_status']) || (isset($_GET['plugin_status']) && $_GET['plugin_status'] != 'mustuse' && $_GET['plugin_status'] != 'dropins')))
			array_splice($actions, 1, 0, '<a href="#'.$plugin_file.'" title="'.__('Bearbeite Plugin-Details wie Titel, Beschreibung, Bild und Kategorien', 'wmd_prettyplugins').'" class="edit_details">'.__('Details bearbeiten', 'wmd_prettyplugins').'</a>');

		//changes edit link to edit code for clarity
		if(isset($actions['edit']))
			$actions['edit'] = str_replace(__('Edit'), __( 'Code bearbeiten', 'wmd_prettyplugins' ), $actions['edit']);

		if($plugin_file == 'pretty-plugins/pretty-plugins.php')
			$actions['settings'] = '<a href="'.admin_url('network/settings.php?page=pretty-plugins.php').'" title="'.__('Gehe zur Einstellungsseite für PS-Pretty Plugins', 'wmd_prettyplugins').'">'.__('Einstellungen', 'wmd_prettyplugins').'</a>';

		return $actions;
	}

	function network_filter_all_plugins($plugins) {
		if(is_network_admin() && $plugins && isset($_REQUEST['category']) && $_REQUEST['category']) {

			$category = $_REQUEST['category'];
			
			$plugins_custom_data = $this->get_merged_plugins_custom_data();

			$category_plugins = array();
			foreach($plugins_custom_data as $plugin_custom_data_slug => $plugin_custom_data_data) {
				if(!isset($plugin_custom_data_data['Categories']) || !in_array($category, $plugin_custom_data_data['Categories'])) {
					unset($plugins_custom_data[$plugin_custom_data_slug]);
				}
			}

			$plugins = array_intersect_key($plugins, $plugins_custom_data);
		}

		return $plugins;
	}

	function options_page_validate_save_notices() {
		//default save
		if(isset($_POST['option_page']) && $_POST['option_page'] == 'wmd_prettyplugins_options' && isset($_POST['save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'wmd_prettyplugins_options-options')) {
			$validated = $this->get_validated_options($_POST['wmd_prettyplugins_options']);

			update_site_option( 'wmd_prettyplugins_options', $validated );

			echo '<div id="message" class="updated"><p>'.__( 'Erfolgreich gespeichert', 'wmd_prettyplugins' ).'</p></div>';
		}
		elseif(isset($_REQUEST['prettyplugins_action'], $_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'wmd_prettyplugins_options')) {
			//delete custom data
			if($_REQUEST['prettyplugins_action'] == 'delete_custom_data') {
				echo '<div id="message" class="updated"><p>'.__( 'Alle benutzerdefinierten Plugin-Daten wurden erfolgreich gelöscht.', 'wmd_prettyplugins' ).'</p></div>';
				delete_site_option('wmd_prettyplugins_plugins_custom_data');
				delete_site_option('wmd_prettyplugins_plugins_custom_data_config');
				delete_site_option('wmd_prettyplugins_plugins_categories');
				delete_site_option('wmd_prettyplugins_plugins_categories_config');
				delete_site_option('wmd_prettyplugins_last_config_file_m_time');
			}
		}
		//try to import config file
		elseif(isset($_POST['option_page'], $_POST['import_config'], $_POST['_wpnonce']) && $_POST['option_page'] == 'wmd_prettyplugins_options' && wp_verify_nonce($_POST['_wpnonce'], 'wmd_prettyplugins_options-options')) {
			if (!isset($_FILES['config_file']) && $_FILES['config_file']['error'] > 0) {
				echo '<div id="message" class="error"><p>'.__( 'Beim Hochladen der Datei ist ein Problem aufgetreten.', 'wmd_prettyplugins' ).'</p></div>';
			}
			else {
				$this->import_xml_data_setting_file($_FILES['config_file']["tmp_name"]);
				echo '<div id="message" class="updated"><p>'.__( 'Plugin Daten und Einstellungen erfolgreich importiert.', 'wmd_prettyplugins' ).'</p></div>';
			}
		}
	}

	function export_data_settings() {
		$this->export_xml_data_setting_file();
	}

	function prettyplugins_edit_html() {
		include($this->plugin_dir.'includes/element-edit-plugin-details.php');
	}

	function add_category_ajax() {
		$error = 0;
		$new_key = '';

		$this->plugins_categories = get_site_option(
			'wmd_prettyplugins_plugins_categories',
			array()
		);

		if (
			! isset( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ),
				'wmd_prettyplugins_edit_plugin_details'
			)
		) {
			$error = 1;
		} else {
			$category_name = isset( $_POST['plugin_new_category'] )
				? sanitize_text_field( wp_unslash( $_POST['plugin_new_category'] ) )
				: '';

			if ( '' !== $category_name ) {
				$last_category = $this->get_last_category_id();
				$last_category++;
				$new_key = 'category' . $last_category;

				$this->plugins_categories[ $new_key ] = $category_name;

				update_site_option(
					'wmd_prettyplugins_plugins_categories',
					$this->plugins_categories
				);
			} else {
				$error = 1;
			}
		}

		echo wp_json_encode(
			array(
				'id'    => $new_key,
				'name'  => $category_name ?? '',
				'error' => $error,
			)
		);

		wp_die();
	}

	function save_category_ajax() {
		$error = 0;

		$this->plugins_categories = get_site_option(
			'wmd_prettyplugins_plugins_categories',
			array()
		);

		if (
			! isset( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ),
				'wmd_prettyplugins_edit_plugin_details'
			)
		) {
			$error = 1;
		} else {
			$category_key = isset( $_POST['plugin_edit_category_key'] )
				? sanitize_key( wp_unslash( $_POST['plugin_edit_category_key'] ) )
				: '';

			$category_name = isset( $_POST['plugin_edit_category'] )
				? sanitize_text_field( wp_unslash( $_POST['plugin_edit_category'] ) )
				: '';

			if (
				'' !== $category_key &&
				isset( $this->plugins_categories[ $category_key ] ) &&
				'' !== $category_name
			) {
				$this->plugins_categories[ $category_key ] = $category_name;

				update_site_option(
					'wmd_prettyplugins_plugins_categories',
					$this->plugins_categories
				);
			} else {
				$error = 1;
			}
		}

		echo wp_json_encode(
			array(
				'id'    => $category_key ?? '',
				'name'  => $category_name ?? '',
				'error' => $error,
			)
		);

		wp_die();
	}

	function save_plugin_details_ajax() {
		// error_reporting(0); // Entfernt - unterdrückt Fehler
		$error = 0;

		// Loads variables for AJAX call.
		$this->plugins_categories = get_site_option(
			'wmd_prettyplugins_plugins_categories',
			array()
		);

		$this->plugins_custom_data = get_site_option(
			'wmd_prettyplugins_plugins_custom_data',
			array()
		);

		$this->plugins_custom_data_config = get_site_option(
			'wmd_prettyplugins_plugins_custom_data_config',
			array()
		);

		if (
			isset( $_POST['_wpnonce'] ) &&
			wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ),
				'wmd_prettyplugins_edit_plugin_details'
			)
		) {
			$plugin_image_id = isset( $_POST['plugin_image_id'] )
				? absint( $_POST['plugin_image_id'] )
				: 0;

			$plugin_image_url = isset( $_POST['plugin_image_url'] )
				? esc_url_raw( wp_unslash( $_POST['plugin_image_url'] ) )
				: '';

			if ( $plugin_image_id > 0 ) {
				$resized_image_url = $this->get_resized_attachment_url( $plugin_image_id );

				if ( $resized_image_url ) {
					$plugin_image_url = $resized_image_url;
				}
			}

			$plugin_categories = isset( $_POST['plugin_categories'] ) && is_array( $_POST['plugin_categories'] )
				? array_map( 'sanitize_key', wp_unslash( $_POST['plugin_categories'] ) )
				: array();

			$plugin_categories = array_filter(
				$plugin_categories,
				static function ( $category ) {
					return strpos( $category, 'config' ) === false;
				}
			);

			$plugin_path = isset( $_POST['plugin_path'] )
				? sanitize_text_field( wp_unslash( $_POST['plugin_path'] ) )
				: '';

			if ( '' === $plugin_path ) {
				$error = 1;
			} elseif ( ! isset( $this->plugins_custom_data[ $plugin_path ] ) ) {
				$this->plugins_custom_data[ $plugin_path ] = array();
			}

			$plugin_custom_url = isset( $_POST['plugin_custom_url'] )
				? esc_url_raw( wp_unslash( $_POST['plugin_custom_url'] ) )
				: '';

			$plugin_description = isset( $_POST['plugin_description'] )
				? sanitize_textarea_field( wp_unslash( $_POST['plugin_description'] ) )
				: '';

			$plugin_name = isset( $_POST['plugin_name'] )
				? sanitize_text_field( wp_unslash( $_POST['plugin_name'] ) )
				: '';

			$data = array(
				'Categories'   => $plugin_categories,
				'ScreenShot'   => $plugin_image_url,
				'ScreenShotID' => $plugin_image_id,
				'CustomLink'   => $plugin_custom_url,
				'Description'  => $plugin_description,
				'Name'         => $plugin_name,
			);
			foreach ( $data as $name => $value ) {
				if ( ! empty( $value ) ) {
					$this->plugins_custom_data[ $plugin_path ][ $name ] = $value;
				} else {
					unset( $this->plugins_custom_data[ $plugin_path ][ $name ] );
				}
			}

			// Empty categories fix.
			if ( isset( $this->plugins_custom_data[ $plugin_path ]['Categories'] ) && count( $this->plugins_custom_data[ $plugin_path ]['Categories'] ) < 1 ) {
				unset( $this->plugins_custom_data[ $plugin_path ]['Categories'] );
			}

			// Add http to custom link when a URL scheme is missing.
			if ( isset( $this->plugins_custom_data[ $plugin_path ]['CustomLink'] ) ) {
				$custom_link = $this->plugins_custom_data[ $plugin_path ]['CustomLink'];

				if ( strpos( $custom_link, '://' ) === false && count( explode( '/', $custom_link ) ) > 1 ) {
					$this->plugins_custom_data[ $plugin_path ]['CustomLink'] = 'http://' . $custom_link;
				}
			}

			// Remove unused categories if necessary.
			$removed_categories = array();
			$all_used_categories = array();

			foreach ( $this->plugins_custom_data as $data ) {
				if ( isset( $data['Categories'] ) && is_array( $data['Categories'] ) ) {
					$all_used_categories = array_merge(
						$all_used_categories,
						$data['Categories']
					);
				}
			}

			foreach ( $this->plugins_categories as $key => $category_name ) {
				if ( ! in_array( $key, $all_used_categories, true ) ) {
					unset( $this->plugins_categories[ $key ] );
					$removed_categories[] = $key;
				}
			}

			if ( ! empty( $removed_categories ) ) {
				update_site_option(
					'wmd_prettyplugins_plugins_categories',
					$this->plugins_categories
				);
			}

			$plugins_custom_data_ready = $this->get_converted_plugins_data_for_js($this->get_merged_plugins_custom_data());

			if ( empty( $plugins_custom_data_ready[ $plugin_path ] ) ) {
				$error = 1;
			}

			if ( empty( $this->plugins_custom_data[ $plugin_path ] ) ) {
				unset( $this->plugins_custom_data[ $plugin_path ] );
			}

			ksort($this->plugins_custom_data);
			update_site_option('wmd_prettyplugins_plugins_custom_data', $this->plugins_custom_data);
		}
		else
			$error = 1;

		echo wp_json_encode(
			array(
				'new_details'      => $plugins_custom_data_ready[ $plugin_path ] ?? array(),
				'remove_categories' => $removed_categories,
				'error'             => $error,
			)
		);

		wp_die();
	}

	function new_plugin_page() {
		if ( ! current_user_can('activate_plugins') )
			wp_die( __( 'Du hast nicht genügend Berechtigungen, um Plugins für diese Seite zu verwalten.' ) );

		$plugins = $this->plugins_data;
		$plugins_categories = $this->get_merged_plugins_categories();

		?>
		<?php if ( isset($_GET['error']) ) :

			if ( isset( $_GET['main'] ) )
				$errmsg = __( 'Du kannst ein Plugin nicht löschen, während es auf der Hauptseite aktiv ist.' );
			elseif ( isset($_GET['charsout']) )
				$errmsg = sprintf(__('Das Plugin hat während der Aktivierung %d Zeichen einer <strong>unerwarteten Ausgabe</strong> generiert. Wenn Du feststellst, dass &#8220;Header bereits Nachrichten gesendet&#8221 haben, Probleme mit Syndication-Feeds oder andere Probleme auftreten, deaktiviere oder entferne dieses Plugin.'), $_GET['charsout']);
			else
				$errmsg = __('Das Plugin konnte nicht aktiviert werden, da es einen <strong>schwerwiegenden Fehler</strong> auslöste.');
			?>
			<div id="message" class="updated"><p><?php echo $errmsg; ?></p>
			<?php
				if ( !isset( $_GET['main'] ) && !isset($_GET['charsout']) && wp_verify_nonce($_GET['_error_nonce'], 'plugin-activation-error_' . $plugin) ) { ?>
			<iframe style="border:0" width="100%" height="70px" src="<?php echo 'plugins.php?action=error_scrape&amp;plugin=' . esc_attr($plugin) . '&amp;_wpnonce=' . esc_attr($_GET['_error_nonce']); ?>"></iframe>
			<?php
				}
			?>
			</div>
		<?php elseif ( isset($_GET['activate']) ) : ?>
			<div id="message" class="updated"><p><?php _e('Plugin <strong>aktiviert</strong>.') ?></p></div>
		<?php elseif ( isset($_GET['deactivate']) ) : ?>
			<div id="message" class="updated"><p><?php _e('Plugin <strong>deaktiviert</strong>.') ?></p></div>
		<?php endif;

		include($this->current_theme_details['dir'].'index.php');
	}
}

global $wmd_prettyplugins;
$wmd_prettyplugins = new WMD_PrettyPlugins;


include_once(PLUGLOOK_PLUGIN_DIR.'includes/frontend-showcase.php');

