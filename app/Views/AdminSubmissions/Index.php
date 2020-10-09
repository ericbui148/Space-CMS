<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

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
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	UtilComponent::printNotice(__('infoSubmissionTitle', true, false), __('infoSubmissionBody', true, false)); 
	?>
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<span class="block float_right">
			<select name="form_id" class="form-field w200 float_right">
				<option value="">-- <?php __('lblAll');?> --</option>
				<?php
				foreach($tpl['form_arr'] as $form)
				{
					?>
					<option value="<?php echo $form['id'];?>"<?php echo isset($_GET['form_id']) ? ($_GET['form_id'] == $form['id'] ? ' selected="selected"' : NULL) : NULL;?>><?php echo SanitizeComponent::html($form['form_title']);?></option>
					<?php
				} 
				?>
			</select>
			<label class="block t8 float_right r10"><?php __('lblFilterByForm');?></label>
		</span>	
		<br class="clear_both" />
	</div>
	
	<div id="grid"></div>
	<?php
} 
?>
<div id="dialogView" style="display: none" title=""></div>
<script type="text/javascript">
	var Grid = Grid || {};
	Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
	Grid.queryString = "";
	<?php
	if (isset($_GET['form_id']) && (int) $_GET['form_id'] > 0)
	{
		?>Grid.queryString += "&form_id=<?php echo (int) $_GET['form_id']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.ip_address = "<?php __('lblIp');?>";
	myLabel.date_time = "<?php __('lblDateTime');?>";
	myLabel.form_name = "<?php __('lblFormName');?>";
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
</script>