<div id="editor">
	<div class="inner">
		<div id="edit_close" onclick="edit_close()">×</div>
		<div id="edit_header">
			<ul id="edit_motion">
			<li onclick="edit_view(1)"><img src="./img/icon/document_edit.png" title="変更を保存" /></li>
			<li onclick="edit_view(2)"><img src="./img/icon/document_add.png" title="この記事を後に追加" /></li>
			<li onclick="edit_view(3)"><img src="./img/icon/document_delete.png" title="この記事を削除" /></li>
			</ul>
			<ul id="edit_layout">
			<li onclick="edit_add_tag(0)"><img src="./img/icon/img_box.png" title="画像を追加" /></li>
			<li onclick="edit_img_circle()"><img src="./img/icon/img_circle.png" title="丸く表示" /></li>
			<li onclick="edit_img_change()"><img src="./img/icon/img_change.png" title="画像切替え" /></li>
			<li onclick="edit_img_size()"><img src="./img/icon/img_size.png" title="サイズ変更" /></li>
			<li onclick="edit_img_view()"><img src="./img/icon/img_view.png" title="写真を拡大ビュー" /></li>
			</ul>
		</div>
		<div id="edit_conte">
			<div id="edit_sidebar">
				<ul>
				<li onclick="edit_restore()"><img src="./img/icon/back2.png" title="戻す" /></li>
				</ul>
				<ul>
				<li onclick="edit_range(0)"><img src="./img/icon/tag_br.png" title="改行" /></li>
				<li onclick="edit_range(1)"><img src="./img/icon/tag_div.png" title="ブロック" /></li>
				<li onclick="edit_range(2)"><img src="./img/icon/tag_em.png" title="強調" /></li>
				<li onclick="edit_range(3)"><img src="./img/icon/tag_span.png" title="色指定" /></li>
				<li onclick="edit_range(4)"><img src="./img/icon/tag_mk.png" title="マーク" /></li>
				<li onclick="edit_range(5)"><img src="./img/icon/tag_del.png" title="取消" /></li>
				<li onclick="edit_bg_color(0)"><img src="./img/icon/bg_memo.png" title="背景1" /></li>
				<li onclick="edit_bg_color(1)"><img src="./img/icon/bg_note.png" title="背景2" /></li>
				<li onclick="edit_bg_color(2)"><img src="./img/icon/bg_win10.png" title="背景3" /></li>
				<li onclick="edit_bg_color(3)"><img src="./img/icon/bg_linux.png" title="背景4" /></li>
				<li onclick="edit_color(0)"><img src="./img/icon/col_red.png" title="赤" /></li>
				<li onclick="edit_color(1)"><img src="./img/icon/col_green.png" title="緑" /></li>
				<li onclick="edit_color(2)"><img src="./img/icon/col_blue.png" title="青" /></li>
				<li onclick="edit_color(3)"><img src="./img/icon/col_yellow.png" title="黄" /></li>
				<li onclick="edit_color(4)"><img src="./img/icon/col_cyan.png" title="空色" /></li>
				<li onclick="edit_color(5)"><img src="./img/icon/col_orange.png" title="橙" /></li>
				</ul>
			</div>
			<div class="edit_panel">
				<form enctype="multipart/form-data" accept-charset="UTF-8" name="edit_form" id="form-edit" method="post">
					<input type="hidden" name="mode" value="" />
					<input type="hidden" name="action" value="" />
					<input type="hidden" name="navid" value="" />
					<input type="hidden" name="token" value="" />
					<input type="hidden" name="sectid" value="" />
					<input type="hidden" name="pagenm" value="" />
					<input type="hidden" name="edtype" value="" />
					<input type="hidden" name="motion" value="" />
					<input type="hidden" name="images" value="" />
					<input type="hidden" name="clsname" value="" />
					<div id="edit_area">
						<textarea name="edit_textarea" id="edit_textarea" spellcheck="false"></textarea>
					</div>
				</form>
			</div>
		</div>
		<div id="edit_footer"></div>
	</div>
</div>

<div id="edit_view" class="display">
	<div class="inner">
		<div id="edit_view_close" class="display_close" onclick="edit_close()">×</div>
		<div id="edit_view_header" class="display_header"></div>
		<div id="edit_view_conte" class="display_conte">
		</div>
		<div class="div_commit">
			<div>
			<input type="checkbox" name="edit_check" id="edit_check" value="1" />
			<label for="edit_check" id="edit_check_msg">記事を登録</label>
			</div>
			<div>
			<input type="checkbox" name="edit_backup" id="edit_backup" value="1" />
			<label for="edit_backup">履歴を保存</label>
			</div>
		</div>
		<div class="div_commit">
			<input type="button" class="bt_commit" id="edit_save_action" value="送信" onclick="edit_save()">
			<input type="button" class="bt_cancel" value="戻る" onclick="edit_back()">
		</div>
		<div id="edit_view_footer" class="display_footer"></div>
	</div>
</div>

