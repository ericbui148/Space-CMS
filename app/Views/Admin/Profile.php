<?php
use Core\Framework\Components\SanitizeComponent;
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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Profile"><?php __('menuProfile'); ?></a></li>
		</ul>
	</div>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Profile" method="post" id="frmUpdateProfile" class="form form" enctype="multipart/form-data">
		<input type="hidden" name="profile_update" value="1" />
		<p>
			<label class="title">Avatar</label>
			<span class="inline_block">
					<?php
					if (!empty($tpl['arr']['avatar']) && is_file($tpl['arr']['avatar']))
					{
						?><img width="120px" height="120px" src="<?php echo $tpl['arr']['avatar']; ?>" alt="" class="align_middle" />
						<input type="button" class="button delete-avatar" value="<?php __('lblDelete'); ?>" /><?php
					} else {
						?><input type="file" name="avatar" id="avatar" class="form-field w350"/><?php
					}
					?>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblName'); ?></label>
			<span class="inline_block">
				<input type="text" name="name" id="name" value="<?php echo SanitizeComponent::html($tpl['arr']['name']); ?>" class="form-field w250 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('email'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
				<input type="text" name="email" id="email" class="form-field required email w200" value="<?php echo SanitizeComponent::html($tpl['arr']['email']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPhone'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
				<input type="text" name="phone" id="phone" class="form-field required w200" value="<?php echo SanitizeComponent::html($tpl['arr']['phone']); ?>" autocomplete="off" />
			</span>
		</p>		
		<p>
			<label class="title"><?php __('pass'); ?>:</label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
				<input type="text" name="password" id="password" class="form-field required w200" value="<?php echo SanitizeComponent::html($tpl['arr']['password']); ?>" autocomplete="off" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
		</p>
	</form>
	<?php
}
?>
	<div id="dialogDeleteAvatar"></div>
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
	myLabel.btn_delete = "<?php __('lblDelete'); ?>";
	myLabel.delete_image_confirm = "<?php __('lblDeleteImageConfirm'); ?>";
	</script>