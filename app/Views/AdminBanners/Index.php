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
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
	}
	?>
	<?php
	UtilComponent::printNotice("Danh sách banner", "Dưới đây là danh sách tất cả banner"); 
	?>
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.name = "<?php __('lblFileName'); ?>";
		myLabel.uploaded_on = "<?php __('lblUploadedOn'); ?>";
		myLabel.uploaded_by = "<?php __('lblUploadedBy'); ?>";
		myLabel.size = "<?php __('lblSize'); ?>";
		myLabel.exported = "<?php __('lblExport'); ?>";
		myLabel.delete_selected = "<?php __('delete_selected'); ?>";
		myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
	</script>
	<?php
}
?>