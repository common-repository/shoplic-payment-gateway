<?php
/**
 * Raw data template.
 *
 * Context:
 *
 * @var int   $order_id
 * @var array $payment_result
 * @var array $notification_result
 * @var array $refund_results
 *
 * @package shpg
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo esc_html( /* translators: order id. */ sprintf( __( 'Payment data of order #%1$d', 'shoplic-pg' ), $order_id ) ); ?>
    </title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
            line-height: 1.4em;
        }

        h2 {
            margin-top: 2em;
        }

        table {
            margin: 0;
            width: 100%;
            border: 1px solid #dcdcde;
            border-spacing: 0;
            background-color: #f6f7f7;
        }

        thead th {
            background-color: #f6f7f7;
            border-bottom: 1px solid #dcdcde;
        }

        thead th:first-child,
        tbody th {
            border-right: 1px solid #dcdcde;
        }

        tbody td {
            padding-left: 1em;
        }

        tr {
            vertical-align: top;
        }

        tr:nth-child(2n+1) {
            background-color: #fff;
        }

        .close {
            text-align: right;
        }

        button {
            background: #2271b1;
            border-color: #2271b1;
            border-radius: 3px;
            border-style: solid;
            border-width: 1px;
            box-sizing: border-box;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            line-height: 2.15384615;
            min-height: 30px;
            padding: 0 10px;
            text-decoration: none;
            text-shadow: none;
            white-space: nowrap;
            -webkit-appearance: none;
        }

        button::-moz-focus-inner {
            border-width: 0;
            border-style: none;
            padding: 0;
        }
    </style>
</head>
<body>
<h1>
    <?php
    echo esc_html(
    /* translators: raw data count */
        sprintf( __( 'Payment Data: #%1$d', 'shoplic-pg' ), $order_id )
    );
    ?>
</h1>

<h2><?php esc_html_e( 'Payment Result', 'shoplic-pg' ); ?></h2>
<p class="description">
    <?php
    wp_kses(
        sprintf(
        // translators: NicePay official document URL.
            __( 'Please refer to <a href="%1$s" target="_blank">documentation</a> for each key.', 'shoplic-pg' ),
            esc_url( 'https://developers.nicepay.co.kr/manual-auth.php#parameter-api-response' )
        ),
        [ 'a' => [ 'href' => true ] ]
    );
    ?>
</p>
<table class="form-table">
    <?php if ( ! empty( $payment_result ) ) : ?>
        <thead>
        <tr>
            <th>
                <pre><?php esc_html_e( 'Key', 'shoplic-pg' ); ?></pre>
            </th>
            <th>
                <pre><?php esc_html_e( 'Value', 'shoplic-pg' ); ?></pre>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $payment_result as $key => $val ) : ?>
            <tr>
                <th scope="row">
                    <pre><?php echo esc_html( $key ); ?></pre>
                </th>
                <td>
                    <pre><?php
                        if ( is_array( $val ) ) {
                            echo esc_html( print_r( $val, 1 ) );
                        } else {
                            echo esc_html( $val );
                        }
                        ?></pre>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    <?php else : ?>
        <p>No DATA</p>
    <?php endif; ?>
</table>

<?php if ( ! empty( $notification_result ) ) : ?>
    <h2><?php esc_html_e( 'Notification Result', 'shoplic-pg' ); ?></h2>
    <table class="form-table">
        <thead>
        <tr>
            <th>
                <pre><?php esc_html_e( 'Key', 'shoplic-pg' ); ?></pre>
            </th>
            <th>
                <pre><?php esc_html_e( 'Value', 'shoplic-pg' ); ?></pre>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $notification_result as $key => $val ) : ?>
            <tr>
                <th scope="row">
                    <pre><?php echo esc_html( $key ); ?></pre>
                </th>
                <td>
                    <?php if ( 'MerchantKey' === $key ) : ?>
                        <pre style="font-style: italic;"><?php esc_html_e( 'MerchantKey was not saved due to security reason.', 'shoplic-pg' ); ?></pre>
                    <?php else : ?>
                        <pre><?php echo esc_html( $val ); ?></pre>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if ( ! empty( $refund_results ) ) : ?>
    <h2><?php echo esc_html( _n( 'Refund Result', 'Refund Results', count( $refund_results ), 'shoplic-pg' ) ); ?> </h2>

    <p class="description">
        <?php
        printf(
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.MissingTranslatorsComment
            __( 'Please refer to <a href="%1$s" target="_blank">documentation</a> for each key.', 'shoplic-pg' ),
            esc_url( 'https://developers.nicepay.co.kr/manual-auth.php#parameter-netcancel-response' )
        );
        ?>
    </p>

    <?php foreach ( $refund_results as $idx => $refund_result ) : ?>
        <?php if ( $refund_result ) : ?>

            <?php if ( count( $refund_results ) > 1 ) : ?>
                <h3>
                    Refund Data #<?php echo esc_html( $idx + 1 ); ?>
                </h3>
            <?php endif; ?>

            <table class="form-table">
                <thead>
                <tr>
                    <th>
                        <pre><?php esc_html_e( 'Key', 'shoplic-pg' ); ?></pre>
                    </th>
                    <th>
                        <pre><?php esc_html_e( 'Value', 'shoplic-pg' ); ?></pre>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $refund_result as $key => $val ) : ?>
                    <tr>
                        <th scope="row">
                            <pre><?php echo esc_html( $key ); ?></pre>
                        </th>
                        <td>
                            <pre><?php echo esc_html( $val ); ?></pre>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>

<?php endif; ?>

<p class="close">
    <button type="button" onclick="window.close();"><?php esc_html_e( 'Close Window', 'shoplic-pg' ); ?></button>
</p>

</body>
</html>
