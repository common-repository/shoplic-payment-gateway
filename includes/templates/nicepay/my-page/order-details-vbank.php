<?php
/**
 * my-page > payment (bank) template
 *
 * Context
 *
 * @var string $payment_method Payment method.
 * @var string $bank_name      Bank name.
 * @var string $account_number Virtual account number.
 * @var string $expiration     Transfer expiration.
 * @var string $receipt_url
 * @var string $notification_result
 * @var bool   $test_mode
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

    <h2><?php esc_html_e( 'Payment Information', 'shoplic-pg' ); ?></h2>

    <table class="woocommerce-table woocommerce-table--shpg-payment shop_table shpg-payment">
        <tbody>
        <tr>
            <th scope="row"><?php esc_html_e( 'Payment Method', 'shoplic-pg' ); ?>:</th>
            <td>
				<?php if ( isset( $test_mode ) && $test_mode ) : ?>
					<?php esc_html_e( '[TEST MODE]', 'shoplic-pg' ); ?>
				<?php endif; ?>
				<?php echo esc_html( $payment_method ); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Account', 'shoplic-pg' ); ?>:</th>
            <td>
				<?php echo esc_html( $bank_name ); ?>
				<?php echo esc_html( $account_number ); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Expiration', 'shoplic-pg' ); ?>:</th>
            <td>
				<?php echo esc_html( $expiration ); ?>
            </td>
        </tr>

		<?php if ( $notification_result ) : ?>
            <tr>
                <th scope="row">
                    <?php echo esc_html_x( 'Notification', 'Virtaul bank account deposit notification.', 'shoplic-pg' ); ?>:
                </th>
                <td>
					<?php echo esc_html( $notification_result ); ?>
                </td>
            </tr>
		<?php endif; ?>

		<?php if ( $receipt_url ) : ?>
            <tr>
                <th scope="row"><?php esc_html_e( 'Receipt', 'shoplic-pg' ); ?>:</th>
                <td>
                    <a id="shpg-view-receipt"
                       href="<?php echo esc_url( $receipt_url ); ?>">
						<?php esc_html_e( 'View receipt', 'shoplic-pg' ); ?>
                    </a>
                </td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>

<?php require __DIR__ . '/order-details-inline-js.php';
