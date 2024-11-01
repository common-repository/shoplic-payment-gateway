<?php
/**
 * SNP: admins > shop order > order data template.
 *
 * Context
 *
 * @var string $payment_method Payment method.
 * @var string $card_name      Card name.
 * @var bool   $is_check       True if check card.
 * @var string $card_no        Masked card number.
 * @var string $auth_code      Authorization code.
 * @var string $receipt_url    Receipt url.
 * @var string $raw_data_url   Payment data url.
 * @var bool   $test_mode
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

	<div id="shpg-payment-data">
		<h3><?php esc_html_e( 'Payment', 'shoplic-pg' ); ?></h3>
		<?php if ( $payment_method ) : ?>
			<p>
				<strong><?php esc_html_e( 'Method', 'shoplic-pg' ); ?></strong>
				<span><?php echo esc_html( $payment_method ); ?>,
					<?php echo esc_html( $card_name ); ?>
					<?php echo $is_check ? esc_html( '(' . _x( 'check', 'cheque card', 'shoplic-pg' ) . ')' ) : ''; ?>
					<?php echo esc_html( $card_no ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( $auth_code ) : ?>
			<p>
				<strong><?php esc_html_e( 'Authorization Code', 'shoplic-pg' ); ?></strong>
				<span><?php echo esc_html( $auth_code ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( $receipt_url ) : ?>
			<p>
				<strong><?php esc_html_e( 'Receipt', 'shoplic-pg' ); ?></strong>
				<span><a id="shpg-view-receipt" href="<?php echo esc_url( $receipt_url ); ?>">
						<?php esc_html_e( 'View receipt', 'shoplic-pg' ); ?></a></span>
			</p>
		<?php endif; ?>

		<?php if ( $raw_data_url ) : ?>
			<p>
				<strong><?php esc_html_e( 'Payment Data', 'shoplic-pg' ); ?></strong>
				<span><a id="shpg-view-raw-data" href="<?php echo esc_url( $raw_data_url ); ?>">
						<?php esc_html_e( 'View payment data', 'shoplic-pg' ); ?></a></span>
			</p>
		<?php endif; ?>

		<?php if ( isset( $test_mode ) && $test_mode ) : ?>
			<p>
				<strong><?php esc_html_e( 'Test Mode', 'shoplic-pg' ); ?></strong>
				<span><?php esc_html_e( 'Yes', 'shoplic-pg' ); ?></span>
			</p>
		<?php endif; ?>
	</div>

<?php
require __DIR__ . '/shop-order-payment-data-inline-js.php';
