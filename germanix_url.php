<?php
/*
Plugin Name: Germanix URL
Plugin URI:  http://toscho.de/2010/wordpress-plugin-germanix/
Description: Konvertiert URL-Slugs und Dateinamen nach US-ASCII.
Version:     0.8
Author:      Thomas Scholz
Author URI:  http://toscho.de
Created:     13.05.2010

Changelog

v 0.1
	* Initial release
v 0.2
	* Added sanitize_filename_filter() for uploaded files.
v 0.3
	* Split into two plugins
v 0.4
	* Fixed bug in title filter with dots.
v 0.5
	* Trim leading and trailing minus (-).
v 0.6
	* Fixed an issue with 'Invalid Taxonomy' in the backend.
	  Props: Sergey Biryukov · http://sergeybiryukov.ru/
v 0.7
	* Added support for Turkish

v 0.8
	* Fix for http://core.trac.wordpress.org/ticket/16642
*/

if ( is_admin() || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) )
{
	remove_filter( 'sanitize_title', 'sanitize_title_with_dashes', 11 );

	add_filter('sanitize_title',
			array ( 'Germanizer', 'sanitize_title_filter' ), 10, 2);

	// »häßliches-bild.jpg => haessliches-bild.jpg
	add_filter('sanitize_file_name',
		array ( 'Germanizer', 'sanitize_filename_filter' ), 10, 1);

	add_filter('http_request_args',
		array ( 'Germanizer', 'no_upgrade_check' ),       5, 2);
}

class Germanizer
{

	/**
	 * Fixes names of uploaded files.
	 *
	 * @param  string $filename
	 * @return string
	 */
	static function sanitize_filename_filter($filename)
	{
		$filename = self::translit($filename);
		$filename = self::remove_doubles($filename);
		$filename = self::lower_ascii($filename);
		// Readd the native sanitizer to catch underscores.
		return $filename;
	}

	/**
	 * Fixes URI slugs.
	 *
	 * @param  string $title
	 * @param  string $raw_title
	 * @return string
	 */
	static function sanitize_title_filter($title, $raw_title = NULL)
	{
		if ( ! is_null( $raw_title ) )
		{
			$title = $raw_title;
		}
		$title = self::sanitize_filename_filter( $title );
		#return $title;
		return str_replace('.', '-', $title);
	}

	static function remove_doubles($str)
	{
		return preg_replace('~([-=+.])\\1+~', "\\1", $str);
	}

	static function lower_ascii($str)
	{
		$str = strtolower($str);
		// Leave underscores, otherwise the taxonomy tag cloud in the backend won’t work anymore.
		return preg_replace('~([^a-z\d_.-])~', '', $str);
	}

	/**
	 * Replaces non ASCII chars.
	 *
	 * http://github.com/wordpress/wordpress/blob/master/wp-includes/formatting.php#L531
	 * is unfortunately completely inappropriate.
	 * Modified version of Heiko Rabe’s code.
	 *
	 * @author Heiko Rabe http://code-styling.de
	 * @link   http://www.code-styling.de/?p=574
	 * @param  string $str
	 * @return string
	 */
	static function translit($str)
	{
		$utf8 = array (
				'Ä' => 'Ae'	,	'ä' => 'ae'	,	'Æ' => 'Ae'	,	'æ' => 'ae'
			,	'À' => 'A'	,	'à' => 'a'	,	'Á' => 'A'	,	'á' => 'a'
			,	'Â' => 'A'	,	'â' => 'a'	,	'Ã' => 'A'	,	'ã' => 'a'
			,	'Å' => 'A'	,	'å' => 'a'	,	'ª' => 'a'	,	'ₐ' => 'a'
			,	'ā' => 'a'
			,	'Ć' => 'C'	,	'ć' => 'c'	,	'Ç' => 'C'	,	'ç' => 'c'
			,	'Ð' => 'D'	,	'đ' => 'd'
			,	'È' => 'E'	,	'è' => 'e'	,	'É' => 'E'	,	'é' => 'e'
			,	'Ê' => 'E'	,	'ê' => 'e'	,	'Ë' => 'E'	,	'ë' => 'e'
			,	'ₑ' => 'e'
			,	'ƒ' => 'f'
			,	'ğ' => 'g'	,	'Ğ' => 'G'
			,	'Ì' => 'I'	,	'ì' => 'i'	,	'Í' => 'I'	,	'í' => 'i'
			,	'Î' => 'I'	,	'î' => 'i'	,	'Ï' => 'Ii'	,	'ï' => 'ii'
			,	'ī' => 'i'
			,	'ı' => 'i'	,	'I' => 'I' // turkish, correct?
			,	'Ñ' => 'N'	,	'ñ' => 'n'	,	'ⁿ' => 'n'
			,	'Ò' => 'O'	,	'ò' => 'o'	,	'Ó' => 'O'	,	'ó' => 'o'
			,	'Ô' => 'O'	,	'ô' => 'o'	,	'Õ' => 'O'	,	'õ' => 'o'
			,	'Ø' => 'O'	,	'ø' => 'o'	,	'ₒ' => 'o'	,	'Ö' => 'Oe'
			,	'ö' => 'oe'	,	'Œ' => 'Oe'	,	'œ' => 'oe'
			,	'ß' => 'ss'	,	'Š' => 'S'	,	'š' => 's'
			,	'ş' => 's'	,	'Ş' => 'S'
			,	'™' => 'TM'
			,	'Ù' => 'U'	,	'ù' => 'u'	,	'Ú' => 'U'	,	'ú' => 'u'
			,	'Û' => 'U'	,	'û' => 'u'	,	'Ü' => 'Ue'	,	'ü' => 'ue'
			,	'Ý' => 'Y'	,	'ý' => 'y'	,	'ÿ' => 'y'
			,	'Ž' => 'Z'	,	'ž' => 'z'
			// misc
			,	'¢' => 'Cent'	,	'€' => 'Euro'	,	'‰' => 'promille'
			,	'№' => 'Nummer'
			,	'℃' => 'Grad Celsius'	,	'°C' => 'Grad Celsius'
			,	'℉' => 'Grad Fahrenheit',	'°F' => 'Grad Fahrenheit'
			// Superscripts
			,	'⁰' => '0'	,	'¹' => '1'	,	'²' => '2'	,	'³' => '3'
			,	'⁴' => '4'	,	'⁵' => '5'	,	'⁶' => '6'	,	'⁷' => '7'
			,	'⁸' => '8'	,	'⁹' => '9'
			// Subscripts
			,	'₀' => '0'	,	'₁' => '1'	,	'₂' => '2'	,	'₃' => '3'
			,	'₄' => '4'	,	'₅' => '5'	,	'₆' => '6'	,	'₇' => '7'
			,	'₈' => '8'	,	'₉' => '9'
			// Operators, punctuation
			,	'±' => 'plusminus'	,	'×' => 'x'	,	'₊' => 'plus'
			,	'₌' => '='			,	'⁼' => '='
			,	'⁻' => '-'    // sup minus
			,	'₋' => '-'    // sub minus
			,	'–' => '-'    // ndash
			,	'—' => '-'    // mdash
			,	'‑' => '-'    // non breaking hyphen
			,	'․' => '.'    // one dot leader
			,	'‥' => '..'  // two dot leader
			,	'…' => '...'  // ellipsis
			,	'‧' => '.'    // hyphenation point
			,	' ' => '-'   // nobreak space
			,	' ' => '-'   // normal space
		);

		$str = strtr($str, $utf8);
		return trim($str, '-');
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