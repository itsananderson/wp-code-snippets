<div class="wrap">
	<h2>Code Snippets</h2>
	<?php if ( WP_Code_Snippets::circular_snippets_check() ) { ?>
	<div class='error' style='padding: 10px;'>
		<strong>Warning! Circular Snippet Codes Detected</strong><br />
		No code snippets can not be inserted until this problem is fixed.
	</div>
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
				<td><input type="checkbox" name="snippet_values[<?php echo $count ?>][case]" <?php checked( isset( $values['case'] ) ); ?> /></td>
				<td><input type="checkbox" name="snippet_values[<?php echo $count ?>][expand]" <?php checked( isset( $values['expand'] ) ); ?> /></td>
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
			<input type="submit" name="Submit" value="<?php _e('Save Snippets') ?>" class="button-primary" />
		</p>
	</form>
</div>