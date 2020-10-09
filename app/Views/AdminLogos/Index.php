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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']],  @$bodies[$_GET['err']]);
	}
	
	?>
	<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=AdminLogos&amp;action=Index" id="frmLogo" method="post" class="form form" enctype="multipart/form-data">
		<input type="hidden" name="logo_post" value="1" />
			<?php
			UtilComponent::printNotice($titles['LOGO001'], $bodies['LOGO001'], false);
			?>
			<p>
				<label class="title"><?php __('lblLogoName'); ?></label>
				<span class="left" id="box_logo">
					<?php
					if (!empty($tpl['arr']['small_path']))
					{
						?><img src="<?php echo $tpl['arr']['small_path']; ?>" alt="" class="align_middle" />
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
<?php }?>