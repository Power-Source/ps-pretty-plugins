<div class="wrap">
	<h2><?php _e('PS-Pretty Plugins Einstellungen', 'wmd_prettyplugins') ?></h2>
	<p><?php printf(__('Auf dieser Seite kannst Du PS-Pretty Plugins konfigurieren. Du kannst benutzerdefinierte Plugin-Daten für jedes Plugin konfigurieren auf der <a href="%s">"Plugins" Netzwerkseite</a> durch Klicken auf "Details bearbeiten". Klicke <a href="%s">hier</a> um zu sehen, wie es aktuell auf der Hauptseite aussieht.', 'wmd_prettyplugins'), admin_url('network/plugins.php'), admin_url('admin.php?page=pretty-plugins.php')) ?></p>
	<form action="settings.php?page=pretty-plugins.php" method="post" enctype="multipart/form-data">

		<?php
		settings_fields('wmd_prettyplugins_options');
		?>

		<h3><?php _e('Allgemeine Einstellungen', 'wmd_prettyplugins') ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[setup_mode]"><?php _e('Aktiviere Setup-Modus', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<select name="wmd_prettyplugins_options[setup_mode]">
						<?php $this->the_select_options(array(), $this->options['setup_mode']); ?>
					</select>
					<p class="description"<?php echo ($this->options['setup_mode'] == 1) ? ' style="color:red;"' : ''; ?>><?php _e('Bei der Einstellung "WAHR" wird die Plugin-Seite "PS-Pretty Plugins" nur auf der Hauptseite angezeigt. Dieser Modus ist nützlich, um Plugin-Details zu konfigurieren, bevor die PS-Pretty Plugins-Funktionen an allen Standorten im Netzwerk aktiviert werden.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[theme]"><?php _e('Darstellung der Plugin-Seite', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<?php
					$select_options = $this->get_themes();
					?>
					<select name="wmd_prettyplugins_options[theme]">
						<?php $this->the_select_options($select_options, $this->options['theme']); ?>
					</select>
					<p class="description"><?php _e('Wähle das Design aus, mit dem Du Deine Plugin-Seiten anzeigen möchtest. Du kannst Deine eigenen Themes hinzufügen im "wp-content/upload/prettyplugins/your-theme-/" Ordner. (Tipp: Dupliziere das Standarddesign von "wp-content/plugins/prettyplugins/themes/" um anzufangen.)', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

		</table>

		<h3><?php _e('Themen- und Darstellungseinstellungen', 'wmd_prettyplugins') ?></h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_links]"><?php _e('Wähle wohin Plugin Links zeigen', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<?php
					$select_options = array( 'plugin_url' => 'Plugin Original-URL', 'plugin_cutom_url' => 'Plugin Info URL', 'plugin_url_or_cutom_url' => 'Plugin Info URL oder ursprüngliche URL', 'disable' => 'Deaktiviert' );
					?>
					<select name="wmd_prettyplugins_options[plugins_links]">
						<?php $this->the_select_options($select_options, $this->options['plugins_links']); ?>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_auto_screenshots]"><?php _e('Ersten Screenshot automatisch laden', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<select name="wmd_prettyplugins_options[plugins_auto_screenshots]">
						<?php $this->the_select_options(array(), $this->options['plugins_auto_screenshots']); ?>
					</select>
					<p class="description"><?php _e('Wenn kein Bild für ein Plugin festgelegt wurde, wird der erste verfügbare Screenshot im Plugin-Ordner geladen (Beispiel: Screenshot-1.png).', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_auto_screenshots_by_name]"><?php _e('Screenshot mit korrektem Namen automatisch laden', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<select name="wmd_prettyplugins_options[plugins_auto_screenshots_by_name]">
						<?php $this->the_select_options(array(), $this->options['plugins_auto_screenshots_by_name']); ?>
					</select>
					<p class="description"><?php _e('Wenn das vorgestellte Bild für ein Plugin nicht festgelegt wurde und sich ein Bild in "wp-content/upload/prettyplugins/screenshots/"  befindet mit dem richtigen Namen (Beispiel: Plugin-Speicherort -  "wp-content/plugins/akismet/akismet.php", image file - "akismet-akismet.png".), wird es automatisch geladen. Bei dieser Methode funktionieren nur PNG-Dateien', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_auto_screenshots_wp]"><?php _e('WP.org-Bild automatisch laden', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<select name="wmd_prettyplugins_options[plugins_auto_screenshots_wp]">
						<?php $this->the_select_options(array(), $this->options['plugins_auto_screenshots_wp']); ?>
					</select>
					<p class="description"><?php _e('Wenn kein ausgewähltes Bild für ein Plugin festgelegt wurde, wird ein Bild aus dem Plugin-Verzeichnis von wordpress.org geladen. Es kann nicht garantiert werden, dass das Bild gefunden wird.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_links]"><?php _e('Beschreibungen minimieren', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<select name="wmd_prettyplugins_options[plugins_hide_descriptions]">
						<?php $this->the_select_options(array(), $this->options['plugins_hide_descriptions']); ?>
					</select>
					<p class="description"><?php _e('Wenn "Wahr", wird die "Beschreibung" des Plugins wird standardmäßig ausgeblendet und nach dem Klicken auf den Link "Beschreibungen" auf der Seite der Plugins angezeigt. Das Theme muss dies unterstützen', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>
		</table>

		<h3><?php _e('Beschriftungen', 'wmd_prettyplugins') ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_link_label]"><?php _e('Plugin Seitentitel', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<input type="text" class="regular-text" name="wmd_prettyplugins_options[plugins_page_title]" value="<?php echo stripslashes(esc_attr($this->options['plugins_page_title'])); ?>"/>
					<p class="description"><?php _e('Dies ist der Menüpunkt "Plugins". Nenne es "Plugins", "Apps", "Add-Ons" oder was auch immer Du möchtest.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_link_label]"><?php _e('Beschreibung der Plugin-Seite', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<input type="text" class="regular-text" style="width:95%;" name="wmd_prettyplugins_options[plugins_page_description]" value="<?php echo stripslashes(esc_attr($this->options['plugins_page_description'])); ?>"/>
					<p class="description"><?php _e('Dieser Text wird oben auf der Plugins-Seite angezeigt. Sage Deinen Benutzern, was Du zu bieten hast.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_link_label]"><?php _e('Benutzerdefiniertes Link-Label', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<input type="text" class="regular-text" name="wmd_prettyplugins_options[plugins_link_label]" value="<?php echo stripslashes(esc_attr($this->options['plugins_link_label'])); ?>"/>
					<p class="description"><?php _e('Fügt für jedes Plugin einen Link zu einer beliebigen URL hinzu. Zum Beispiel ein Link zur Unterstützung von Dokumenten für das Plugin.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="save_settings" class="button-primary" value="<?php _e('Änderungen speichern', 'wmd_prettyplugins') ?>" />
		</p>

		<h3><?php _e('Exportieren and Importieren', 'wmd_prettyplugins') ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label><?php _e('Exportieren', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<a href="<?php echo add_query_arg(array('prettyplugins_action' => 'export', '_wpnonce' => wp_create_nonce('wmd_prettyplugins_options'))); ?>" class="button"><?php _e('Exportdatei herunterladen', 'wmd_prettyplugins') ?></a>
					<p class="description">
						<?php _e('Exportiere Daten und Einstellungen für den späteren Import oder verwende sie als Konfigurationsdatei. Du kannst die exportierte Datei mit dem Namen "config.xml" in den Ordner "wp-content/upload/prettyplugins/" legen, um Daten und Einstellungen automatisch zu laden.', 'wmd_prettyplugins') ?> <small><?php _e('Beachte dass auch Daten aus der aktuellen Konfigurationsdatei (falls vorhanden) exportiert werden.', 'wmd_prettyplugins') ?></small>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_prettyplugins_options[plugins_link_label]"><?php _e('Importieren:', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<input type="file" name="config_file" id="upload" size="25">
					<input type="submit" name="import_config" class="button" value="<?php _e('Datei hochladen und importieren', 'wmd_prettyplugins'); ?>"/>
					<p class="description"><?php _e('Wähle eine Exportdatei (korrekt formatierte XML-Datei), um Daten und Einstellungen zu importieren. Diese Aktion ersetzt alle vorhandenen Daten und Einstellungen.', 'wmd_prettyplugins') ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label><?php _e('Zurücksetzen:', 'wmd_prettyplugins') ?></label>
				</th>

				<td>
					<a onclick="return confirm('<?php _e('Are you sure?', 'wmd_prettyplugins'); ?>')" href="<?php echo add_query_arg(array('prettyplugins_action' => 'delete_custom_data', '_wpnonce' => wp_create_nonce('wmd_prettyplugins_options'))); ?>" class="button"><?php _e('Lösche alle benutzerdefinierten Plugin-Daten', 'wmd_prettyplugins') ?></a>
					<p class="description">
						<?php _e('Diese Aktion löscht alle vorhandenen benutzerdefinierten Plugin-Daten dauerhaft.', 'wmd_prettyplugins') ?></br>
					</p>
				</td>
			</tr>
		</table>
	</form>

</div>