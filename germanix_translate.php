<?php
/*
Plugin Name: Germanix Translate
Plugin URI:  http://toscho.de/
Description: Rüstet deutsche Übersetzungen im Backend nach.
Version:     0.1
Author:      Thomas Scholz
Author URI:  http://toscho.de
Created:     13.05.2010

Changelog

v 0.1
	* Split from the original Germanix plugin
*/

if ( is_admin() || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) )
{
	add_filter('gettext',
		array ( 'Germanix_Translate', 'gettext_filter'   ), 10, 1);
	add_filter('ngettext',
		array ( 'Germanix_Translate', 'ngettext_filter'  ), 10, 3);

	add_filter('http_request_args',
		array ( 'Germanix_Translate', 'no_upgrade_check' ),  5, 2);
}

class Germanix_Translate
{
	/**
	 * Adds missing translations in gettext.
	 *
	 * @param  string $str
	 * @return string
	 */
	static function gettext_filter($str)
	{
		return strtr($str, array (
			// Extend to fit your needs.
			'Dashboard'             => 'Übersicht',
			'Submitted on'          => 'Eingereicht am',
			'Biographische Angaben' => 'Beschreibung',
			'- Wähle -' 			=> 'Wähle',
			'Theme Files' 			=> 'Theme-Dateien',
			'Attribute' 			=> 'Eigenschaften',
			'Template' 				=> 'Vorlage',
			'neues Fenster oder neuen Tab.'
							=> 'neues Fenster oder neuer Tab.',
			'aktuelle Fenster oder aktueller Tab, ohne Frames.'
							=> 'aktuelles Fenster oder aktueller Tab, ohne Frames.',
		));
	}

	/**
	 * Adds missing translations in ngettext.
	 *
	 * @param  string $translation
	 * @param  string $single
	 * @param  string $plural
	 * @return string
	 */
	static function ngettext_filter($trans, $single, $plural)
	{
		return "Approved" == $plural ? "Genehmigte" : $trans;
	}

	/**
	 * Blocks update checks for this plugin.
	 *
	 * @author Mark Jaquith http://markjaquith.wordpress.com
	 * @link   http://wp.me/p56-65
	 * @param  array $r
	 * @param  string $url
	 * @return array
	 */
	static function no_upgrade_check($r, $url)
	{
		if ( 0 !== strpos(
				$url
			,	'http://api.wordpress.org/plugins/update-check'
			)
		)
		{ // Not a plugin update request. Bail immediately.
			return $r;
		}

		$plugins = unserialize( $r['body']['plugins'] );
		$p_base  = plugin_basename( __FILE__ );

		unset (
			$plugins->plugins[$p_base],
			$plugins->active[array_search($p_base, $plugins->active)]
		);

		$r['body']['plugins'] = serialize($plugins);

		return $r;
	}
}