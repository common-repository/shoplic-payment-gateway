<?php
/**
 * shpgB: my-page > payment (card) template
 *
 * @var string $payment_method Payment method.
 * @var string $card_name
 * @var string $card_no
 * @var bool   $is_check
 * @var string $auth_code
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
                <?php echo esc_html( $payment_method ); ?>,
				<?php echo esc_html( $card_name ); ?>
				<?php echo $is_check ? esc_html_x( 'check', 'cheque card', 'shoplic-pg' ) : ''; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Card Number', 'shoplic-pg' ); ?>:</th>
            <td>
				<?php echo esc_html( shpg_format_card_no( $card_no ) ); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e( 'Authorization Code', 'shoplic-pg' ); ?>:</th>
            <td>
				<?php echo esc_html( $auth_code ); ?>
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
