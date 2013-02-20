<?php
/*
Plugin Name: WP Code Snippets
Plugin URI: http://www.itsananderson.com/plugins/wp-code-snippets/
Description: Define and insert HTML code snippets into your posts.
Author: Will Anderson
Version: 1.1
Author URI: http://www.itsananderson.com/
*/

class WP_Code_Snippets {


	public static function start() {
		add_filter( 'the_content', array( __CLASS__, 'insert_snippets' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
	}

	public static function insert_snippets( $the_content ) {
		if ( self::circular_snippets_check() )
			return $the_content;
		$the_new_content = $the_content; // dont want to change the argument value
		$snippets = self::get_snippets();
		if ( $snippets ) {
			foreach ( $snippets as $key => $values ) {
				if ( $values['case'] ) {
					if ( strpos( $the_new_content, $key ) !== false ) {
						$the_new_content = str_replace( $key, self::expand_key( $snippets, $key ), $the_new_content );
					}
				} else {

					if ( stripos( $the_new_content, $key ) !== false ) {
						$the_new_content = str_ireplace( $key, self::expand_key( $snippets, $key ), $the_new_content );
					}
				}
			}
		}
		return $the_new_content;
	}

	public static function expand_key( $snippets, $key ) {
		if ( $snippets[$key]['expand'] ) {
			$text = $snippets[$key]['text'];
			foreach ( $snippets as $key => $values ) {
				if ( $values['case'] ) {
					if ( strpos( $text, $key ) !== false ) {
						$text = str_replace( $key, self::expand_key( $snippets, $key ), $text );
					}
				}
				else{
					if ( stripos( $text, $key ) !== false ) {
						$text = str_ireplace( $key, self::expand_key( $snippets, $key ), $text );
					}
				}
			}
			return $text;
		}
		return $snippets[$key]['text'];
	}

	public static function circular_snippets_check() {
		$snippets = self::get_snippets();
		$found_circular = false;
		if ( $snippets ) {
			foreach ( array_keys( $snippets ) as $key) {
				foreach ( $snippets as $key2 => $values ) {
					if ( isset( $snippets[$key]['case'] ) ) {
						if ( strpos( $values['text'], $key ) !== false && isset( $values['expand'] ) ) {
							$found_circular |= self::circular_snippets_helper( $snippets, array( $key2, $key ) );
						}
					} else {
						if ( stripos( $values['text'], $key ) !== false && isset( $values['expand'] ) ) {
							$found_circular |= self::circular_snippets_helper( $snippets, array( $key2, $key ) );
						}
					}
				}
			}
		}
		return $found_circular;
	}

	public static function circular_snippets_helper( $snippets, $keys ) {
		$values = $snippets[$keys[count( $keys) - 1]];
		foreach ( $keys as $key ) {
			if ( isset( $snippets[$key]['case'] ) ) {
				if ( strpos( $values['text'], $key ) !== false && isset( $values['expand'] ) ) {
					return true;
				}
			} else {
				if ( stripos( $values['text'], $key ) !== false && isset( $values['expand'] ) ) {
					return true;
				}
			}
		}
		$found_circular = false;
		foreach ( array_keys( $snippets ) as $key ) {
			if ( isset( $snippets[$key]['case'] ) ) {
				if ( strpos( $values['text'], $key ) !== false) {
					$keys[] = $key;
					$found_circular |= self::circular_snippets_helper( $snippets, $keys );
				}
			} else {
				if ( stripos( $values['text'], $key ) !== false) {
					$keys[] = $key;
					$found_circular |= self::circular_snippets_helper( $snippets, $keys );
				}
			}
		}
		return $found_circular;
	}

	public static function code_snippet_settings() {
		$snippets = self::get_snippets();
		include plugin_dir_path( __FILE__ ) . 'views/settings.php';
	}

	public static function get_snippets() {
		$snippet_keys = maybe_unserialize( get_option( 'snippet_keys' ) );
		$snippet_keys = is_array( $snippet_keys ) ? $snippet_keys : array(); // if $snippet_keys is not an array, make it one
		$snippet_values = maybe_unserialize( get_option( 'snippet_values' ) );
		$snippet_values = is_array( $snippet_values ) ? $snippet_values : array(); // if $snippet_values is not an array, make it one
		if ( count( $snippet_keys ) && count( $snippet_values ) == count( $snippet_keys) ) {
			$snippets = array_combine( $snippet_keys, $snippet_values );
			unset( $snippets[''] );
			return $snippets;
		}
		return false;
	}

	public static function add_menu() {
		add_submenu_page( 'options-general.php', __( 'Code Snippets' ), __( 'Code Snippets' ), 'administrator', __FILE__, array( __CLASS__, 'code_snippet_settings' ) );
	}
}

WP_Code_Snippets::start();

