<?php
/**
 * SHPG:
 *
 * @package sphg
 *
 * @var string $field_key Field's key identifier.
 * @var array  $data      Data array.
 * @var string $desc_html Description HTML is created by WC_Payment_Gateway instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field_key ); ?>">
			<?php echo wp_kses_post( $data['title'] ); ?>
		</label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			<input
					id="<?php echo esc_attr( $field_key ); ?>"
                    type="url"
					class="input-text regular-input"
					value="<?php echo esc_attr( $data['url'] ); ?>"
					readonly
			/>
            <p class="description"><?php echo wp_kses_post( $data['description'] ?? '' ); ?></p>
		</fieldset>
		<script>
			document.getElementById('vbank-info').addEventListener('click', function (e) {
				e.preventDefault();
				window.open(e.currentTarget.href, 'vbank-info', 'width=650,menubar=no,toolbar=no,location=no,personalbar=no,status=no,resizable=no,minimizable=no,fullscreen=no,chrome=yes,dialog=yes,titlebar=no,alwaysRaised=yes');
			});
		</script>
	</td>
</tr>
