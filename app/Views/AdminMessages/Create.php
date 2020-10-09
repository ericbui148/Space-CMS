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
	
	UtilComponent::printNotice(__('infoAddMessageTitle', true), __('infoAddMessageBody', true), false); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Create" method="post" id="frmCreateMessage" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="message_create" value="1" />
		<p>
			<label class="title"><?php __('lblSubject'); ?></label>
			<span class="inline_block">
				<input type="text" name="subject" id="subject" class="form-field w400 required" />
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
					<textarea id="tinymce_message" name="tinymce_message" class="mceEditor required" style="width: 740px; height: 350px"></textarea>
				</span>
				
			</div>
			<div id="tabs-2">
				<span class="inline_block">
					<textarea id="plain_message" name="plain_message" class="textarea h350 w734"></textarea>
				</span>
				
			</div>
		</div>
		
		<p>
			<label class="title"><?php __('lblAttachFiles'); ?></label>
			<span class="inline_block">
				<input name="files[]" type="file" multiple="multiple"/>
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
						?><option value="<?php echo $k; ?>"<?php echo $k == 'T' ? ' selected="selected"' : null;?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button save-message" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminMessages&action=Index';" />
		</p>
	</form>
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