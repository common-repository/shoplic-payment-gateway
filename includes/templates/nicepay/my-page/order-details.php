<?php
/**
 * SHPG: my-page > payment template
 *
 * @var string $payment_method
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
            <th scope="row"><?php esc_html_e( 'Payment method', 'shoplic-pg' ); ?>:</th>
            <td>
	            <?php if ( isset( $test_mode ) && $test_mode ) : ?>
		            <?php esc_html_e( '[TEST MODE]', 'shoplic-pg' ); ?>
	            <?php endif; ?>
				<?php echo esc_html( $payment_method ); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Receipt', 'shoplic-pg' ); ?>:</th>
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
