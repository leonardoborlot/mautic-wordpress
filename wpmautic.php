<?php
/**
 * Plugin Name: Mautic for Wordpress
 * Plugin URI: https://github.com/luizeof/mautic-wordpress
 * Description: This plugin will allow you to add Mautic (Free Open Source Marketing Automation) tracking to your site
 * Version: 2.0.0
 * Author: luizeof
 * Author URI: https://www.luizeof.com.br
 * License: GPL3
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Store plugin directory
define( 'VPMAUTIC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Store plugin main file path
define( 'VPMAUTIC_PLUGIN_FILE', __FILE__ );

add_action('admin_menu', 'mauticwordpress_settings');
add_action('wp_footer', 'mauticwordpress_function');
add_shortcode('mautic', 'mauticwordpress_shortcode');
add_shortcode('mauticform', 'mauticwordpress_form_shortcode');
add_shortcode('mauticfocus', 'mauticwordpress_focus_shortcode');

function mauticwordpress_settings()
{
	include_once(dirname(__FILE__) . '/options.php');
	add_options_page('Mautic', 'Mautic', 'manage_options', 'mauticwordpress', 'mauticwordpress_options_page');
}

/**
 * Settings Link in the ``Installed Plugins`` page
 */
function mauticwordpress_plugin_actions( $links, $file ) {
	if( $file == plugin_basename( VPMAUTIC_PLUGIN_FILE ) && function_exists( "admin_url" ) ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=mauticwordpress' ) . '">' . __('Settings') . '</a>';
		// Add the settings link before other links
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'mauticwordpress_plugin_actions', 10, 2 );

/**
 * Writes Tracking JS to the HTML source of WP head
 */
function wpmautic_function()
{
	$options = get_option('mauticwordpress_options');
	$base_url = trim($options['base_url'], " \t\n\r\0\x0B/");

	$mauticTrackingJS = <<<JS
<script>
    (function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
        w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
        m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
    })(window,document,'script','{$base_url}/mtc.js','mt');

		mt('send', 'pageview', {}, {
		    onload: function() {
		        console.log("Mautic Tracking Script loaded!");
		    },
		    onerror: function() {
		        console.log("Ops! Error on Mautic Tracking Script!");
		    }
		});
</script>
JS;

	echo $mauticTrackingJS;
}

/**
 * Handle mautic shortcode. Must include a type attribute.
 * Allowable types are:
 *  - form
 *  - content
 * example: [mautic type="form" id="1"]
 * example: [mautic type="focus" id="1"]
 * example: [mautic type="content" slot="slot_name"]Default Content[/mautic]
 * example: [mautic type="video" gate-time="15" form-id="1" src="https://www.youtube.com/watch?v=QT6169rdMdk"]
 *
 * @param      $atts
 * @param null $content
 *
 * @return string
 */
function mauticwordpress_shortcode( $atts, $content = null )
{
	$atts = shortcode_atts(array(
	    'type' => null,
        'id' => null,
        'slot' => null,
        'src' => null,
        'width' => null,
        'height' => null,
        'form-id' => null,
        'gate-time' => null,
				'values' => null
    ), $atts);

	switch ($atts['type'])
	{
		case 'form':
			return mauticwordpress_form_shortcode( $atts );
		case 'content':
			return mauticwordpress_dwc_shortcode( $atts, $content );
        case 'video':
            return mauticwordpress_video_shortcode( $atts );
							case 'tags':
								return mauticwordpress_tags_shortcode( $atts );
								case 'focus':
									return mauticwordpress_focus_shortcode( $atts );
	}

	return false;
}

/**
 * Handle mauticform shortcode
 * example: [mauticform id="1"]
 *
 * @param  array $atts
 * @return string
 */
function mauticwordpress_form_shortcode( $atts )
{
	$options = get_option('mauticwordpress_options');
	$base_url = trim($options['base_url'], " \t\n\r\0\x0B/");
	$atts = shortcode_atts(array('id' => ''), $atts);

	if (! $atts['id']) {
		return false;
	}

	return '<script type="text/javascript" src="' . $base_url . '/form/generate.js?id=' . $atts['id'] . '"></script>';
}


/**
 * Handle mautic focus shortcode
 * example: [mauticfocus id="1"]
 *
 * @param  array $atts
 * @return string
 */
function mauticwordpress_focus_shortcode( $atts )
{
	$options = get_option('mauticwordpress_options');
	$base_url = trim($options['base_url'], " \t\n\r\0\x0B/");
	$atts = shortcode_atts(array('id' => ''), $atts);

	if (! $atts['id']) {
		return false;
	}

	return '<script type="text/javascript" src="' . $base_url . '/focus/' . $atts['id'] . '.js" charset="utf-8" async="async"></script>';
}


/**
 * Handle mautictags shortcode
 * example: [mautic type="tags" values="addtag,-removetag"]
 *
 * @param  array $atts
 * @return string
 */
function mauticwordpress_tags_shortcode( $atts )
{
	$options = get_option('mauticwordpress_options');
	$base_url = trim($options['base_url'], " \t\n\r\0\x0B/");
	$atts = shortcode_atts(array('values' => ''), $atts);

	if (! $atts['values']) {
		return false;
	}

	return '<img src="' . $base_url . '/mtracking.gif?tags=' . $atts['values'] . '" alt="Mautic Tags" />';
}


function mauticwordpress_dwc_shortcode( $atts, $content = null)
{
	$options  = get_option('mauticwordpress_options');
	$base_url = trim($options['base_url'], " \t\n\r\0\x0B/");
	$atts     = shortcode_atts(array('slot' => ''), $atts, 'mautic');

	return '<div class="mautic-slot" data-slot-name="' . $atts['slot'] . '">' . $content . '</div>';
}

function mauticwordpress_video_shortcode( $atts )
{
    $video_type = '';
    $atts = shortcode_atts(array(
        'gate-time' => 15,
        'form-id' => '',
        'src' => '',
        'width' => 640,
        'height' => 360
    ), $atts);

    if (empty($atts['src']))
    {
        return 'You must provide a video source. Add a src="URL" attribute to your shortcode. Replace URL with the source url for your video.';
    }

    if (empty($atts['form-id']))
    {
        return 'You must provide a mautic form id. Add a form-id="#" attribute to your shortcode. Replace # with the id of the form you want to use.';
    }

    if (preg_match('/^.*((youtu.be)|(youtube.com))\/((v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))?\??v?=?([^#\&\?]*).*/', $atts['src']))
    {
        $video_type = 'youtube';
    }

    if (preg_match('/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/', $atts['src']))
    {
        $video_type = 'vimeo';
    }

    if (strtolower(substr($atts['src'], -3)) === 'mp4')
    {
        $video_type = 'mp4';
    }

    if (empty($video_type))
    {
        return 'Please use a supported video type. The supported types are youtube, vimeo, and MP4.';
    }

    return '<video height="' . $atts['height'] . '" width="' . $atts['width'] . '" data-form-id="' . $atts['form-id'] . '" data-gate-time="' . $atts['gate-time'] . '">' .
            '<source type="video/' . $video_type . '" src="' . $atts['src'] . '" /></video>';
}

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function mauticwordpress_wp_title( $title = '', $sep = '' ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= trim(wp_title($sep, false));

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

	return $title;
}
