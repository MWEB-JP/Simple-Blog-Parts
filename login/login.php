<?php
function login_button(){
	if (isset($_SESSION['login_user'])) {
		$str = "<div class='kanri_button' onclick='kanri();'>kanri</div>";
		$str .= "<div class='logout_button' onclick='logout();'>out</div>";
		return $str;
	} else {
		return "<div class='login_button' onclick='login_start(this);'>in</div>";
	}
}
?>
<div id="login">
	<div class="inner">
		<div id="login_close" onclick="login_close()">×</div>
		<div id="login_header">ログイン</div>
		<div id="login_conte">
			<form enctype="multipart/form-data" accept-charset="UTF-8" name="login_form" id="form-login" method="post" autocomplete="off">
				<input type="hidden" name="mode" value="" />
				<input type="hidden" name="action" value="" />
				<input type="hidden" name="navid" value="" />
				<input type="hidden" name="token" value="" />
				<input type="hidden" name="passcode" value="" />
				<div id="login_area">
					<div id="login_cntl_01" class="login_panel">
						<div class="login_form">
							<h3>メールアドレス</h3>
							<div>
								<input type="email" name="email_address" value="" maxlength="80">
							</div>
						</div>
						<div class="login_form">
							<h3>パスワード</h3>
							<div>
								<input type="password" name="password" value="" maxlength="80">
							</div>
						</div>
						<div class="login_commit">
							<input type="checkbox" name="login_action" id="login_action" value="1" />
							<label for="login_action">認証コードをメールします。</label>
						</div>
						<div class="login_commit">
							<input type="button" class="bt_commit" id="login_mail" value="送信する" onclick="login_check();">
						</div>
					</div>
					<div id="login_cntl_02" class="login_panel">
						<div class="login_form" id="confirm_area">
							<h3>認証コード</h3>
							<div>
								<input type="text" name="confirmation" value="" maxlength="80">
							</div>
						</div>
						<div class="login_form" id="new_pass_area">
							<h3>新パスワード</h3>
							<div>
								<input type="password" name="new_password" value="" maxlength="80">
							</div>
						</div>
						<div class="login_form" id="chk_pass_area">
							<h3>確認入力</h3>
							<div>
								<input type="password" name="chk_password" value="" maxlength="80">
							</div>
						</div>
						<div class="login_commit">
							<input type="button" class="bt_commit" id="login_commit" value="実行する" onclick="login_confirm();">
						</div>
					</div>
				</div>
			</form>
		</div>
		<div id="login_footer"></div>
	</div>
</div>
