<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

function mauticwordpress_options_page()
{ ?>
	<div>
		<h2>WP Mautic</h2>
		<p><?php _e("Enable Base URL for Mautic Integration."); ?></p>
		<form action="options.php" method="post">
			<?php settings_fields('mauticwordpress_options'); ?>
			<?php do_settings_sections('mauticwordpress'); ?>
			<p><?php _e("Something like https://mautic.yourdomain.com"); ?></p>
			<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
		<h3>Shortcode Examples:</h3>
		<ul>
			<li>Mautic Form Embed: <code>[mautic type="form" id="1"]</code></li>
			<li>Mautic Dynamic Content: <code>[mautic type="content" slot="slot_name"]Default Text[/mautic]</code></li>
			<li>Mautic Gated Video: <code>[mautic type="video" gate-time="15" form-id="1" src="https://www.youtube.com/watch?v=QT6169rdMdk"]</code></li>
			<li>Add or Remove Lead Tags <code>[mautic type="tags" values="addtag,-removetag"]</code></li>
			<li>Mautic Focus:  <code>[mautic type="focus" id="1"]</code></li>
		</ul>
		<h3>Quick Links</h3>
		<ul>
			<li>
				<a href="https://github.com/mautic/mautic-wordpress#mautic-wordpress-plugin" target="_blank">Plugin docs</a>
			</li>
			<li>
				<a href="https://github.com/mautic/mautic-wordpress/issues" target="_blank">Plugin support</a>
			</li>
			<li>
				<a href="https://mautic.org" target="_blank">Mautic project</a>
			</li>
			<li>
				<a href="http://docs.mautic.org/" target="_blank">Mautic docs</a>
			</li>
			<li>
				<a href="https://www.mautic.org/community/" target="_blank">Mautic forum</a>
			</li>
		</ul>
	</div>
	<?php
}

add_action('admin_init', 'mauticwordpress_admin_init');

function mauticwordpress_admin_init()
{
	register_setting( 'mauticwordpress_options', 'mauticwordpress_options', 'mauticwordpress_options_validate' );
	add_settings_section('mauticwordpress_main', 'Main Settings', 'mauticwordpress_section_text', 'mauticwordpress');
	add_settings_field('mauticwordpress_base_url', 'Mautic URL', 'mauticwordpress_base_url', 'mauticwordpress', 'mauticwordpress_main');
}

function mauticwordpress_section_text()
{
}

function mauticwordpress_base_url()
{
	$options = get_option('mauticwordpress_options');
	echo "<input id='mauticwordpress_base_url' name='mauticwordpress_options[base_url]' size='240' style='width:100%;' type='text' placeholder='http://...' value='{$options['base_url']}' />";
}

function mauticwordpress_options_validate($input)
{
	$options = get_option('mauticwordpress_options');
	$options['base_url'] = trim($input['base_url']);

	return $options;
}
