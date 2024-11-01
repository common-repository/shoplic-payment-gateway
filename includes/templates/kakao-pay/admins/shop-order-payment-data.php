<?php
/**
 * SHPG: Kakao Pay template
 *
 * Context
 *
 * @var string $payment_method Payment method.
 * @var string $receipt_url    Receipt url.
 * @var string $raw_data_url   Payment data url.
 * @var bool   $test_mode      Is test mode.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    <div id="shpg-payment-data">
        <h3><?php esc_html_e( 'Payment', 'shoplic-pg' ); ?></h3>

        <?php if ( $payment_method ) : ?>
            <p>
                <strong><?php esc_html_e( 'Method', 'shoplic-pg' ); ?></strong>
                <span><?php echo esc_html( $payment_method ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $raw_data_url ) : ?>
            <p>
                <strong><?php esc_html_e( 'Payment Data', 'shoplic-pg' ); ?></strong>
                <span><a id="shpg-view-raw-data"
                         href="<?php echo esc_url( $raw_data_url ); ?>"><?php
                        esc_html_e( 'View payment data', 'shoplic-pg' ); ?></a></span>
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