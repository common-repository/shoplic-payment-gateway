<?php
/**
 * shpgB: my-page > subscriptions > payment method inline JavaScript
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<script>
    (function (w, h) {
        w = 535;
        h = 415;
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('shpg-view-receipt').addEventListener('click', function (e) {
                var t = (screen.availHeight - h) * 0.5, l = (screen.availWidth - w) * 0.5,
                    p = 'width=' + w + ',height=' + h + ',left=' + l + ',top=' + t + ',menubar=no,toolbar=no,location=no,personalbar=no,status=no,resizable=no,minimizable=no,fullscreen-no,chrome=yes,dialog=yes,titlebar=no,alwaysRaised=yes';
                e.preventDefault();
                window.open(e.target.href, 'shoplic-pg', p)
            });
        });
    })();
</script>
