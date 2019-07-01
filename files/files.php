<div id="file_editor">
	<div class="inner">
		<div id="file_close" onclick="file_close()">×</div>
		<div id="file_header">
			<ul>
				<li onclick="file_upload_panel()"><img src="./img/icon/file_add.png" title="画像をアップロード" /></li>
				<li onclick="file_list_panel()"><img src="./img/icon/file_delete.png" title="画像を削除"  /></li>
				<li onclick="file_restore_panel()"><img src="./img/icon/document_restore.png" title="履歴からページを復活" /></li>
			</ul>
		</div>
		<div id="file_conte">
			<form enctype="multipart/form-data" accept-charset="UTF-8" name="file_edit_form" id="form-file_edit" method="post">
				<input type="hidden" name="mode" value="" />
				<input type="hidden" name="action" value="" />
				<input type="hidden" name="navid" value="" />
				<input type="hidden" name="token" value="" />
				<input type="hidden" name="sectid" value="" />
				<input type="hidden" name="pagenm" value="" />
				<input type="hidden" name="filenm" value="" />
				<input type='hidden' name='MAX_FILE_SIZE' value='2000000'>
				<div id="file_area">
					<div id="file_cntl_01" class="file_panel">
						<input type='file' name='upload' accept='image/png, image/jpeg'>
						<div class="file_form">
							<h3>保存ファイル名</h3>
							<div class="next">
								<input type="text" class="" name="file_name" id="file_name" value="" size="25em" maxlength="40">
							</div>
						</div>
						<div class="file_commit">
							<input type="button" class="bt_commit" id="file_upload_action" value="送信する" onclick="file_upload();">
						</div>
					</div>
					<div id="file_cntl_02" class="file_list_panel">
						<div class="file_form">
							<div id="file_div_prev" class="prev">
							</div>
							<div id="file_div_next" class="next">
							</div>
						</div>
						<div class="file_commit">
							<input type="button" class="bt_commit" id="file_delete_action" value="削除する" onclick="file_delete();">
						</div>
					</div>
					<div id="file_cntl_03" class="file_restore_panel">
						<div class="file_form">
							<div id="file_history" class="file_history">
							<span>履歴がありません。</span>
							</div>
						</div>
						<div class="file_commit">
							<input type="button" class="bt_commit" id="file_restore_action" value="復活する" onclick="file_restore();">
						</div>
					</div>
				</div>
			</form>
		</div>
		<div id="file_footer"></div>
	</div>
</div>

