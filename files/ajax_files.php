<?php
/**
 * ajax_files.php
 * ファイル管理
 * @copyright 2013 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
 */
$results = "";
$comment = "";
$sessid = "";
$token = "";
if ($current_action=="start") {
	//セッション開始（一時トークンを送る）
	$results = 'ok';
	if (!isset($_SESSION['login_user']) || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else {
		$sessid = fc_get_navid(); //クッキーが使えない場合に対応
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	if($results == 'ok') {
		//ドキュメント名、親要素のID
		$page_name = fc_prepare_input_length($_POST['pagenm'], 20);
		if (fc_not_null($page_name)) {
			$page_name = fc_sanitiz_id($page_name);
			$_SESSION['page_file'] = $page_name;
		} else unset($_SESSION['page_file']);
		$section_id = fc_prepare_input_length($_POST['sectid'], 20);
		if (fc_not_null($section_id)) {
			$section_id = fc_sanitiz_id($section_id);
			$_SESSION['section_id'] = $section_id;
		} else unset($_SESSION['section_id']);
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['navid'] = $sessid;
	$rtn['token'] = $token;
	echo json_encode($rtn);

} else if ($current_action=="history") {
	//復活リスト
	$results = 'ok';
	if (!isset($_SESSION['login_user']) || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	if($results == 'ok') {
		$page_name = $_SESSION['page_file'];
		if (empty($page_name)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	$html_str = "";
	if($results == 'ok') {
		//リストの取得
		try {
			$arry =  explode('-', $page_name);
			$dir_name = $arry[0];
			$realdir = BASE_DIR . '/pages/archive/' . $dir_name;
			if (is_dir($realdir)) {
				if($dir = @dir($realdir)){
					while ($file = $dir->read()) {
						$filename = $realdir . '/' . $file;
						if (!is_dir($filename)) {
							$arry =  explode('.', $file);
							if($arry[0] == $page_name) {
								$stat = stat($filename);
								$str = "";
								$fsize = $stat['size'];
								if($fsize > 1000000) $str .= sprintf("%01.2f MB", $fsize/1000000);
								else $str .= sprintf("%01.2f KB", $fsize/1000);
								$str .= ' <em>' . date('y/m/d H:i',$stat['mtime']) . '</em>';
								$str .= ' (Act. ' . date('y/m/d H:i',$stat['atime']) . ') ';
								$html_str .= '<span onclick="file_select(this);"><em>' . $file . '</em> ' . $str . '</span><br>';
							}
						}
					}
					$dir->close();
				}

			} else {
				$comment = '履歴が登録されていません。';
				$results = 'rt';
			}
		} catch ( Exception $e ) {
			$comment = 'ファイルを参照できません。';
			$results = 'rt';
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['token'] = $token;
	$rtn['conte'] = $html_str;
	echo json_encode($rtn);

} else if ($current_action=="restore") {
	//ファイル復活
	$results = 'ok';
	if (!isset($_SESSION['login_user'])  || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	//ドキュメント名
	if($results == 'ok') {
		$page_name = $_SESSION['page_file'];
		if (empty($page_name)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	if($results == 'ok') {
		$filenm = fc_prepare_input_length($_POST['filenm'], 80);
		if (empty($filenm)) {
			$comment = 'ファイルが選択されていません。';
			$results = 'rt';
		}
	}
	if($results == 'ok') {
		try {
			$arry =  explode('-', $page_name);
			$dir_name = $arry[0];
			$backfile = BASE_DIR . '/pages/archive/' . $dir_name . '/' . $filenm;
			$tempfile = BASE_DIR . '/pages/' . $dir_name . '/' . $page_name . '.tmp';
			$pagefile = BASE_DIR . '/pages/' . $dir_name . '/' . $page_name . '.php';
			if (is_file($backfile)) {
				if(copy($backfile, $tempfile)) {
					@chmod($tempfile, 0660);
					if(is_file($tempfile)) rename($tempfile, $pagefile);
				}
			} else {
				$comment = 'ファイルが見つかりません。';
				$results = 'rt';
			}
		} catch ( Exception $e ) {
			$comment = 'ファイルを参照できません。';
			$results = 'rt';
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['token'] = $token;
	echo json_encode($rtn);

} else if ($current_action=="list") {
	//ファイルリスト
	$results = 'ok';
	if (!isset($_SESSION['login_user']) || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	if($results == 'ok') {
		$page_name = $_SESSION['page_file'];
		if (empty($page_name)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	$html_str = "";
	if($results == 'ok') {
		//リストの取得
		try {
			$dir =  explode('-', $page_name);
			$dir_name = $dir[0];
			$realdir = BASE_DIR . '/img/' . $dir_name;
			$fpath = './img/' . $dir_name;
			if($dir = @dir($realdir)){
				while ($file = $dir->read()) {
					$realpath = $realdir . '/' . $file;
					$fname = $fpath . '/' . $file;
					if (!is_dir($realpath)) {
						if (strpos($file,'_thmb.')===false) {
							$html_str .= '<span onclick="file_view(this);">' . $fname . '</span><br>';
						}
					}
				}
				$dir->close();
			}
		} catch ( Exception $e ) {
			$comment = 'ファイルを参照できません。';
			$results = 'rt';
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['token'] = $token;
	$rtn['conte'] = $html_str;
	echo json_encode($rtn);

} else if ($current_action=="delete") {
	//ファイル削除
	$results = 'ok';
	if (!isset($_SESSION['login_user'])  || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	//ドキュメント名
	if($results == 'ok') {
		$page_name = $_SESSION['page_file'];
		if (empty($page_name)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	if($results == 'ok') {
		$filenm = fc_prepare_input_length($_POST['filenm'], 80);
		if (empty($filenm)) {
			$comment = 'ファイルが選択されていません。';
			$results = 'rt';
		}
	}
	$html_str = "";
	if($results == 'ok') {
		//ファイルを削除
		try {
			$file = basename($filenm);
			$ext = substr($file, strrpos($file, '.'));
			$nam = fc_sanitiz_id(basename($filenm, $ext));

			$dir =  explode('-', $page_name);
			$dir_name = $dir[0];
			$realdir = BASE_DIR . '/img/' . $dir_name;
			$fpath = './img/' . $dir_name;

			//イメージ
			$imagefile = $realdir . '/' . $nam . $ext;
			if (is_file($imagefile)) @unlink($imagefile);
			//サムネイル
			$thumbnail = $realdir . '/' . $nam . '_thmb' . $ext;
			if (is_file($thumbnail)) @unlink($thumbnail);
			$comment = 'ファイルを削除しました。' . './img/' . $dir_name . '/' . $nam . $ext;
		} catch ( Exception $e ) {
			$comment = 'ファイルを削除できません。' . './img/' . $dir_name . '/' . $nam . $ext;
			$results = 'rt';
		}
		//一覧を再取得
		try {
			if($dir = @dir($realdir)){
				while ($file = $dir->read()) {
					$realpath = $realdir . '/' . $file;
					$fname = $fpath . '/' . $file;
					if (!is_dir($realpath)) {
						if (strpos($file,'_thmb.')===false) {
							$html_str .= '<span onclick="file_view(this);">' . $fname . '</span><br>';
						}
					}
				}
				$dir->close();
			}
		} catch ( Exception $e ) {
			$comment = 'ファイルを参照できません。';
			$results = 'rt';
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['token'] = $token;
	$rtn['conte'] = $html_str;
	echo json_encode($rtn);

} else if ($current_action=="upload") {
	//アップロード
	$results = 'ok';
	if (!isset($_SESSION['login_user'])  || empty($_SESSION['login_user'])) {
		$comment = '無効なアクセスです';
		$results = 'ng';
		sleep(3);
	} else if(empty($current_token) || $current_token != $_SESSION['current_token']) {
		$comment = 'アクセスが無効になりました。';
		$results = 'ng';
		sleep(3);
	} else {
		$token = fc_get_token();
		$_SESSION['current_token'] = $token;
	}
	if($results == 'ok') {
		$page_name = $_SESSION['page_file'];
		if (empty($page_name)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	if($results == 'ok') {
		if ($_FILES['upload']['error'] !== 0) {
			if ($_FILES['upload']['error'] == 2) {
				$comment = 'ファイルサイズエラー（Max 2MB）';
			} else if ($_FILES['upload']['error'] == 4) {
				$comment = 'ファイルが指定されていません。';
			} else $comment = 'ファイル転送エラーです。';
			$results = 'rt';
		}
	}
	if($results == 'ok') {
		try {
			$fmime = mime_content_type($_FILES['upload']['tmp_name']);
			if ($fmime == 'image/jpeg') $ext = '.jpg';
			else if ($fmime == 'image/png') $ext = '.png';
			else {
				$comment = 'ファイル種類は jpg、png のみです。';
				$results = 'rt';
			}
		} catch ( Exception $e ) {
			$comment = 'ファイル種類は jpg、png のみです。';
			$results = 'rt';
		}
		/* gifはアニメgif等、変換に対応できないため、対象から除外 */
	}
	if($results == 'ok') {
		$fsize = $_FILES['upload']['size'];
		if ($_FILES['upfile']['size'] > 2000000) { //2MBまで
			$comment = 'ファイルサイズが2MBを超えています。';
			$results = 'rt';
		}
	}
	if($results == 'ok') {
		try {
			$file = $_FILES['upload']['name'];
			$sname = fc_prepare_input_length($_POST['file_name'], 40);
			if (fc_not_null($sname)) {
				$file = $sname;
			}
			//ファイル名を再構築
			$type = substr($file, strrpos($file, '.'));
			$name = fc_sanitiz_id(basename($file, $type));
			$fname = $name . $ext;

			$dir =  explode('-', $page_name);
			$dir_name = $dir[0];
			$realdir = BASE_DIR . '/img/' . $dir_name;
			$wkpath = WORKING_DIR . '/' . $fname;

			//一時フォルダに保存
			if(move_uploaded_file($_FILES['upload']['tmp_name'], $wkpath)) {
				if (!is_dir($realdir)) {
					mkdir($realdir);
					chmod($realdir, 0775);
				}
				if(fc_save_image($wkpath, $ext, 150, 800, $realdir)) {
					$comment = "ファイルを保存しました。 " . './img/' . $dir_name . '/' . $fname;
				} else {
					$comment = 'ファイルを保存できません。';
					$results = 'rt';
				}
				@unlink($wkpath);
			} else {
				$comment = 'ファイルを保存できません。';
				$results = 'rt';
			}
		} catch ( Exception $e ) {
			$comment = 'ファイルを保存できません。';
			$results = 'rt';
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
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
// 画像のサイズ変換出力
function fc_save_image($tmp_path, $ext, $tmb_size, $new_size, $out_path) {
	$result = true;
	if ($ext == '.jpg') {
		$image = imagecreatefromjpeg($tmp_path);
		//$rotate = imagerotate($image, $degree, 0);
	} else if ($ext == '.png') {
		$image = imagecreatefrompng($tmp_path);
	}
	if(!$image){
		$result = false;
	} else {
		//ファイル名
		$name = fc_sanitiz_id(basename($tmp_path, $ext));
		//ファイルを縮小する（小さければそのまま再作成）
		$width = imagesx($image);
		$height = imagesy($image);
		$ratio = $width/$height;
		if(($width > $new_size) || ($height > $new_size)) {
			$new_width = $new_size;
			$new_height = $new_size;
			if ($new_width/$new_height > $ratio) {
				$new_width = $new_height*$ratio;
			} else {
				$new_height = $new_width/$ratio;
			}
		} else {
			$new_width = $width;
			$new_height = $height;
		}
		$new_image = imagecreatetruecolor($new_width, $new_height);
		if ($ext == '.png') { //背景透過に対応
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
		}
		imagecopyresampled($new_image,$image,0,0,0,0,$new_width,$new_height,$width,$height);
		$savefile = $out_path . '/' . $name . $ext;
		if ($ext == '.jpg') {
			imagejpeg($new_image, $savefile);
		} else if ($ext == '.png') {
			imagepng($new_image, $savefile);
		}
		chmod($savefile, 0664);
		imagedestroy($new_image);
		//サムネイルの出力
		if($width > $tmb_size || $height > $tmb_size) {
			$tmb_width = $tmb_size;
			$tmb_height = $tmb_size;
			if ($tmb_width/$tmb_height > $ratio) {
				$tmb_width = $tmb_height*$ratio;
			} else {
				$tmb_height = $tmb_width/$ratio;
			}
		} else {
			$tmb_width = $width;
			$tmb_height = $height;
		}
		$thumbnail = imagecreatetruecolor($tmb_width, $tmb_height);
		if ($ext == '.png') { //背景透過に対応
			imagealphablending($thumbnail, false);
			imagesavealpha($thumbnail, true);
		}
		imagecopyresampled($thumbnail,$image,0,0,0,0,$tmb_width,$tmb_height,$width,$height);
		$savethmb = $out_path . '/' . $name . '_thmb' . $ext;
		if ($ext == '.jpg') {
			imagejpeg($thumbnail, $savethmb);
		} else if ($ext == '.png') {
			imagepng($thumbnail, $savethmb);
		}
		chmod($savethmb, 0664);
		imagedestroy($thumbnail);
		//イメージ削除
		imagedestroy($image);
	}
	return $result;
}
?>
