<?php
class WMD_PrettyPlugins_Functions {


	//Helpers


	//check if page that requires data is displayed
	function is_prettyplugin_data_required() {
		global $pagenow;

		if(
			(
				$pagenow == 'settings.php' &&
				isset($_REQUEST['page']) &&
				$_REQUEST['page'] == 'pretty-plugins.php'
			)
			||
			(
				$pagenow == 'admin.php' &&
				isset($_REQUEST['page']) &&
				$_REQUEST['page'] == 'pretty-plugins.php'
			)
			||
			(
				$pagenow == 'plugins.php' &&
				is_network_admin()
			)
		)
			return true;
		else
			return false;
	}
    //get theme array for select option
    function get_themes() {
    	$themes_dirs = $themes = array();
    	$themes_dirs_paths = array(
    			'standard' => $this->plugin_dir.'themes/',
    			'custom' => $this->plugin_dir_custom.'themes/'
    		);
    	if(is_dir($themes_dirs_paths['standard']))
			$themes_dirs['standard'] = scandir($themes_dirs_paths['standard']);

		foreach ($themes_dirs as $type => $themes_dir)
			foreach ($themes_dir as $theme_dir) {
				$theme_dir = str_replace('.', '', $theme_dir);
				if(!empty($theme_dir))
				    if (is_dir($themes_dirs_paths[$type].'/'.$theme_dir))
				        if(file_exists($themes_dirs_paths[$type].'/'.$theme_dir.'/index.php')) {
				        	$theme_dir_name = ucwords(str_replace('-', ' ', $theme_dir));
				        	$type_name = ($type == 'custom') ? __( ' (Benutzerdefiniert)', 'wmd_prettyplugins' ) : '';
				        	$themes[$type.'/'.$theme_dir] = $theme_dir_name.$type_name;
				    	}
			}

		return $themes;
    }

    function get_current_theme_details() {
    	$theme = array('url' => '', 'dir' => '');
    	$theme_details = explode('/', $this->options['theme']);
    	if($theme_details[0] == 'standard') {
    		$theme['dir_url'] = $this->plugin_dir_url.'themes/'.$theme_details[1].'/';
    		$theme['dir'] = $this->plugin_dir.'themes/'.$theme_details[1].'/';
    		$theme['type'] = 'standard';
    	}
    	elseif($theme_details[0] == 'custom' && !empty($this->plugin_dir_url_custom)) {
    		$theme['dir_url'] = $this->plugin_dir_url_custom.'themes/'.$theme_details[1].'/';
    		$theme['dir'] = $this->plugin_dir_custom.'themes/'.$theme_details[1].'/';
    		$theme['type'] = 'custom';
    	}

    	return $theme;
    }

    function get_screenshot_url($screenshot_value, $plugin_path, $get_default = 1) {
    	$plugin_path_slug = str_replace('.php', '', str_replace('/', '-', $plugin_path));

		if(!empty($screenshot_value) && count(explode('/', $screenshot_value)) == 1 && file_exists($this->plugin_dir_custom.'screenshots/'.$screenshot_value))
			$screenshot_value = $this->plugin_dir_url_custom.'screenshots/'.$screenshot_value;
		elseif(empty($screenshot_value) && $this->options['plugins_auto_screenshots_by_name'] && file_exists($this->plugin_dir_custom.'screenshots/'.$plugin_path_slug.'.png'))
			$screenshot_value = $this->plugin_dir_url_custom.'screenshots/'.$plugin_path_slug.'.png';
		
		// Suche nach screenshot-1.png, logo.png oder logo.jpg im Plugin-Root-Verzeichnis - UNABHÄNGIG von $get_default
		if(empty($screenshot_value) && isset($this->options['plugins_auto_screenshots']) && $this->options['plugins_auto_screenshots']) {
			$plugin_dir = plugin_dir_path(WP_PLUGIN_DIR.'/'.$plugin_path);
			$plugin_url_base = WP_CONTENT_URL . '/plugins/' . dirname($plugin_path);
			
			// Priorität: screenshot-1.png > Screenshot-1.png > logo.png > Logo.png > logo.jpg > Logo.jpg
			if(file_exists($plugin_dir.'screenshot-1.png')) {
				$screenshot_value = $plugin_url_base . '/screenshot-1.png';
			}
			elseif(file_exists($plugin_dir.'Screenshot-1.png')) {
				$screenshot_value = $plugin_url_base . '/Screenshot-1.png';
			}
			elseif(file_exists($plugin_dir.'logo.png')) {
				$screenshot_value = $plugin_url_base . '/logo.png';
			}
			elseif(file_exists($plugin_dir.'Logo.png')) {
				$screenshot_value = $plugin_url_base . '/Logo.png';
			}
			elseif(file_exists($plugin_dir.'logo.jpg')) {
				$screenshot_value = $plugin_url_base . '/logo.jpg';
			}
			elseif(file_exists($plugin_dir.'Logo.jpg')) {
				$screenshot_value = $plugin_url_base . '/Logo.jpg';
			}
		}
		
		// Fallback nur wenn $get_default = 1 UND immer noch kein Bild gefunden
		if($get_default && empty($screenshot_value)) {
			if($this->options['plugins_auto_screenshots_wp']) {
				$plugin_path_parts = explode("/", $plugin_path);
				$screenshot_value = '//ps.w.org/'.$plugin_path_parts[0].'/assets/icon-128x128.png';
			}
			else {
				global $wp_version;
				if($wp_version < 3.8 && $this->current_theme_details['type'] == 'standard' )
					$screenshot_value = $this->current_theme_details['dir_url'].'images/default_screenshot_classic.png';
				else
					$screenshot_value = $this->current_theme_details['dir_url'].'images/default_screenshot.png';
			}
		}

    	return (is_ssl()) ? str_replace('http://', 'https://', $screenshot_value) : $screenshot_value;
    }

	function get_resized_attachment_url( $attachment_id, $width = 600, $height = 600, $crop = true, $suffix = '-plugin-screenshot' ) {
		$attachment_url = wp_get_attachment_url( $attachment_id );

		if ( ! $attachment_url ) {
			return false;
		}

		$attachment_meta = wp_get_attachment_metadata( $attachment_id );

		if (
			! is_array( $attachment_meta ) ||
			empty( $attachment_meta['width'] ) ||
			empty( $attachment_meta['height'] )
		) {
			return $attachment_url;
		}

		if ( $attachment_meta['width'] <= $width && $attachment_meta['height'] <= $height ) {
			return $attachment_url;
		}

		$old_image_details = array(
			'path' => get_attached_file( $attachment_id ),
			'url'  => $attachment_url,
		);

		$new_image_details = array();

		foreach ( $old_image_details as $type => $address ) {
			$path_parts = pathinfo( $address );

			if ( empty( $path_parts['filename'] ) || empty( $path_parts['extension'] ) ) {
				return false;
			}

			$new_filename = $path_parts['filename'] . $suffix . '.' . $path_parts['extension'];
			$new_image_details[ $type ] = $path_parts['dirname'] . '/' . $new_filename;
		}

		if ( ! file_exists( $new_image_details['path'] ) ) {
			$image = wp_get_image_editor( $old_image_details['path'] );

			if ( is_wp_error( $image ) ) {
				return false;
			}

			$resize_result = $image->resize( $width, $height, $crop );

			if ( is_wp_error( $resize_result ) ) {
				return false;
			}

			$save_result = $image->save( $new_image_details['path'] );

			if ( is_wp_error( $save_result ) ) {
				return false;
			}
		}

		return file_exists( $new_image_details['path'] )
			? $new_image_details['url']
			: false;
	}

	function get_merged_plugins_categories() {
		if(!isset($this->plugins_categories_config) || !is_array($this->plugins_categories_config))
			$this->plugins_categories_config = array();
		if(!isset($this->plugins_categories) || !is_array($this->plugins_categories))
			$this->plugins_categories = array();

		$categories = array_merge($this->plugins_categories_config, $this->plugins_categories);
		asort($categories);

		return $categories;
	}

	function get_merged_plugins_custom_data() {
		if(!isset($this->plugins_custom_data_config) || !is_array($this->plugins_custom_data_config))
			$this->plugins_custom_data_config = array();
		if(!isset($this->plugins_custom_data) || !is_array($this->plugins_custom_data))
			$this->plugins_custom_data = array();

		$plugins = array_replace_recursive($this->plugins_custom_data_config, $this->plugins_custom_data);

		//properly merge config categories
		foreach ($plugins as $path => $values) {
			$categories = (isset($this->plugins_custom_data[$path]['Categories']) && is_array($this->plugins_custom_data[$path]['Categories'])) ? $this->plugins_custom_data[$path]['Categories'] : array();
			$config_categories = (isset($this->plugins_custom_data_config[$path]['Categories'])) ? $this->plugins_custom_data_config[$path]['Categories'] : array();
			if(count($categories) || count($config_categories))
			$plugins[$path]['Categories'] = array_merge($categories, $config_categories);
		}

		ksort($plugins);

		return $plugins;
	}

	function get_merged_plugins_all_data($plugins = false) {
		if(!function_exists('get_plugins'))
			require_once ABSPATH.'wp-admin/includes/plugin.php';
		$plugins_default_data = $plugins ? get_plugins() : apply_filters('all_plugins', get_plugins());
		$plugins_custom_data = $this->get_merged_plugins_custom_data();

		//remove details for plugins that do not exists
		foreach($plugins_custom_data as $plugin_path => $plugin)
			if(!array_key_exists($plugin_path, $plugins_default_data))
				unset($plugins_custom_data[$plugin_path]);

		$plugins_all_data = array_replace_recursive($plugins_default_data, $plugins_custom_data);

		return $plugins_all_data;
	}

	function get_last_category_id() {
		if ( empty( $this->plugins_categories ) || ! is_array( $this->plugins_categories ) ) {
			return 0;
		}

		$keys = array_keys( $this->plugins_categories );
		$last_category = end( $keys );

		if ( ! is_string( $last_category ) || strlen( $last_category ) <= 8 ) {
			return 0;
		}

		return (int) substr( $last_category, 8 );
	}

	function get_validated_options( $input ) {
		if ( ! is_array( $input ) ) {
			return $this->options;
		}

		if ( isset( $input['plugins_links'] ) && in_array(
			$input['plugins_links'],
			array( 'plugin_url', 'plugin_cutom_url', 'plugin_url_or_cutom_url', 'disable' ),
			true
		) ) {
			$this->options['plugins_links'] = $input['plugins_links'];
		} else {
			$this->options['plugins_links'] = 'plugin_cutom_url';
		}

		$possible_themes = $this->get_themes();

		if ( isset( $input['theme'] ) && array_key_exists( $input['theme'], $possible_themes ) ) {
			$this->options['theme'] = $input['theme'];
		} else {
			$this->options['theme'] = 'standard/quick-sand';
		}

		$standard_options = array(
			'plugins_link_label'            => 'strip_tags',
			'plugins_page_title'            => 'strip_tags',
			'plugins_page_description'      => '',
			'plugins_auto_screenshots'      => '',
			'plugins_auto_screenshots_wp'   => '',
			'setup_mode'                    => '',
			'plugins_hide_descriptions'     => '',
			'plugins_auto_screenshots_by_name' => '',
		);

		foreach ( $standard_options as $option => $action ) {
			if ( isset( $input[ $option ] ) ) {
				$value = $input[ $option ];

				if ( 'strip_tags' === $action ) {
					$value = strip_tags( $value );
				}

				$this->options[ $option ] = $value;
			} elseif ( ! isset( $this->options[ $option ] ) ) {
				$this->options[ $option ] = $this->default_options[ $option ];
			}
		}

		return $this->options;
	}

	function get_converted_plugins_data_for_js($plugins_custom_data_source = array()) {
		$plugins_custom_data_ready = array();
		foreach ($plugins_custom_data_source as $path => $details) {
			$possible_data = array('Name', 'Description', 'Categories', 'CustomLink', 'ScreenShot', 'ScreenShotID');
			foreach ($possible_data as $possible_data_name)
				$details[$possible_data_name] = (isset($details[$possible_data_name]) && !empty($details[$possible_data_name])) ? $details[$possible_data_name] : null;

			$details['ScreenShotPreview'] = $this->get_screenshot_url($details['ScreenShot'], $path, 0);

			$plugins_custom_data_ready[$path] = array(
					'path' => $path,
					'name' => $details['Name'],
					'description' => stripslashes($details['Description']),
					'categories' => $details['Categories'],
					'custom_url' => $details['CustomLink'],
					'image_url' => $details['ScreenShot'],
					'image_url_preview' => $details['ScreenShotPreview'],
					'image_id' => $details['ScreenShotID']
				);
		}
		//set up screenshot preview for plugins without any image url
		foreach (apply_filters('all_plugins', get_plugins()) as $path => $value) {
			if(!isset($plugins_custom_data_ready[$path])) {
				$screenshot = $this->get_screenshot_url('', $path, 0);
				if(!empty($screenshot))
					$plugins_custom_data_ready[$path]['image_url_preview'] = $screenshot;
			}
		}

		return $plugins_custom_data_ready;
	}

	//Converts array to xml
	function get_array_as_xml( $array, $node_name = 'item' ) {
		$xml = "\n";

		if ( ! is_array( $array ) && ! is_object( $array ) ) {
			return "\n" . htmlspecialchars(
				(string) $array,
				ENT_QUOTES | ENT_XML1,
				'UTF-8'
			) . "\n";
		}

		foreach ( $array as $key => $value ) {
			$key = is_numeric( $key ) ? $node_name : (string) $key;

			$xml .= '<' . $key . '>';
			$xml .= get_array_as_xml( $value, $node_name );
			$xml .= '</' . $key . '>' . "\n";
		}

		return $xml;
	}

	//used to sort plugins by name
	function compare_by_name($a, $b) {
		return strtolower($a['Name']) > strtolower($b['Name']) ? 1 : -1;
	}

	function the_select_options($array, $current) {
		if(empty($array))
			$array = array( 1 => 'Wahr', 0 => 'Falsch' );

		foreach( $array as $name => $label ) {
			$selected = selected( $current, $name, false );
			echo '<option value="'.$name.'" '.$selected.'>'.$label.'</option>';
		}
	}


	//Actions


	function import_xml_data_setting_file($file_path, $config = 0) {
	    if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return;
		}

		$xml = simplexml_load_file( $file_path );

		if ( false === $xml ) {
			return;
		}

		$xml_import_data = json_decode( wp_json_encode( $xml ), true );

		if ( ! is_array( $xml_import_data ) ) {
			return;
		}

		$plugins_categories_replace = array();
		$plugins_categories_to_import = array();

		if ( isset( $xml_import_data['Categories'] ) && is_array( $xml_import_data['Categories'] ) ) {

			//replace names for config categories
			if($config) {
				//rename categories so they have "config" at the beginning
				foreach ($xml_import_data['Categories'] as $key => $value) {
					$new_key = str_replace('category', 'configcategory', $key);

					$plugins_categories_replace[$key] = $new_key;
					$plugins_categories_to_import[$new_key] = $value;
				}
			}
			//looks for different keyes with same value and creates new key for them
			elseif(!empty($this->plugins_categories)) {
				$plugins_categories_replace = array();
				$last_category = 0;
				foreach ($xml_import_data['Categories'] as $key => $value) {
					$category_key = array_search($value, $this->plugins_categories);
					if(isset($category_key) && $category_key)
						$plugins_categories_replace[$key] = $category_key;
					elseif(isset($this->plugins_categories[$key]) && $this->plugins_categories[$key] != $value) {
						if ( ! $last_category ) {
							$category_keys = array_keys( $this->plugins_categories );
							$last_category_key = end( $category_keys );

							if ( is_string( $last_category_key ) && strlen( $last_category_key ) > 8 ) {
								$last_category = (int) substr( $last_category_key, 8 );
							} else {
								$last_category = 0;
							}
						}

						$last_category ++;
						$new_last_category = 'category'.$last_category;
						$plugins_categories_replace[$key] = $new_last_category;
						$plugins_categories_to_import[$new_last_category] = $value;
					}
					else
						$plugins_categories_to_import[$key] = $value;
				}
			}
			else
				$plugins_categories_to_import = $xml_import_data['Categories'];

			if($config) {
				$this->plugins_categories_config = $plugins_categories_to_import;
				update_site_option('wmd_prettyplugins_plugins_categories_config', $this->plugins_categories_config);
			}
			else {
				$this->plugins_categories = array_replace_recursive($this->plugins_categories, $plugins_categories_to_import);
				update_site_option('wmd_prettyplugins_plugins_categories', $this->plugins_categories);
			}
		}

		if ( isset( $xml_import_data['Plugins']['Plugin'] ) && is_array( $xml_import_data['Plugins']['Plugin'] )) {
			//fix for single plugin in xml
			$plugins_to_import = $xml_import_data['Plugins']['Plugin'];

			if ( isset( $plugins_to_import['Path'] ) ) {
				$plugins_to_import = array( $plugins_to_import );
			}

			$plugin_custom_data_to_import = array();
			foreach($plugins_to_import as $key => $value) {
				if ( isset( $value['Path'] ) && is_string( $value['Path'] ) && '' !== trim( $value['Path'] ) ) {
					$path = trim( $value['Path'] );

					unset( $value['Path'], $value['ScreenShotID'] );

					//fix for single plugin category in xml
					if ( isset( $value['Categories']['item'] ) && ! is_array( $value['Categories']['item'] ) ) {
						$value['Categories']['item'] = array( $value['Categories']['item'] );
					}

					//Merges old categories with new one
					if(isset($value['Categories']['item']) && isset($plugins_categories_replace) && $plugins_categories_replace) {
						//configure plugin categories
						$new_categories = array();
						foreach ($value['Categories']['item'] as $id => $category) {
							if(array_key_exists($category, $plugins_categories_replace))
								$new_categories[] = $plugins_categories_replace[$category];
							else
								$new_categories[] = $category;
						}

						if ( ! $config && isset( $this->plugins_custom_data[ $path ]['Categories'] ) ) {
							$value['Categories'] = array_values(
								array_unique(
									array_merge(
										(array) $this->plugins_custom_data[ $path ]['Categories'],
										$new_categories
									)
								)
							);
						} else {
							$value['Categories'] = $new_categories;
						}
					}
					elseif(isset($value['Categories']['item']))
						$value['Categories'] = $value['Categories']['item'];
					else
						unset($value['Categories']);

					if(!empty($value))
						$plugin_custom_data_to_import[$path] = $value;
				}
			}
			if(!empty($plugin_custom_data_to_import)) {
				if($config) {
					$this->plugins_custom_data_config = $plugin_custom_data_to_import;
					update_site_option('wmd_prettyplugins_plugins_custom_data_config', $this->plugins_custom_data_config);
				}
				else {
					$this->plugins_custom_data = array_replace_recursive($this->plugins_custom_data, $plugin_custom_data_to_import);
					ksort($this->plugins_custom_data);
					update_site_option('wmd_prettyplugins_plugins_custom_data', $this->plugins_custom_data);
				}
			}
		}

		if(isset($xml_import_data['Options'])) {
			$validated = $this->get_validated_options($xml_import_data['Options']);

			update_site_option( 'wmd_prettyplugins_options', $validated );
		}
	}

	function export_xml_data_setting_file() {
		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'wmd_prettyplugins_options' ) ) {
			//rename categories to remove "config" part and merges
			$plugins_categories_xml = array();
			$plugins_categories_config_ready = array();
			$plugins_categories_replace = array();
			$last_category = 0;
			foreach ($this->plugins_categories_config as $key => $value) {
				if(!empty($this->plugins_categories))
					$category_key = array_search($value, $this->plugins_categories);
				if(isset($category_key) && $category_key)
					$plugins_categories_replace[$key] = $category_key;
				else {
					if(!$last_category)
						$last_category = $this->get_last_category_id();
					$last_category ++;
					$new_last_category = 'category'.$last_category;
					$plugins_categories_replace[$key] = $new_last_category;
					$plugins_categories_config_ready[$new_last_category] = $value;
				}
			}
			$plugins_categories_xml = array_merge($plugins_categories_config_ready, $this->plugins_categories);

			$plugins_custom_data_xml = array();
			foreach ($this->get_merged_plugins_custom_data() as $path => $value) {
				//replace plugins categories keys to match new ones(without config in name)
				if(isset($value['Categories']) && $plugins_categories_replace) {
					$new_categories = array();
					foreach ($value['Categories'] as $id => $category) {
						if(array_key_exists($category, $plugins_categories_replace))
							$new_categories[] = $plugins_categories_replace[$category];
						else
							$new_categories[] = $category;
					}
					if ( isset( $this->plugins_custom_data[ $path ]['Categories'] ) ) {
						$value['Categories'] = array_values(
							array_unique(
								array_merge(
									(array) $this->plugins_custom_data[ $path ]['Categories'],
									$new_categories
								)
							)
						);
					}
					else
						$value['Categories'] = $new_categories;
				}

				//move path from key to array
				$plugins_custom_data_xml[] = array_merge(array('Path' => $path), $value);
			}

			$filename = 'config.xml';

			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );

			$xml = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";

			$xml .= '<Plugins-data-settings>'."\n";
				$xml .= '<Plugins>';
					$xml .= $this->get_array_as_xml($plugins_custom_data_xml, 'Plugin');
				$xml .= '</Plugins>'."\n";

				$xml .= '<Categories>';
					$xml .= $this->get_array_as_xml($plugins_categories_xml);
				$xml .= '</Categories>'."\n";

				$xml .= '<Options>';
					$xml .= $this->get_array_as_xml($this->options);
				$xml .= '</Options>'."\n";
			$xml .= '</Plugins-data-settings>';

			echo $xml;

			die();
		}
	}


	//Plugins integration


	function prosite_plugin_available($plugin_file) {
		if (!isset($this->pro_site_settings['pp_plugins'])) {
			return true;
		}
		
		$psts_plugins = $this->pro_site_settings['pp_plugins'];

		if(isset($psts_plugins[$plugin_file]['level']) && $psts_plugins[$plugin_file]['level'] != 0 && is_numeric($psts_plugins[$plugin_file]['level']) && !is_super_admin())
			if((function_exists('is_pro_site') && is_pro_site($this->blog_id, $psts_plugins[$plugin_file]['level'])) ||
				(function_exists('psts_show_ads') && !psts_show_ads($this->blog_id)))
				return true;
			else
				return false;
		else
			return true;
	}

	function prosite_plugin_required_level_name($plugin_file) {
		global $psts;
		
		if (!isset($this->pro_site_settings['pp_plugins'])) {
			return true;
		}
		
		$psts_plugins = $this->pro_site_settings['pp_plugins'];

		if(isset($psts_plugins[$plugin_file]['level']) && $psts_plugins[$plugin_file]['level'] != 0 && is_numeric($psts_plugins[$plugin_file]['level'])) {
			return $psts->get_level_setting($psts_plugins[$plugin_file]['level'], 'name');
		}
		else
			return true;
	}
}