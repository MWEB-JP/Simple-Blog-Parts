/**
 * file.js
 * @copyright 2018 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
 */
var started = false;
var submitted = false;
//ファイル管理画面を閉じる////////////////////////
function file_close(){
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("file_editor");
	if(obj1 != null && obj2 != null){
		var frm = document.getElementById("form-file_edit");
		frm.reset();
		obj1.style.cssText = "display: none;";
		obj2.style.cssText = "display: none;";
	}
	started = false;
	submitted = false;
	return false;
}
//ファイル管理画面////////////////////////////////
function file_start(obj){
	if (started == true) {
		return false;
	}
	started = true;

	//バックボードとダイアログを表示する
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("file_editor");
	if(obj1 != null && obj2 != null){
		document.getElementById("file_footer").innerHTML = "";

		//ドキュメント名、親要素のIDを取得
		var sectid = obj.parentNode.id;
		var str = document.location.toString().split('/').pop();
		var pagenm = str.split("=").pop();
		if(!pagenm) pagenm = document_name;

		//フォームに保存
		var frm = document.getElementById("form-file_edit");
		frm.sectid.value = sectid;
		frm.pagenm.value = pagenm;
		//console.log(sectid+' '+pagenm);

		//サーバに接続
		var req = "mode=files";
		req += "&action=start";
		req += "&sectid="+encodeURIComponent(sectid);
		req += "&pagenm="+encodeURIComponent(pagenm);
		var resp = xhrSendJson(req);
		//console.log(resp);

		if((typeof resp) === 'object') {
			var results = resp.results;
			document.getElementById("file_footer").innerHTML = resp.comment;
			if(results == "ok") {
				frm.navid.value = resp.navid;
				frm.token.value = resp.token;
				//ファイル管理画面の表示
				obj1.style.cssText = "display: block;";
				obj2.style.cssText = "display: block;";
				document.getElementById("file_cntl_01").style.cssText = "display: block;";
				document.getElementById("file_cntl_02").style.cssText = "display: none;";
				document.getElementById("file_cntl_03").style.cssText = "display: none;";
				document.getElementById("file_upload_action").disabled = "";
				submitted = false;
			}
		}
	}
	started = false;
	return false;
}
//////////////////////////////////////////////////
//ファイルアップロードの表示//////////////////////
function file_upload_panel(){
	if (started == true) {
		return false;
	}
	started = true;
	document.getElementById("file_footer").innerHTML = "";

	document.getElementById("file_cntl_01").style.cssText = "display: block;";
	document.getElementById("file_cntl_02").style.cssText = "display: none;";
	document.getElementById("file_cntl_03").style.cssText = "display: none;";
	document.getElementById("file_div_next").style.cssText = "";
	submitted = false;
	started = false;
	return false;
}
//ファイルアップロード////////////////////////////
function file_upload() {
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("file_footer").innerHTML = "";

	//設定のチェック
	var frm = document.getElementById("form-file_edit");
	var files = frm.upload.files;
	if (files.length < 1) {
		document.getElementById("file_footer").innerHTML = "ファイルが選択されていません。";
		submitted = false;
		return false;
	}
	document.getElementById("file_upload_action").disabled = "disabled";
	var fname= files.item(0).name;

	//サーバに送信
	frm.mode.value = "files";
	frm.action.value = "upload";
	var resp = xhrSendForm("file_edit_form");
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("file_footer").innerHTML = resp.comment;
		if(results == "ok") {
			frm.upload.value = "";
			frm.file_name.value = "";
			document.getElementById("file_upload_action").disabled = "";
		} else if(results == "rt") {
			document.getElementById("file_upload_action").disabled = "";
		} else {
			setTimeout("file_close()",3000);
		}
	} else {
		document.getElementById("file_footer").innerHTML = "サーバに接続できません。";
	}

	//画面に戻る
	submitted = false;
	return false;
}
//////////////////////////////////////////////////
//ファイル一覧の表示//////////////////////////////
function file_list_panel(){
	if (started == true) {
		return false;
	}
	started = true;
	document.getElementById("file_footer").innerHTML = "";

	//ドキュメント名、親要素のIDを取得
	var frm = document.getElementById("form-file_edit");
	var navid = frm.navid.value;
	var token = frm.token.value;

	//サーバに接続
	var req = "mode=files";
	req += "&action=list";
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("file_footer").innerHTML = resp.comment;
		document.getElementById("file_div_prev").innerHTML = "";
		document.getElementById("file_div_next").innerHTML = "";
		frm.filenm.value = "";
		if(results == "ok") {
			document.getElementById("file_div_next").innerHTML = resp.conte;
		} else {
			started = false;
			return false;
		}
		document.getElementById("file_delete_action").disabled = "";
	} else {
		document.getElementById("file_footer").innerHTML = "サーバに接続できません。";
		document.getElementById("file_delete_action").disabled = "disabled";
		started = false;
		return false;
	}
	document.getElementById("file_cntl_01").style.cssText = "display: none;";
	document.getElementById("file_cntl_02").style.cssText = "display: block;";
	document.getElementById("file_cntl_03").style.cssText = "display: none;";
	document.getElementById("file_div_next").style.cssText = "min-height: 150px;";
	submitted = false;
	started = false;
	return false;
}
//ファイル一覧の画像表示////////////////////////////
function file_view(obj){
	//サムネイルを表示
	var fnm = obj.innerHTML;
	var pss = fnm.slice(0,fnm.lastIndexOf('.'));
	var ext = fnm.slice(fnm.lastIndexOf('.'));
	//console.log(pss+" "+ext);
	var thmb = pss+'_thmb'+ext;
	var str = "<img src='"+thmb+"' />";
	document.getElementById("file_div_prev").innerHTML = str;
	//対象を保存
	var frm = document.getElementById("form-file_edit");
	frm.filenm.value = fnm;
	//対象をハイライト
	var children = obj.parentNode.childNodes;
	for (var i = 0; i < children.length; i++) {
		children[i].style.cssText = "";
	}
	obj.style.cssText = "background: #cfcfff;";
	document.getElementById("file_footer").innerHTML = "";
}
//ファイル削除////////////////////////////////////
function file_delete() {
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("file_footer").innerHTML = "";

	//設定のチェック
	var frm = document.getElementById("form-file_edit");
	var navid = frm.navid.value;
	var token = frm.token.value;
	var filenm = frm.filenm.value;
	if (filenm.length < 1) {
		document.getElementById("file_footer").innerHTML = "ファイルが選択されていません。";
		submitted = false;
		return false;
	}
	document.getElementById("file_delete_action").disabled = "disabled";

	//サーバに送信
	var req = "mode=files";
	req += "&action=delete";
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	req += "&filenm="+encodeURIComponent(filenm);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("file_footer").innerHTML = resp.comment;
		document.getElementById("file_div_prev").innerHTML = "";
		document.getElementById("file_div_next").innerHTML = "";
		frm.filenm.value = "";
		if(results == "ok") {
			document.getElementById("file_div_next").innerHTML = resp.conte;
			document.getElementById("file_delete_action").disabled = "";
		}
	} else {
		document.getElementById("file_footer").innerHTML = "サーバに接続できません。";
		document.getElementById("file_delete_action").disabled = "disabled";
	}
	//画面に戻る
	submitted = false;
	return false;
}
//////////////////////////////////////////////////
//ファイル復活画面////////////////////////////////
function file_restore_panel(){
	if (started == true) {
		return false;
	}
	started = true;
	document.getElementById("file_footer").innerHTML = "";

	//ドキュメント名、親要素のIDを取得
	var frm = document.getElementById("form-file_edit");
	var navid = frm.navid.value;
	var token = frm.token.value;

	//サーバに接続
	var req = "mode=files";
	req += "&action=history";
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("file_footer").innerHTML = resp.comment;
		document.getElementById("file_history").innerHTML = "";
		frm.filenm.value = "";
		if(results == "ok") {
			document.getElementById("file_history").innerHTML = resp.conte;
		} else {
			started = false;
			return false;
		}
		document.getElementById("file_restore_action").disabled = "";
	} else {
		document.getElementById("file_footer").innerHTML = "サーバに接続できません。";
		document.getElementById("file_restore_action").disabled = "disabled";
		started = false;
		return false;
	}
	document.getElementById("file_cntl_01").style.cssText = "display: none;";
	document.getElementById("file_cntl_02").style.cssText = "display: none;";
	document.getElementById("file_cntl_03").style.cssText = "display: block;";
	submitted = false;
	started = false;
	return false;
}
//ファイル選択////////////////////////////////////
function file_select(obj){
	//ファイル名
	var name = obj.childNodes[0].innerHTML;
	console.log(name);
	var frm = document.getElementById("form-file_edit");
	frm.filenm.value = name;
	//ハイライト
	var children = obj.parentNode.childNodes;
	for (var i = 0; i < children.length; i++) {
		children[i].style.cssText = "";
	}
	obj.style.cssText = "background: #cfcfff;";
	document.getElementById("file_footer").innerHTML = "";
}
//ファイル復活////////////////////////////////////
function file_restore() {
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("file_footer").innerHTML = "";

	//設定のチェック
	var frm = document.getElementById("form-file_edit");
	var navid = frm.navid.value;
	var token = frm.token.value;
	var filenm = frm.filenm.value;
	if (filenm.length < 1) {
		document.getElementById("file_footer").innerHTML = "ファイルが選択されていません。";
		submitted = false;
		return false;
	}
	document.getElementById("file_restore_action").disabled = "disabled";

	//サーバに送信
	var req = "mode=files";
	req += "&action=restore";
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	req += "&filenm="+encodeURIComponent(filenm);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("file_footer").innerHTML = resp.comment;
		document.getElementById("file_history").innerHTML = "";
		frm.filenm.value = "";
		if(results == "ok") {
			setTimeout("window.location.reload(true)",500);
			file_close();
			document.getElementById("file_restore_action").disabled = "";
		}
	} else {
		document.getElementById("file_footer").innerHTML = "サーバに接続できません。";
		document.getElementById("file_restore_action").disabled = "disabled";
	}
	//画面に戻る
	submitted = false;
	return false;
}
//////////////////////////////////////////////////
