/**
 * editor.js
 * @copyright 2018 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
 */
var started = false;
var submitted = false;
var old_src = new Array();
//編集画面を閉じる////////////////////////////////
function edit_close(){
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("editor");
	var obj3 = document.getElementById("edit_view");
	if(obj1 != null && obj2 != null && obj3 != null){
		var frm = document.getElementById("form-edit");
		frm.reset();
		obj1.style.cssText = "display: none;";
		obj2.style.cssText = "display: none;";
		obj3.style.cssText = "display: none;";
	}
	started = false;
	submitted = false;
	return false;
}
//編集画面スタート////////////////////////////////
function edit_start(obj, no){
	if (started == true) {
		return false;
	}
	started = true;
	var edtype = ['edit','add','staff'][no];

	//バックボードとダイアログを表示する
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("editor");
	if(obj1 != null && obj2 != null){
		document.getElementById("edit_footer").innerHTML = "";

		//ドキュメント名、親要素のIDを取得
		var sectid = obj.parentNode.id;
		var str = document.location.toString().split('/').pop();
		var pagenm = str.split("=").pop();
		if(!pagenm) pagenm = document_name;
		var clsname = obj.parentNode.className;

		//フォームに保存
		var frm = document.getElementById("form-edit");
		frm.sectid.value = sectid;
		frm.pagenm.value = pagenm;
		frm.clsname.value = clsname;
		frm.edtype.value = edtype;
		//console.log(sectid+' '+pagenm);

		//サーバに接続
		var req = "mode=edit";
		req += "&action=start";
		req += "&sectid="+encodeURIComponent(sectid);
		req += "&pagenm="+encodeURIComponent(pagenm);
		var resp = xhrSendJson(req);
		//console.log(resp);

		if((typeof resp) === 'object') {
			var results = resp.results;
			document.getElementById("edit_footer").innerHTML = resp.comment;
			if(results == "ok") {
				frm.navid.value = resp.navid;
				frm.token.value = resp.token;
				frm.images.value = resp.images;
				//コントロールを表示する
				var ctrl1 = '';
				if (edtype=='add') {
					ctrl1 += '<li onclick="edit_view(2)"><img src="./img/icon/document_add.png" title="新規追加" /></li>';
				} else if (edtype=='staff') {
					ctrl1 += '<li onclick="edit_view(1)"><img src="./img/icon/document_edit.png" title="変更を保存" /></li>';
				} else {
					ctrl1 += '<li onclick="edit_view(1)"><img src="./img/icon/document_edit.png" title="変更を保存" /></li>';
					ctrl1 += '<li onclick="edit_view(2)"><img src="./img/icon/document_add.png" title="この記事を後に追加" /></li>';
					ctrl1 += '<li onclick="edit_view(3)"><img src="./img/icon/document_delete.png" title="この記事を削除" /></li>';
				}
				document.getElementById("edit_motion").innerHTML = ctrl1;
				//編集ボタンを表示する
				var cntl2 = '';
				if (edtype=='staff') {
					cntl2 += '<li onclick="edit_add_tag(0)"><img src="./img/icon/img_box.png" title="画像を追加" /></li>';
					cntl2 += '<li onclick="edit_img_circle()"><img src="./img/icon/img_circle.png" title="丸く表示" /></li>';
					cntl2 += '<li onclick="edit_img_change()"><img src="./img/icon/img_change.png" title="画像切替え" /></li>';
					cntl2 += '<li onclick="edit_add_box()"><img src="./img/icon/add_box.png" title="スタッフ" /></li>';
				} else {
					cntl2 += '<li onclick="edit_add_tag(0)"><img src="./img/icon/img_box.png" title="画像を追加" /></li>';
					cntl2 += '<li onclick="edit_img_change()"><img src="./img/icon/img_change.png" title="画像切替え" /></li>';
					cntl2 += '<li onclick="edit_img_size()"><img src="./img/icon/img_size.png" title="サイズ変更" /></li>';
					cntl2 += '<li onclick="edit_img_view()"><img src="./img/icon/img_view.png" title="写真を拡大ビュー" /></li>';
					cntl2 += '<li onclick="edit_add_tag(1)"><img src="./img/icon/add_tag_h.png" title="小見出し" /></li>';
					cntl2 += '<li onclick="edit_add_tag(2)"><img src="./img/icon/add_tag_p.png" title="テキスト" /></li>';
					cntl2 += '<li onclick="edit_add_tag(3)"><img src="./img/icon/add_tag_a.png" title="リンク" /></li>';
					cntl2 += '<li onclick="edit_add_tag(4)"><img src="./img/icon/add_tag_c.png" title="コード" /></li>';
					cntl2 += '<li onclick="edit_add_tag(5)"><img src="./img/icon/add_tag_t.png" title="４段テーブル" /></li>';
					cntl2 += '<li onclick="edit_add_tag(6)"><img src="./img/icon/form_ul2.png" title="画像を左ブロック" /></li>';
					cntl2 += '<li onclick="edit_add_tag(7)"><img src="./img/icon/form_ur2.png" title="画像を右ブロック" /></li>';
					cntl2 += '<li onclick="edit_add_tag(8)"><img src="./img/icon/form_list.png" title="２段リスト" /></li>';
				}
				document.getElementById("edit_layout").innerHTML = cntl2;
				//内容を取得する
				if (edtype=='add') {
					//新規作成の内容
					var src = '<h2>タイトル</h2>';
				} else {
					//親要素の内容を取得
					var src = obj.parentNode.innerHTML;
					//ヘッダーまでを削除（ヘッダーが無い場合はSTAまで）
					var ph = src.lastIndexOf('<h2>');
					if(ph < 0) {
						ph = src.lastIndexOf('<!--STA-->');
						if(ph >= 0) ph = ph + 10;
					}
					src = src.slice(ph);
				}
				//編集するテキスト内容を表示する
				document.getElementById("edit_area").style.cssText = '';
				document.getElementById("edit_textarea").value = src;

				//前回値として保存する
				old_src.push(src);

				//編集画面の表示
				obj1.style.cssText = "display: block;";
				obj2.style.cssText = "display: block;";
				submitted = false;
			}
		}
	}
	started = false;
	return false;
}
//////////////////////////////////////////////////
//classオプションの置き換え
function edit_tag_replace_atter(prev, next, tag, att, set, arry){
	var str = "";
	var p0 = prev.lastIndexOf('<'+tag);
	if (p0 >= 0) {
		var prev0 = prev.slice(0, p0);
		var next0 = prev.slice(p0);
		var e0 = next0.indexOf('>');
		if(e0 >= 0) {
			//タグ部分の抽出
			str = next0.slice(0, e0+1);
			next0 = next0.slice(e0+1);
			next = next0 + next;
		} else {
			var e0 = next.indexOf('>');
			str = next0 + next.slice(0, e0+1);
			next = next.slice(e0+1);
		}
		prev = prev0;

		//属性部分の抽出
		var p1 = str.indexOf(att+'="');
		if (p1 >= 0) {
			p1 = str.indexOf('"', p1);
			e1 = str.indexOf('"', p1+1);
			var prev1 = str.slice(0, p1+1);
			var str1 = str.slice(p1+1, e1);
			var next1 = str.slice(e1);

			//重複設定の削除
			var chk = "";
			if(arry == "") {
				//総入れ替え
				var str1 = ""; 
			} else {
				var n = arry.length;
				while (n--) {
					if (str1.indexOf(arry[n]) !== -1) {
						chk = arry[n];
						break;
					}
				}
			}
			if (chk != "") {
				str1 = str1.replace(chk, set);
			} else {
				str1 = str1+ ' ' + set;
			}
			str = prev1 + str1.trim() + next1;
		} else {
			var e1 = str.lastIndexOf('/>');
			if (e1 < 0) e1 = str.lastIndexOf('>');
			if (e1 >= 0) {
				var prev1 = str.slice(0, e1);
				var next1 = str.slice(e1);
				var str1 = ' ' + att + '="' + set + '"';
			}
			str = prev1 + str1 + next1;
		}
		str = prev + str + next;
	}
	return str;
}
//タグまたはオプションを抽出
function edit_tag_get_atter(prev, next, tag, opt) {
	var str = "";
	var p0 = prev.lastIndexOf('<'+tag);
	if (p0 >= 0) {
		var prev0 = prev.slice(0, p0);
		var next0 = prev.slice(p0);
		var e0 = next0.indexOf('>');
		if(e0 >= 0) {
			//タグ部分の抽出
			str = next0.slice(0, e0+1);
		} else {
			var e0 = next.indexOf('>');
			str = next0 + next.slice(0, e0+1);
		}
		//属性部分の抽出
		var p1 = str.indexOf(opt+'="');
		if (p1 >= 0) {
			p1 = str.indexOf('"', p1);
			e1 = str.indexOf('"', p1+1);
			var prev1 = str.slice(0, p1+1);
			var str1 = str.slice(p1+1, e1);
			var next1 = str.slice(e1);
			return str1;
		}
	}
	return "";
}
//編集ボタン//////////////////////////////////
//画像を丸く表示する
function edit_img_circle(){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;
	var pos = obj.selectionEnd;
	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	var arry = ['circle'];

	var rtn = edit_tag_replace_atter(prev, next, 'img', 'class', 'circle', arry);
	if (rtn != "") {
		obj.value = rtn;
		//前回値として保存する
		//old_src.push(src);
		//if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		//obj.scrollTop = sc_top;
	}
	document.getElementById("edit_area").style.cssText = '';
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//画像を変更する
var thmptimer = false;
function edit_img_change(){
	var frm = document.getElementById("form-edit");
	var pagenm = frm.pagenm.value;
	var dir_name = pagenm.split('-').shift();
	var images = frm.images.value;

	//登録画像が無い場合は実施しない
	if(images.length < 1) return false;

	//画像をリストアップ
	var arry = images.split(' ');

	var obj = document.getElementById("edit_textarea");
	var src = obj.value;
	var pos = obj.selectionEnd;
	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	//現在の割付け画像
	var now_src = edit_tag_get_atter(prev, next, 'img', 'src');

	//次の画像を選択
	var img = '';
	if(now_src != '') {
		var n = arry.length;
		while (n--) {
			if (now_src.indexOf(arry[n]) !== -1) {
				img = (n < (arry.length - 1))? arry[n+1]:arry[0];
				break;
			}
		}
	}
	if (img == "") img = arry[0];

	//画像
	var img_src = './img/'+dir_name+'/'+img;
	var rtn = edit_tag_replace_atter(prev, next, 'img', 'src', img_src, '');
	if (rtn != "") {
		obj.value = rtn;
		//編集画面の背景に画像を参考表示
		var thmp = './img/'+dir_name+'/' + img.split('.').shift() + '_thmb.' + img.split('.').pop();
		document.getElementById("edit_area").style.cssText = 'background: url("'+thmp+'") center center no-repeat;';
		if (thmptimer !== false) clearTimeout(thmptimer);
		thmptimer = setTimeout("document.getElementById('edit_area').style.cssText=''", 2000);
		//前回値として保存する
		//old_src.push(src);
		//if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		//obj.scrollTop = sc_top;
	}
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//画像のサイズを変更する
function edit_img_size(){
	var arry = ['w150','w200','w250','w300','w350','w400'];

	//現在のサイズ
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;
	var pos = obj.selectionEnd;
	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	var now_txt = edit_tag_get_atter(prev, next, 'img', 'class');

	//次のサイズを選択
	var n = arry.length;
	var sz1 = "";
	var sz2 = "";
	while (n--) {
		if (now_txt.indexOf(arry[n]) !== -1) {
			sz1 = arry[n];
			sz2 = (n < (arry.length - 1))? arry[n+1]:arry[0];
			break;
		}
	}
	if (sz2 == "") sz2 = arry[0];

	var rtn = edit_tag_replace_atter(prev, next, 'img', 'class', sz2, arry);
	if (rtn != "") {
		obj.value = rtn;
		//前回値として保存する
		//old_src.push(src);
		//if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		//obj.scrollTop = sc_top;
	}
	document.getElementById("edit_area").style.cssText = '';
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//拡大表示を追加する
function edit_img_view(){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;
	var pos = obj.selectionEnd;
	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	var rtn = edit_tag_replace_atter(prev, next, 'img', 'onclick', 'image_view(this);', '');
	if (rtn != "") {
		obj.value = rtn;
		//前回値として保存する
		//old_src.push(src);
		//if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		//obj.scrollTop = sc_top;
	}
	document.getElementById("edit_area").style.cssText = '';
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//範囲を囲む
function edit_range(no){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;

	//前回値として保存する
	old_src.push(src);
	if(old_src.length > 5) old_src.shift();

	var pos_st = obj.selectionStart;
	var pos_en = obj.selectionEnd;
	var sc_top = obj.scrollTop;

	var prev = src.slice(0, pos_st);
	var text = src.slice(pos_st, pos_en);
	var next = src.slice(pos_en);

	var str = "";
	if(no == 0) {
		str = text + '<br>';
	} else if(no == 1) {
		str = '<div>' + text + '</div>';
	} else if(no == 2) {
		str = '<em>' + text + '</em>';
	} else if(no == 3) {
		str = '<span class="red">' + text + '</span>';
	} else if(no == 4) {
		str = '<mark>' + text + '</mark>';
	} else if(no == 5) {
		str = '<del>' + text + '</del>';
	}
	src = prev + str + next;
	document.getElementById("edit_area").style.cssText = '';
	obj.value = src;

	//キャレットの移動とスクロール
	var pnt = pos_st + str.length;
	//obj.scrollTop = sc_top;
	obj.focus();
	obj.selectionEnd = pnt;
	return false;
}
//前に戻す
function edit_restore(){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;
	var pos = obj.selectionEnd;
	if(old_src.length > 0) {
		obj.value = old_src.pop();
	}
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//背景色を変更する
function edit_bg_color(no){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;

	var pos = obj.selectionEnd;
	var sc_top = obj.scrollTop;

	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	var set = "";
	if(no == 0) {
		set = 'memo';
	} else if(no == 1) {
		set = 'note';
	} else if(no == 2) {
		set = 'win10';
	} else if(no == 3) {
		set = 'linux';
	}

	var arry = ['code','note','memo','panel','win10','linux'];
	var tag = 'p'; //preを含む
	var rtn = edit_tag_replace_atter(prev, next, tag, 'class', set, arry);
	if(rtn != "") {
		obj.value = rtn;
		//前回値として保存する
		old_src.push(src);
		if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		obj.scrollTop = sc_top;
	}
	document.getElementById("edit_area").style.cssText = '';
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//色を変更する
function edit_color(no){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;

	var pos = obj.selectionEnd;
	var sc_top = obj.scrollTop;

	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	var set = "";
	if(no == 0) {
		set = 'red';
	} else if(no == 1) {
		set = 'green';
	} else if(no == 2) {
		set = 'blue';
	} else if(no == 3) {
		set = 'yellow';
	} else if(no == 4) {
		set = 'cyan';
	} else if(no == 5) {
		set = 'orange';
	}

	var arry = ['red','green','blue','yellow','cyan','orange'];

	var p0 = prev.lastIndexOf('<span');
	var p1 = prev.lastIndexOf('<em');
	var p2 = prev.lastIndexOf('<p');

	var tag = 'p'
	if (p0 > p1 && p0 > p2) tag = 'span';
	else if (p1 > p2) tag = 'em';

	var rtn = edit_tag_replace_atter(prev, next, tag, 'class', set, arry);
	if(rtn != "") {
		obj.value = rtn;
		//前回値として保存する
		old_src.push(src);
		if(old_src.length > 5) old_src.shift();
		//キャレットの移動とスクロール
		pos = rtn.length - next.length;
		obj.scrollTop = sc_top;
	}
	document.getElementById("edit_area").style.cssText = '';
	obj.focus();
	obj.selectionEnd = pos;
	return false;
}
//タグを追加する
function edit_add_tag(no){
	var obj = document.getElementById("edit_textarea");
	var src = obj.value;

	//前回値として保存する
	old_src.push(src);
	if(old_src.length > 5) old_src.shift();

	var pos = obj.selectionEnd;
	var sc_top = obj.scrollTop;

	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	//Tagを追加
	var str = "";
	if(no == 0) {
		str = '<img class="w200" src="./img/sp.png" />';
	} else if(no == 1) {
		str = '<h3>';
		str = str + 'タイトル';
		str = str + '</h3>';
	} else if(no == 2) {
		str = '<p>';
		str = str + 'テキスト<br>';
		str = str + '</p>';
	} else if(no == 3) {
		str = '<a href="https://" target="_blank">リンク</a>';
	} else if(no == 4) {
		str = '<pre class="code">';
		str = str + 'コード';
		str = str + '</pre>';
	} else if(no == 5) {
		str = '<ul class="tables">';
		str = str + "\n"+'<li>項目</li>';
		str = str + '<li>データ</li>';
		str = str + '<li>データ</li>';
		str = str + '<li>データ</li>';
		str = str + "\n"+'</ul>';
	} else if(no == 6) {
		str = '<div class="blockf">';
		str = str + "\n"+'<img class="w200" src="./img/sp.png" />';
		str = str + "\n"+'<p>';
		str = str + 'テキスト<br>';
		str = str + '</p>';
		str = str + "\n"+'</div>';
	} else if(no == 7) {
		str = '<div class="blockf">';
		str = str + "\n"+'<p>';
		str = str + 'テキスト<br>';
		str = str + '</p>';
		str = str + "\n"+'<img class="w200" src="./img/sp.png" />';
		str = str + "\n"+'</div>';
	} else if(no == 8) {
		str = '<dl>';
		str = str + "\n"+'<dt>';
		str = str + 'テキスト<br>';
		str = str + '</dt>';
		str = str + '<dd>';
		str = str + 'テキスト<br>';
		str = str + '</dd>';
		str = str + "\n"+'</dl>';
	}
	src = prev + str + next;
	document.getElementById("edit_area").style.cssText = '';
	obj.value = src;

	//キャレットの移動とスクロール
	var pnt = pos + str.length;
	obj.scrollTop = sc_top;
	obj.focus();
	obj.selectionEnd = pnt;
	return false;
}
//////////////////////////////////////////////////
//記事を追加する
function edit_add_box(){
	var obj = document.getElementById("edit_textarea");
	var pos = obj.selectionEnd;
	var src = obj.value;
	var sc_top = obj.scrollTop;

	var prev = src.slice(0, pos);
	var next = src.slice(pos);

	//Tagを追加
	var str = "\n"+'<div class="staff">';
	str = str + "\n"+'<span>○○ ○○(職種)</span>';
	str = str + "\n"+'<img src="./img/sample2.jpg" />';
	str = str + "\n"+'<p>';
	str = str + "\n"+'いつも元気！<br>';
	str = str + "\n"+'やさしい笑顔！<br>';
	str = str + "\n"+'明るく朗らかです！<br>';
	str = str + "\n"+'</p>';
	str = str + "\n"+'</div>';

	var src = prev + str + next;
	document.getElementById("edit_area").style.cssText = '';
	obj.value = src;

	//キャレットの移動とスクロール
	var pnt = pos + str.length;
	//obj.scrollTop = sc_top;
	obj.focus();
	obj.selectionEnd = pnt;
	return false;
}
//編集内容を確認//////////////////////////////////
function edit_view(mno){
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("editor");
	var obj3 = document.getElementById("edit_view");
	if(obj2 != null && obj3 != null){
		var motion =  ['none','edit','add','delete'][mno];
		var msg = "";
		if(motion == 'edit') {
			msg = "変更を保存";
		} else if(motion == 'add') {
			msg = "この記事を後に追加";
		} else if(motion == 'delete') {
			msg = "この記事を削除";
		} else return false;
		document.getElementById("edit_check_msg").innerHTML = msg;
		//編集の動作
		var frm = document.getElementById("form-edit");
		frm.motion.value = motion;
		var clsname = frm.clsname.value;
		if(clsname == "") clsname = 'infos center';
		//編集コンテンツ
		var sectid = frm.sectid.value;
		if(motion == 'delete') {
			var str = document.getElementById(sectid).innerHTML;
			//先頭のコントロールを削除
			var ph = str.lastIndexOf('<!--STA-->');
			if(ph >= 0) {
				ph = ph + 10;
			} else {
				ph = str.lastIndexOf('<h2>');
			}
			if(ph >= 0) str = str.slice(ph);
		} else {
			var str = document.getElementById("edit_textarea").value;
			//全角空白とタグを変換
			str = str.replace(/　/g, '  ');
			str = str.replace(/\t/g, '    ');
			document.getElementById("edit_textarea").value = str;
		}
		var src = '<section class="'+clsname+'">';
		src = src + str;
		src = src + '</section>';
		document.getElementById("edit_view_conte").innerHTML = src;
		//確認表示
		obj2.style.cssText = "display: none;";
		var viewTop = window.pageYOffset;
		obj3.style.cssText = "display: block; margin-top: "+viewTop+"px;";
		document.getElementById("edit_check").checked = false;
		if(motion == 'delete') {
			document.getElementById("edit_backup").checked = true;
		} else {
			document.getElementById("edit_backup").checked = false;
		}
		document.getElementById("edit_view_footer").innerHTML = "";
	}
	return false;
}
//編集画面に戻る////////////////////////////////
function edit_back(){
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("editor");
	var obj3 = document.getElementById("edit_view");
	if(obj1 != null && obj2 != null && obj3 != null){
		obj2.style.cssText = "display: block;";
		obj3.style.cssText = "display: none;";
		document.getElementById("edit_area").style.cssText = '';
	}
	return false;
}
//編集内容を保存//////////////////////////////////
function edit_save() {
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("edit_view_footer").innerHTML = "";

	if(document.getElementById("edit_check").checked == false){
		var check = document.getElementById("edit_check_msg").innerHTML;
		check = "[" + check + "] をチェックして下さい。";
		document.getElementById("edit_view_footer").innerHTML = check;
		submitted = false;
		return false;
	}

	//設定のチェック
	var frm = document.getElementById("form-edit");
	var navid = frm.navid.value;
	var token = frm.token.value;
	var edtype = frm.edtype.value;
	var motion = frm.motion.value;
	var content = frm.edit_textarea.value;

	var backup = "";
	if(document.getElementById("edit_backup").checked != false) backup = "backup";

	document.getElementById("edit_save_action").disabled = "disabled";

	//サーバに送信
	var req = "mode=edit";
	req += "&action=save";
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	req += "&edtype="+encodeURIComponent(edtype);
	req += "&motion="+encodeURIComponent(motion);
	req += "&backup="+encodeURIComponent(backup);
	req += "&content="+encodeURIComponent(content);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("edit_view_footer").innerHTML = resp.comment;
		if(results == "ok") {
			setTimeout("window.location.reload(true)",500);
			edit_close();
			document.getElementById("edit_save_action").disabled = "";
		} else if(results == "rt") {
			document.getElementById("edit_save_action").disabled = "";
		}
	}

	//画面に戻る
	submitted = false;
	return false;
}
//////////////////////////////////////////////////

