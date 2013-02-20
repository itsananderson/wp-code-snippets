<?php
/*
Plugin Name: Code Snippets
Plugin URI: http://www.itsananderson.com/plugins/code-snippets
Description: Define and insert HTML code snippets into your posts.
Author: Will Anderson
Version: 1.1
Author URI: http://www.itsananderson.com/
*/

function insert_snippets( $the_content ) {
	if ( circular_snippets_check() )
		return $the_content;
	$the_new_content = $the_content; // dont want to change the argument value
	$snippets = get_snippets();
	if ( $snippets ) {
		foreach ( $snippets as $key => $values ) {
			if ( $values['case'] ) {
				if ( strpos( $the_new_content, $key ) !== false ) {
					$the_new_content = str_replace( $key, expand_key( $snippets, $key ), $the_new_content );
				}
			} else {

				if ( stripos( $the_new_content, $key ) !== false ) {
					$the_new_content = str_ireplace( $key, expand_key( $snippets, $key ), $the_new_content );
				}
			}
		}
	}
	return $the_new_content;
}

function expand_key( $snippets, $key ) {
	if ( $snippets[$key]['expand'] ) {
		$text = $snippets[$key]['text'];
		foreach ( $snippets as $key => $values ) {
			if ( $values['case'] ) {
				if ( strpos( $the_new_content, $key ) !== false ) {
					$text = str_replace( $key, expand_key( $snippets, $key ), $the_new_content );
				}
			}
			else{
				if ( stripos( $the_new_content, $key ) !== false ) {
					$text = str_ireplace( $key, expand_key( $snippets, $key ), $the_new_content );
				}
			}
		}
		return $text;
	}
	return $snippets[$key]['text'];
}

function circular_snippets_check() {
	$snippets = get_snippets();
	$found_circular = false;
	if ( $snippets ) {
		foreach ( array_keys( $snippets ) as $key) {
			foreach ( $snippets as $key2 => $values ) {
				if ( $snippets[$key]['case'] ) {
					if ( strpos( $values['text'], $key ) !== false && $values['expand'] ) {
						$found_circular |= circular_snippets_helper( $snippets, array( $key2, $key ) );
					}
				} else {
					if ( stripos( $values['text'], $key ) !== false && $values['expand'] ) {
						$found_circular |= circular_snippets_helper( $snippets, array( $key2, $key ) );
					}
				}
			}
		}
	}
	return $found_circular;
}

function circular_snippets_helper( $snippets, $keys ) {
	$values = $snippets[$keys[count( $keys) - 1]];
	foreach ( $keys as $key ) {
		if ( $snippets[$key]['case'] ) {
			if ( strpos( $values['text'], $key ) !== false && $values['expand'] ) {
				return true;
			}
		} else {
			if ( stripos( $values['text'], $key ) !== false && $values['expand'] ) {
				return true;
			}
		}
	}
	$found_circular = false;
	foreach ( array_keys( $snippets ) as $key ) {
		if ( $snippets[$key]['case'] ) {
			if ( strpos( $values['text'], $key ) !== false) {
				$keys[] = $key;
				$found_circular |= circular_snippets_helper( $snippets, $keys );
			}
		} else {
			if ( stripos( $values['text'], $key ) !== false) {
				$keys[] = $key;
				$found_circular |= circular_snippets_helper( $snippets, $keys );
			}
		}
	}
	return $found_circular;
}

function code_snippet_settings() {
	$snippets = get_snippets();
	?>
<div class="wrap">
	<h2>Code Snippets</h2>
	<?php if ( circular_snippets_check() ) { ?>
	<p class='error' style='padding: 10px;'>
		<strong>Warning! Circular Snippet Codes Detected</strong><br />
		No code snippets can not be inserted until this problem is fixed. For
		more information about circular snippets please see the
		<a href="http://www.itsananderson.com/plugins/code-snippets/">plugin website</a>.
	</p>
	<?php }?>
	<form method="post" action="options.php"><?php wp_nonce_field( 'update-options' ); ?>
		<table class="form-table">
			<tr>
				<th><?php _e( 'Snippet Name' )?></th>
				<th><?php _e( 'Snippet Text') ?></th>
				<th><?php _e( 'Case Sensitive') ?></th>
				<th><?php _e( 'Expand') ?></th>
			</tr>
			<?php
			$count = -1; // needed if no snippets exist
			if ( $snippets ) {
				foreach ( $snippets as $name => $values ) {
					$count++;
					?>
			<tr valign="top">
				<td><input type="text" name="snippet_keys[<?php echo $count ?>]" value="<?php echo $name ?>" /></td>
				<td><textarea rows="3" cols="100" name="snippet_values[<?php echo $count ?>][text]"><?php echo $values['text'] ?></textarea></td>
				<td><input type="checkbox" name="snippet_values[<?php echo $count ?>][case]" <?php echo $values['case'] ? 'checked="checked"' : '' ?> /></td>
				<td><input type="checkbox" name="snippet_values[<?php echo $count ?>][expand]" <?php echo $values['expand'] ? 'checked="checked"' : '' ?> /></td>
			</tr>
			<?php
				}
			}
			?>
			<tr valign="top">
				<td><input type="text" name="snippet_keys[<?php echo $count + 1 ?>]" /></td>
				<td><textarea rows="3" cols="100" name="snippet_values[<?php echo $count + 1 ?>][text]"></textarea></td>
				<td><input type="checkbox" name="snippet_values[<?php echo $count + 1 ?>][case]" checked="checked" /></td>
				<td><input type="checkbox" name="snippet_values[<?php echo $count + 1 ?>][expand]" checked="checked" /></td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="snippet_keys, snippet_values" />
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Save Snippets') ?>" />
		</p>
	</form>
</div>
<?php
}

function get_snippets() {
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

function register_snippets_settings_page() {
add_submenu_page( 'options-general.php', __( 'Code Snippets' ), __( 'Code Snippets' ), 5, __FILE__, 'code_snippet_settings' );
}

add_filter( 'the_content', 'insert_snippets' );
add_action( 'admin_menu', 'register_snippets_settings_page' );
?>