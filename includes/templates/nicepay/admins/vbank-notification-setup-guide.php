<?php
/**
 * SNP: Virtual bank account setup guide template.
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
	<title>가상계좌 입금 통지 설정 가이드</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size: 16px;
			line-height: 1.4em;
			width: 600px;
			margin: 20px auto;
		}

		h2 {
			margin-top: 2em;
		}

		img {
			display: block;
			text-align: center;
			border: 2px solid black;
			margin: 10px 15px 20px;
			padding: 10px;
			width: 95%;
			cursor: pointer;
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
<h1>가상계좌 입금 통지 설정 가이드</h1>
<p>
	1. '<a href="https://npg.nicepay.co.kr/logIn.do" target="_blank">나이스페이 관리자</a>에 로그인합니다.
</p>

<p>
	2. '가맹점 정보' &gt; '기본 정보'로 이동합니다. 메뉴는 아래 그림을 참고하세요.
	<img src="<?php echo esc_url( plugins_url( 'assets/img/nicepay-01.jpg', shpg()->get_main_file() ) ); ?>" alt="네비게이션 메뉴 이동 가이드 이미지">
</p>

<p>
	3. 페이지로 이동하면, 화면을 아래로 스크롤 하여 '결제데이터통보' 섹션을 찾습니다.
</p>

<p>
	4. 아래 그림과 같이 설정하세요.
	<img src="<?php echo esc_url( plugins_url( 'assets/img/nicepay-02.jpg', shpg()->get_main_file() ) ); ?>" alt="가샹 계좌 입금 통지 설정 이미지">
</p>

<ol>
	<li>
		'가상계좌' 항목에 '미전송시 체크'에 체크 해제하여 사용 가능하도록 합니다.
	</li>
	<li>
		'URL/IP" 열에 관리 페이지에 있던 주소,
		"<strong><?php echo esc_html( shpg_get_nicepay_notification_url() ); ?></strong>"를
		복사해 붙여 넣습니다.
	</li>
	<li>
		재전송 간격은 1분, 재전송 횟수는 5회로 넣습니다. 다른 값을 취해도 무방합니다.
	</li>
	<li>
		'OK 체크'란에 체크합니다.'
	</li>
	<li>
		표 우측 상단의 '저장' 버튼을 눌러 변경 사항을 저장합니다.
	</li>
	<li>
		<strong>중요!</strong> 상점 관리자 페에지 &gt; 설정 &gt;
		<a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" target="_blank">고유주소</a>를 반드시 방문해
		입금 통지 URL이 제대로 인식되도록 다시 쓰기 규칙을 새로 업데이트합니다.<br>
		<a href="<?php echo esc_url( shpg_get_nicepay_notification_url() ); ?>" target="_blank">이 링크를 통해
			이동했을 때</a>
		404 페이지가 아닌, '<strong>Shoplic NicePlay VBANK notification</strong>' 이라는 메시지를 보게 되면
		제대로 설정이 완료된 것입니다.
	</li>
</ol>
<p class="close">
	<button type="button" onclick="window.close();"><?php esc_html_e( 'Close Window', 'shoplic-pg' ); ?></button>
</p>
<script>
	document.querySelectorAll('img').forEach(function (elem) {
		elem.addEventListener('click', function (e) {
			window.open(e.currentTarget.src, 'vbank-img');
		});
	});
</script>
</body>
</html>
