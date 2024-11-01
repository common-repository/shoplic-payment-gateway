<?php
/**
 *
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>


<script>
	(function () {
		function openPopup(url, target, w, h) {
			var t = (screen.availHeight - h) * 0.5, l = (screen.availWidth - w) * 0.5,
					features = 'width=' + w + ',height=' + h + ',left=' + l + ',top=' + t + ',menubar=no,toolbar=no,location=no,personalbar=no,status=no,resizable=no,minimizable=no,fullscreen=no,chrome=yes,dialog=yes,titlebar=no,alwaysRaised=yes';
			window.open(url, target, features);
		}

		document.addEventListener('DOMContentLoaded', function () {
			var rawData = document.getElementById('shpg-view-raw-data');
			if (rawData) {
				rawData.addEventListener('click', function (e) {
					e.preventDefault();
					openPopup(e.target.href, 'shpg-raw-data', 600, screen.availHeight - 100);
				});
			}
		});
	})();
</script>

