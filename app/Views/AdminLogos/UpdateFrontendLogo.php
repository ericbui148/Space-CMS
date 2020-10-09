
<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=AdminLogos&amp;action=UpdateFrontendLogo" id="frmLogo" method="post" class="form form" enctype="multipart/form-data">
	<input type="hidden" name="logo_post" value="1" />
	<p>
		<label class="title"><?php __('lblLogoName'); ?></label>
		<span class="left" id="box_logo">
			<?php
			if (!empty($tpl['arr']['origin_path']))
			{
				?><img src="<?php echo $tpl['arr']['origin_path']; ?>" alt="" class="align_middle" />
				<input type="button" class="button delete_logo" value="<?php __('lblDelete'); ?>" /><?php
			} else {
				?><input type="file" name="logo" id="y_logo" class="form-field w350"/><?php
			}
			?>
		</span>
	</p>
	<p>
		<input type="submit" value="<?php __('btnSave'); ?>" class="button float_left align_middle" />
	</p>
</form>
<div id="dialogDeleteLogo"></div>
	<script type="text/javascript">
var myLabel = myLabel || {};
myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
myLabel.btn_delete = "<?php __('lblDelete'); ?>";
myLabel.delete_image_confirm = "<?php __('lblDeleteImageConfirm'); ?>";
</script>