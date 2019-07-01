/**
 * login.js
 * @copyright 2013 mweb.jp, 2019 mweb.jp
 * @license GNU Public License V2.0
*/
var started = false;
var submitted = false;
//ログイン画面を閉じる////////////////////////////
function login_close(){
	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("login");
	if(obj1 != null && obj2 != null){
		var frm = document.getElementById("form-login");
		frm.reset();
		obj1.style.cssText = "display: none;";
		obj2.style.cssText = "display: none;";
		document.getElementById("login_mail").disabled = "";
		document.getElementById("login_commit").disabled = "";
	}
	started = false;
	submitted = false;
	return false;
}
//管理画面//////////////////////////////////////
function kanri(){
	window.open("./kanri/kanri.php","kanri");
}
//ログアウト//////////////////////////////////////
function logout(){
	if (started == true) {
		return false;
	}
	started = true;

	var frm = document.getElementById("form-login");
	var req = "mode=login";
	req += "&action=logoff";
	var resp = xhrSendJson(req);
	//console.log(resp);
	//ログアウト（画面再表示）
	setTimeout("window.location.reload(true)",500);

	started = false;
	submitted = false;
	return false;
}
//ログインの開始//////////////////////////////////
function login_start(obj){
	if (started == true) {
		return false;
	}
	started = true;

	var obj1 = document.getElementById("backbord");
	var obj2 = document.getElementById("login");
	if(obj1 != null && obj2 != null){
		document.getElementById("login_footer").innerHTML = "";

		//サーバに接続
		var frm = document.getElementById("form-login");
		var req = "mode=login";
		req += "&action=start";
		var resp = xhrSendJson(req);
		//console.log(resp);

		if((typeof resp) === 'object') {
			var results = resp.results;
			document.getElementById("login_footer").innerHTML = resp.comment;
			if(results == "ok") {
				frm.navid.value = resp.navid;
				frm.token.value = resp.token;
				frm.passcode.value = resp.passcode;
			} else {
				started = false;
				return false;
			}
		} else {
			document.getElementById("login_footer").innerHTML = "サーバに接続できません。";
			started = false;
			return false;
		}

		obj1.style.cssText = "display: block;";
		obj2.style.cssText = "display: block;";
		document.getElementById("login_cntl_01").style.cssText = "display: block;";
		document.getElementById("login_cntl_02").style.cssText = "display: none;";
		submitted = false;
	}
	started = false;
	return false;
}
//ログイン確認////////////////////////////////////
function login_check(){
	if(document.getElementById("login_action").checked == false) return false;
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("login_footer").innerHTML = "";

	var frm = document.getElementById("form-login");
	var email_address = frm.email_address.value;
	var password = frm.password.value;
	var navid = frm.navid.value;
	var token = frm.token.value;
	var passcode = frm.passcode.value;
	if(email_address=="" || password==""){
		document.getElementById("login_footer").innerHTML = "アドレスまたはパスワードが未設定です。";
		submitted = false;
		return false;
	}
	document.getElementById("login_mail").disabled = "disabled";
	var encpwd = passEncode(password, passcode);

	var req = "mode=login";
	req += "&action=check";
	req += "&email_address="+encodeURIComponent(email_address);
	req += "&encpwd="+encodeURIComponent(encpwd);
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		var action = resp.action;
		frm.action.value = resp.action;
		frm.token.value = resp.token;
		document.getElementById("login_footer").innerHTML = resp.comment;
		if(results == "ok") {
			document.getElementById("login_cntl_01").style.cssText = "display: none;";
			document.getElementById("login_cntl_02").style.cssText = "display: block;";
			if(action == "passwd") {
				document.getElementById("login_commit").value = "登録する";
				document.getElementById("confirm_area").style.cssText = "";
				document.getElementById("new_pass_area").style.cssText = "";
				document.getElementById("chk_pass_area").style.cssText = "";
			} else {
				document.getElementById("login_commit").value = "認証する";
				document.getElementById("confirm_area").style.cssText = "margin: 34px 0;";
				document.getElementById("new_pass_area").style.cssText = "display: none;";
				document.getElementById("chk_pass_area").style.cssText = "display: none;";
			}
		} else if(results == "rt") {
			document.getElementById("login_mail").disabled = "";
			frm.password.value = "";
		} else {
			setTimeout("login_close()",3000);
		}
	} else {
		document.getElementById("login_footer").innerHTML = "サーバに接続できません。";
	}
	//画面に戻る
	submitted = false;
	return false;
}
//ログイン確定///////////////////////////////////
function login_confirm(){
	if (submitted == true) {
		return false;
	}
	submitted = true;
	document.getElementById("login_footer").innerHTML = "";

	var frm = document.getElementById("form-login");
	var confirmation = frm.confirmation.value;
	var action = frm.action.value;
	var navid = frm.navid.value;
	var token = frm.token.value;
	var passcode = frm.passcode.value;
	var new_passwd = frm.new_password.value;
	var chk_passwd = frm.chk_password.value;

	if(confirmation==""){
		document.getElementById("login_footer").innerHTML = "確認コードを入力して下さい。";
		submitted = false;
		return false;
	}
	if(action=="passwd"){
		if(new_passwd==""){
			document.getElementById("login_footer").innerHTML = "パスワードを入力して下さい。";
			submitted = false;
			return false;
		} else if(new_passwd != chk_passwd){
			document.getElementById("login_footer").innerHTML = "パスワードが不一致です。";
			submitted = false;
			return false;
		} else if(new_passwd.length < 8){
			document.getElementById("login_footer").innerHTML = "パスワードは8文字以上です。";
			submitted = false;
			return false;
		}
		var encpwd = passEncode(new_passwd, passcode);
	}
	document.getElementById("login_commit").disabled = "disabled";


	var req = "mode=login";
	req += "&action=confirm";
	req += "&confirmation="+encodeURIComponent(confirmation);
	req += "&encpwd="+encodeURIComponent(encpwd);
	req += "&navid="+encodeURIComponent(navid);
	req += "&token="+encodeURIComponent(token);
	console.log(req);
	var resp = xhrSendJson(req);
	//console.log(resp);

	if((typeof resp) === 'object') {
		var results = resp.results;
		frm.token.value = resp.token;
		document.getElementById("login_footer").innerHTML = resp.comment;
		if(results == "ok") {
			//ログイン完了（画面再表示）
			setTimeout("window.location.reload(true)",500);
		} else if(results == "rt") {
			document.getElementById("login_commit").disabled = "";
			frm.confirmation.value = "";
			frm.new_password.value = "";
			frm.chk_password.value = "";
		} else {
			//画面終了
			setTimeout("login_close()",3000);
		}
	} else {
		document.getElementById("login_footer").innerHTML = "サーバに接続できません。";
	}
	//画面に戻る
	submitted = false;
	return false;
}
//パスワード変換//////////////////////////////////
function passEncode(str, passcode) {
	var len, max, n, pos, letter, out;
	len = str.length;
	n = 0;
	out = "";
	if(len > 0){
		max = Math.floor(len * 8 / 6);
		max = (max % 4 == 0)? max : max + (4 - max % 4);
		while (n < max) {
			pos = Math.floor(n * 6 / 8);
			if (pos < len) {
				var cell = str.charCodeAt(pos++) << 8;
				if (pos < len) cell += str.charCodeAt(pos) & 0xff;
				var p = (cell >> (n + 3) % 4 * 2 + 4) & 63;
				letter = passcode.charAt(p);
			} else {
				letter = '=';
			}
			n++;
			out += letter;
		}
	}
	return out;
}
//END/////////////////////////////////////////////
