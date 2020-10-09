<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	UtilComponent::printNotice(__('infoUpdateMessageTitle', true), __('infoUpdateMessageBody', true) . '<br/><br/>' . __('lblMessageTokens', true), false); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Update" method="post" id="frmUpdateMessage" class="form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="message_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<input type="hidden" name="send" value="0" />
		<p>
			<label class="title"><?php __('lblSubject'); ?></label>
			<span class="inline_block">
				<input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['subject'])); ?>" class="form-field w400 required" />
			</span>
		</p>
		<br/>
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblHTMLMessage'); ?></a></li>
				<li><a href="#tabs-2"><?php __('lblPlainMessage'); ?></a></li>
			</ul>
			<div id="tabs-1">
				
				<span class="inline_block">
					<textarea id="tinymce_message" name="tinymce_message" class="mceEditor required" style="width: 740px; height: 350px"><?php echo htmlspecialchars(stripslashes($tpl['arr']['tinymce_message'])); ?></textarea>
				</span>
				
			</div>
			<div id="tabs-2">
				
				<span class="inline_block">
					<textarea id="plain_message" name="plain_message" class="textarea h350 w734"><?php echo htmlspecialchars(stripslashes($tpl['arr']['plain_message'])); ?></textarea>
				</span>
				
			</div>
		</div>
		<p>
			<label class="title"><?php __('lblAttachFiles'); ?></label>
			<span class="block float_left">
				<input name="files[]" type="file" multiple="multiple" class="block"/>
				<?php
				if(!empty($tpl['file_arr']))
				{
					foreach($tpl['file_arr'] as $f)
					{
					?>
					<span class="file-container">
						<label class="block float_left r10"><?php echo $f['file_name'];?></label>
						<a class="download-file" target="_blank" href="<?php echo BASE_URL;?>index.php?controller=AdminMessages&action=DownloadFile&id=<?php echo $f['id']; ?>"><?php __('lblDownload');?></a>
						<a class="delete-file" href="javascript:void(0);" rev="<?php echo $f['id'];?>"><?php echo  strtolower(__('lblDelete', true));?></a>
					</span>
					<?php
					}
				} 
				?>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('u_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['status'] ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnSaveAndSend'); ?>" class="button save-and-send" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminMessages&action=Index';" />
		</p>
	</form>
	<div id="dialogDeleteFile" title="<?php __('lblDeleteFileTitle'); ?>" style="display:none">
		<p><?php __('lblDeleteFileBody'); ?></p>
	</div>
	
	<script type="text/javascript">
		var Grid = Grid || {};
		Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
		var myLabel = myLabel || {};
		myLabel.allowed_ext = "<?php __('lblAllowedExt'); ?>";
		myLabel.BASE_URL = "<?php echo BASE_URL; ?>";
	</script>
	<?php
}
?>