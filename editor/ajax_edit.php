<?php
/**
 * ajax_edit.php
 * 記事編集
 * @copyright 2013 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
 */
$results = "";
$comment = "";
$sessid = "";
$token = "";
$images = "";
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
		//イメージファイルのリスト
		$images = "";
		$dir =  explode('-', $page_name);
		$dir_name = $dir[0];
		$realdir = BASE_DIR . '/img/' . $dir_name;
		if($dir = @dir($realdir)){
			while ($file = $dir->read()) {
				$realpath = $realdir . '/' . $file;
				if (!is_dir($realpath)) {
					if (strpos($file,'_thmb.')===false) {
						if (empty($images)) $images = $file;
						else $images .= ' ' . $file;
					}
				}
			}
		}
	}

	//セッションクローズ
	fc_session_close();

	//応答
	$rtn = array();
	$rtn['results'] = $results;
	$rtn['comment'] = fc_html_output($comment);
	$rtn['navid'] = $sessid;
	$rtn['token'] = $token;
	$rtn['images'] = $images;
	echo json_encode($rtn);

} else if ($current_action=="save") {
	//編集内容の保存
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
		$section_id = $_SESSION['section_id'];
		if (empty($page_name) || empty($section_id)) {
			$comment = 'アクセスが無効になりました。';
			$results = 'ng';
			sleep(3);
		}
	}
	if($results == 'ok') {
		$edtype = fc_prepare_input_length($_POST['edtype'], 10);
		$edtype = fc_sanitiz_id($edtype);
		$motion = fc_prepare_input_length($_POST['motion'], 10);
		$motion = fc_sanitiz_id($motion);
		$backup = fc_prepare_input_length($_POST['backup'], 10);
		$backup = fc_sanitiz_id($backup);
		$content = fc_prepare_input_length($_POST['content'], 20000);
		try {
			if(DIR_DEPTH > 1) {
				$dir =  explode('-', $page_name);
				$dir_name = $dir[0];
				$realdir = BASE_DIR . '/pages/' . $dir_name;
				$archdir = BASE_DIR . '/pages/archive/' . $dir_name;
			} else {
				$realdir = BASE_DIR . '/pages';
				$archdir = BASE_DIR . '/pages/archive';
			}
			if (!is_dir($archdir)) {
				if (!mkdir($archdir)) throw new Exception('error');
				chmod($archdir, 0770);
			}
			$editfile = $realdir . '/' . $page_name . '.php';
			$tempfile = WORKING_DIR . '/' . $page_name . '_' . uniqid() . '.php';
			$archfile = $archdir . '/' . $page_name . '.php';
			$archfile1 = $archdir . '/' . $page_name . '.1.php';
			$archfile2 = $archdir . '/' . $page_name . '.2.php';
			$archfile3 = $archdir . '/' . $page_name . '.3.php';
			//前ページを履歴に保存
			copy($editfile, $archfile);
			if($backup == 'backup') {
				@chmod($archfile, 0660);
				if(is_file($archfile2)) rename($archfile2, $archfile3);
				if(is_file($archfile1)) rename($archfile1, $archfile2);
				if(is_file($archfile)) rename($archfile, $archfile1);
			}
			//編集元の読み込み
			$fp = @fopen($editfile,"rb");
			if ($fp !== false) {
				$desc = @fread($fp, filesize($editfile));
				@fclose($fp);
			}
			//編集パートの抽出
			$key = '<section id="' . $section_id . '"';
			if (stripos($desc, $key) !== false) {
				$next = stristr($desc, $key);
				$prev = stristr($desc, $key, true);
				$part = stristr($next, '</section>', true);
				$sect = stristr($part, '>', true) . '>';
				$nextpart = substr(stristr($next, '</section>'), 10);
			} else throw new Exception('error');

			if ($edtype == 'staff') {
				$btns = '<?php echo staff_button(); ?><!--STA-->';
			} else {
				$btns = '<?php echo edit_button(); ?><?php echo add_button(); ?><!--STA-->';
			}

			if ($motion == 'edit') {
				//内容の更新（不要なTAGは除く）
				$text = $sect . $btns . "\n";
				$text .= fc_clean_html($content);
				$text .= "\n" . '</section>';
				$output = $prev . $text . $nextpart;
			} else if ($motion == 'add') {
				//新しいID
				for($i = 1; $i < 99; $i++) {
					$new_id = sprintf('s%03d',$i);
					$chk = 'id="' . $new_id . '"';
					if(stripos($desc, $chk) === false) break;
				}
				$new_sect = str_ireplace($section_id, $new_id, $sect);
				$text = $new_sect . $btns . "\n";
				$text .= fc_clean_html($content);
				$text .= "\n" . '</section>';
				$output = $prev . $part .'</section>' . "\n" . $text . $nextpart;
			} else if ($motion == 'delete') {
				$output = $prev . $nextpart;
			} else throw new Exception('error');

			//日付、ジャンプ先等の更新
			$output = fc_edit_change($output);
			//不要な改行の削除
			$output = preg_replace('/\n\n+/', "\n\n", $output);

			//ファイルに書き出し
			$fp = @fopen($tempfile,"w");
			if ($fp !== false) {
				@fwrite($fp, $output);
				@fclose($fp);
			}

			//ファイルの差し替え
			@rename($editfile,$archfile);
			if(copy($tempfile, $editfile)) {
				@chmod($editfile, 0660);
				$comment = '編集を保存しました。';
				//一時ファイルの削除
				@unlink($tempfile);
			} else {
				$comment = '編集を保存できません。';
				$results = 'rt';
			}
		} catch ( Exception $e ) {
			$comment = '編集を登録できません。';
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
////////////////////////////////////////////////////////////
// 日付、ジャンプ先等のページ情報の更新
function fc_edit_change($src_txt) {
	$chg_txt = $src_txt;

	$chk_txt = $src_txt;
	$nav_txt = "";
	$key = '<section id="';
	while(stripos($chk_txt, $key) !== false){
		$next = stristr($chk_txt, $key);
		$part = stristr($next, '</section>', true);
		$chk_txt = substr(stristr($next, '</section>'), 10);
		//セクション内のデータ
		$id_str = '';
		$opt = 'id="';
		if (stripos($part, $opt) !== false) {
			$text = substr(stristr($part, $opt), 4, 10);
			$id_str = stristr($text, '"', true);
		}
		$tt_str = '';
		$opt = '<h2>';
		if (stripos($part, $opt) !== false) {
			$text = substr(stristr($part, $opt), 4);
			$tt_str = stristr($text, '</h2>', true);
		}
		if($id_str != '' && $tt_str != '') {
			$nav_txt .= '<a href="javaScript: go_to(' . "'" . $id_str . "'" . ')">' . $tt_str . '</a>'. "\n";
		}
	}
	//ジャンプリストの更新
	$key = '<nav class="pool">';
	if (stripos($chg_txt, $key) !== false) {
		$next = stristr($chg_txt, $key);
		$prev = stristr($chg_txt, $key, true);
		$next = stristr($next, '</nav>');
		$chg_txt = $prev;
		$chg_txt .= '<nav class="pool">' . "\n";
		$chg_txt .= $nav_txt;
		$chg_txt .= $next;
	}
	//編集日付の更新
	$key = '<span class="date">';
	if (stripos($chg_txt, $key) !== false) {
		$next = stristr($chg_txt, $key);
		$prev = stristr($chg_txt, $key, true);
		$next = stristr($next, '</span>');
		$chg_txt = $prev;
		$chg_txt .= '<span class="date">';
		$chg_txt .= strftime("%Y年%m月%d日 %H:%M ",strtotime(fc_date()));
		$chg_txt .= 'by ' . fc_html_output($_SESSION['login_user']);
		$chg_txt .= $next;
	}
	return $chg_txt;
}

