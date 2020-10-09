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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Index"><?php __('lblFiles'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Create"><?php __('lblUploadFile'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoUploadFileTitle', true), __('infoUploadFileDesc', true)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Create" method="post" id="frmCreateFile" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="file_create" value="1" />
		<p>
			<label class="title"><?php __('lblFile'); ?></label>
			<span class="inline_block">
				<input name="file" type="file" class="form-field required w350"/>
			</span>
		</p>
		<?php
		if($controller->isAdmin())
		{
			if(!empty($tpl['user_arr']))
			{
				?>
				<p>
					<label class="title"><?php __('lblUsers'); ?></label>
					<span class="inline_block">
						<select name="user_id[]" id="user_id" class="form-field" multiple="multiple" size="5">
							<?php
							foreach ($tpl['user_arr'] as $v)
							{
								?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['name']); ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<?php
			}else{
				?>
				<p>
					<label class="title"><?php __('lblUsers'); ?></label>
					<span class="inline_block">
						<label class="content"><?php __('lblNoAvailableUsers'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Create"><?php __('lblHere'); ?></a>.</label>
					</span>
				</p>
				<?php
			}
		} 
		?>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
		</p>
	</form>
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.select_users = "<?php __('lblSelectUsers');?>";
		myLabel.field_required = "<?php __('lblFieldRequired'); ?>";
		myLabel.extension_message = "<?php __('lblExtensionMessage');?>";
		myLabel.allowed_extension = "<?php echo $tpl['option_arr']['o_extension_allow']; ?>";
	</script>
	<?php
}
?>