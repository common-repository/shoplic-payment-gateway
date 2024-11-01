<?php
/**
 * SHPG: Hidden request form template.
 *
 * @var string $action_url
 * @var array  $inputs
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
    .shpg-hide-scroll {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
        overflow-y: hidden;
    }

    .shpg-hide-scroll::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera*/
    }
</style>

<form id="shpg-payment-request"
      method="post"
      action="<?php echo esc_url( $action_url ); ?>"
      accept-charset="EUC-KR">
	<?php foreach ( $inputs as $name => $value ) : ?>
        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
	<?php endforeach; ?>
</form>

<button id="shpg-open-payment-request"
        style="display: none;"><?php esc_html_e( 'Retry payment request', 'shoplic-pg' ); ?></button>
