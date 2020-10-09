<?php
use App\Controllers\Components\UtilComponent;
use App\Models\BannerModel;

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
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
		UtilComponent::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Index"><?php __('lblFiles'); ?></a></li>
			<?php
			if ($controller->isAdmin() || ($controller->isEditor() && $controller->isFileAllowed()))
			{ 
				?>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Create"><?php __('lblUploadFile'); ?></a></li>
				<?php
			} 
			?>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Update&amp;id=<?php echo $tpl['arr']['id'];?>"><?php __('lblUpdateFile'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoUpdateFileTitle', true), __('infoUpdateFileDesc', true)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Update" method="post" id="frmUpdateFile" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="file_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id'];?>" />
		<p>
			<label class="title"><?php __('lblCurrentFile'); ?></label>
			<span class="inline_block">
				<label class="content"><a href="<?php echo $controller->baseUrl() . 'file.php?id='.$tpl['arr']['id'].'&amp;hash=' .$tpl['arr']['hash']; ?>" target="_blank"><?php echo $tpl['arr']['file_name'];?></a></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblURL'); ?></label>
			<span class="inline_block">
				<textarea id="file_path" name="path" class="form-field h60 w550" readonly="readonly"><?php echo $controller->baseUrl() . 'file.php?id='.$tpl['arr']['id'].'&amp;hash=' .$tpl['arr']['hash']; ?></textarea>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblFile'); ?></label>
			<span class="inline_block">
				<input name="file" type="file" class="form-field w350"/>
			</span>
		</p>
		<?php
		if($controller->isAdmin())
		{
			?>
			<p>
				<label class="title"><?php __('lblUsers'); ?></label>
				<span class="inline_block">
					<select name="user_id[]" id="user_id" class="form-field" multiple="multiple" size="5">
						<?php
						foreach ($tpl['user_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"<?php echo in_array($v['id'], $tpl['arr']['user_ids']) ? ' selected="selected"' : null;?>><?php echo stripslashes($v['name']); ?></option><?php
						}
						?>
					</select>
				</span>
			</p>
			<?php
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