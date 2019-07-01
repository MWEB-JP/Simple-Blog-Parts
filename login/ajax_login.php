<?php
/**
 * ajax_login.php
 * ログイン・認証メール送信
 * @copyright 2013 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
 */
$results = "";
$comment = "";
$sessid = "";
$token = "";
$passcode = "";
if ($current_action=="logoff") {
	//ログアウト
	$results = 'ok';

	//ログアウトの確認
	if (isset($_SESSION['login_user'])) {
		$comment = "ログアウトしました。";
	}
	fc_session_destroy();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	echo json_encode($rtn);

} else if ($current_action=="start") {
	//ログインセッションの開始（一時トークンを送る）
	$results = 'ok';
	$sessid = fc_get_navid(); //クッキーが使えない場合に対応
	$token = fc_get_token();
	$_SESSION['current_token'] = $token;
	$passcode = fc_get_passwd_token();
	$_SESSION['passwd_token'] = $passcode;

	//セッションクローズ
	fc_session_close();
	unset($_SESSION['login_count']);

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['navid'] = $sessid;
	$rtn['token'] = $token;
	$rtn['passcode'] = $passcode;
	echo json_encode($rtn);

} else if ($current_action=="check") {
	//パスワードの確認
	$results = 'ok';
	$action = 'login';
	if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
		$email_address = fc_prepare_input_length($_POST['email_address'], 80);
		$_SESSION['email_address'] = $email_address;
		$encpwd = fc_prepare_input_length($_POST['encpwd'], 240);
		if (!fc_not_null($email_address) && !fc_not_null($encpwd)) {
			$comment = "アドレスまたはパスワードが未設定です。";
			$results = 'rt';
			unset($_SESSION['email_address']);
		} else if(!fc_validate_email($email_address)) {
			//メールアドレスの書式が不正
			$comment = 'このメールアドレスに対応できません。';
			$results = 'rt';
			unset($_SESSION['email_address']);
		} else if(fc_mail_file_exist($email_address)) {
			$comment = 'しばらく時間をおいて下さい。';
			$results = 'ng';
			unset($_SESSION['email_address']);
		}

		if($results == 'ok') {
			$_SESSION['login_mode'] = 'login';
			// パスワード確認
			$passcode = $_SESSION['passwd_token'];
			$password = passDecode($encpwd, $passcode);
			if ($password == CREATE_NEW_PASSWD) {
				// パスワード登録
				if(in_array($email_address, $accepted_users)){
					// $accepted_usersで指定されたメールアドレスだけ登録を許可する
					$action = 'passwd';
					$_SESSION['login_mode'] = 'passwd';
				} else {
					// 許可されていないメールアドレス
					$results = 'rt';
					$comment = 'このメールアドレスに対応できません。';
				}
			} else {
				// パスワードを確認
				$obj = fc_fget_encrypted($email_address);
				$chkpwd = $obj->encrypted;
				if(empty($chkpwd) || !fc_validate_password($password,$chkpwd)){
					$results = 'rt';
					$comment = 'アドレスまたはパスワードが一致しません。';
				}
			}
		}

		if($results == 'ok') {
			// 確認コード生成
			$confirm_id = fc_rand(13569,64331);
			$_SESSION['confirm_id'] = $confirm_id;
			// 確認コードをメールで送信
			$mail = new PHPMailer();
			$mail->Subject = LOGIN_SUBJECT;
			$mail->AddAddress($email_address);
			if (strlen(LOGIN_CC_SEND)>0) $mail->AddAddress(LOGIN_CC_SEND);
			$mail->Body = fc_login_body($email_address, $confirm_id);
			if (!$mail->Send()) {
				$results = 'ng';
				$comment = 'サーバエラーによりメール送信できません。';
				$debug = $mail->GetError();
			} else {
				$comment = '認証コードをメール送信しました。';
				unset($_SESSION['login_count']);
			}
		}

	}

	//ログインカウント
	if (!isset($_SESSION['login_count'])) {
		$_SESSION['login_count'] = 0;
	} else {
		$_SESSION['login_count']++;
	}
	if($_SESSION['login_count'] >= 3) {
		sleep(3);
		if($_SESSION['login_count'] >= 5) {
			//ログインを繰り返し失敗する場合は、メールアドレスを停止
			if(isset($_SESSION['email_address'])) {
				$comment = 'しばらく時間をおいて下さい。';
				fc_mail_file_write($_SESSION['email_address']);
			}
			$results = 'ng';
		}
	} 
	//$comment .= " " . $_SESSION['login_count'];

	//セッションクローズ
	if($results == 'ng') fc_session_destroy();
	else fc_session_close();

	// 応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = $comment;
	$rtn['action'] = $action;
	$rtn['token'] = $token;
	echo json_encode($rtn);
} else if ($current_action=="confirm") {
	//認証コードの確認
	$results = 'ok';
	if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
		//認証コード
		$confirm_id = fc_prepare_input_length($_POST['confirmation'], 80);
		if(empty($confirm_id) || $confirm_id != $_SESSION['confirm_id']) {
			$results = 'rt';
			$comment = '認証コードが正しくありません。';
		}
		$email_address = $_SESSION['email_address'];
		if(fc_mail_file_exist($email_address)) {
			$comment = 'しばらく時間をおいて下さい。';
			$results = 'ng';
		}

		if($results == 'ok') {
			list( $user, $domain ) = explode( "@", $email_address );
			if($_SESSION['login_mode'] == 'passwd') {
				//パスワード登録の場合
				$encpwd = fc_prepare_input_length($_POST['encpwd'], 240);
				$passcode = $_SESSION['passwd_token'];
				$new_passwd = passDecode($encpwd, $passcode);
				//パスワードのチェック
				if (mb_strlen($new_passwd) >= 8) {
					$comment = 'パスワードを登録しました';
					fc_fput_encrypted($email_address, $new_passwd, $user);
				} else {
					$results = 'rt';
					$comment = 'パスワードは8文字以上です。';
				}
			} else {
				$comment = 'ログイン認証しました。';
			}
		}
		if($results == 'ok') {
			//ログイン時にセッションを再作成する
			fc_session_recreate();
			$_SESSION['login_user'] = $user;
			unset($_SESSION['login_count']);
			unset($_SESSION['passwd_token']);
			unset($_SESSION['confirm_id']);
			unset($_SESSION['current_token']);
		}

	}

	//ログインカウント
	if (!isset($_SESSION['login_count'])) {
		$_SESSION['login_count'] = 0;
	} else {
		$_SESSION['login_count']++;
	}
	if($_SESSION['login_count'] >= 3) {
		sleep(3);
		if($_SESSION['login_count'] >= 5) {
			//ログインを繰り返し失敗する場合は、メールアドレスを停止
			if(isset($_SESSION['email_address'])) {
				$comment = 'しばらく時間をおいて下さい。';
				fc_mail_file_write($_SESSION['email_address']);
			}
			$results = 'ng';
		}
	} 
	//$comment .= " " . $_SESSION['login_count'];

	//セッションクローズ
	if($results == 'ng') fc_session_destroy();
	else fc_session_close();

	// 応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = $comment;
	$rtn['token'] = $token;
	echo json_encode($rtn);

} else {
	fc_session_destroy();
	sleep(3);

	//応答
	$rtn = array();
	$rtn['results'] = 'ng';
	$rtn['comment'] = 'Invalid Access!';
	echo json_encode($rtn);
}
////////////////////////////////////////////////////////////
// メールメッセージ
function fc_login_body($email_address, $confirm_id) {
	$str = LOGIN_SUBJECT;
	$str .= "\n";
	$str .= "\n";
	$str .= "アクセス許可のために、以下の認証コードをログイン画面から入力して下さい。";
	$str .= "\n";
	$str .= "\n";
	$str .= "\n";
	$str .= "認証コード : " . fc_html_output($confirm_id);
	$str .= "\n";
	$str .= "\n";
	$str .= "\n";
	$str .= "◇こちらは自動送信メールです◇";
	$str .= "\n";
	$str .= "本メールは送信専用のアドレスから送信しております。";
	$str .= "\n";
	$str .= "本メールのアドレスには返信なさらないようお願します。";
	$str .= "\n";
	$str .= "\n";
	return $str;
}
////////////////////////////////////////////////////////////
// 不正アクセス対策
//メールアドレスをファイルとして登録する
function fc_mail_file_write($email_address) {
    $ad_file = WORKING_DIR . '/' . md5($email_address);
    $fp = @fopen($ad_file,"w");
    if ($fp !== false) {
        @fwrite($fp,$email_address);
        @fclose($fp);
    }
    return true;
}
//メールアドレスに対するファイルがあるか確認（3分間）
function fc_mail_file_exist($email_address) {
    $ad_file = WORKING_DIR . '/' . md5($email_address);
    if (is_file($ad_file)) {
        $time = filemtime($ad_file) + 3*60;
        $now = time();
        if($now > $time) {
            @unlink($ad_file);
        } else {
            return true;
        }
    }
    return false;
}
?>
