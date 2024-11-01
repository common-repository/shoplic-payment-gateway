<?php
/**
 * shpg: my-page > payment (bank) template
 *
 * Context
 *
 * @var string $payment_method    Payment method.
 * @var string $bank_name         Bank name.
 * @var string $receipt_type      Receipt type.
 * @var string $receipt_tid       Receipt transaction id.
 * @var string $receipt_auth_code Receipt authorization code.
 * @var string $receipt_url
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
            <th scope="row"><?php esc_html_e( 'Bank Name', 'shoplic-pg' ); ?>:</th>
            <td><?php echo esc_html( $bank_name ); ?></td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Receipt', 'shoplic-pg' ); ?>:</th>
            <td><?php echo esc_html( $receipt_type ); ?></td>
        </tr>
		<?php if ( $receipt_tid ) : ?>
            <tr>
                <th scope="row"><?php esc_html_e( 'Receipt TID', 'shoplic-pg' ); ?>:</th>
                <td><?php echo esc_html( $receipt_tid ); ?></td>
            </tr>
		<?php endif; ?>
		<?php if ( $receipt_auth_code ) : ?>
            <tr>
                <th scope="row"><?php esc_html_e( 'Receipt Authorization Code', 'shoplic-pg' ); ?>:</th>
                <td><?php echo esc_html( $receipt_auth_code ); ?></td>
            </tr>
		<?php endif; ?>
        <tr>
            <th scope="row"><?php esc_html_e( 'Expiration', 'shoplic-pg' ); ?>:</th>
            <td>
                <a id="shpg-view-receipt"
                   href="<?php echo esc_url( $receipt_url ); ?>">
					<?php esc_html_e( 'View receipt', 'shoplic-pg' ); ?>
                </a>
            </td>
        </tr>
        </tbody>
    </table>

<?php require __DIR__ . '/order-details-inline-js.php';
