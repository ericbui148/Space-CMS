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
	UtilComponent::printNotice(__('infoFormsTitle', true, false), __('infoFormsBody', true, false)); 
	?>
	<div class="b10">
		<?php
		if ($controller->isAdmin())
		{
			?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
				<input type="hidden" name="controller" value="AdminForms" />
				<input type="hidden" name="action" value="Create" />
				<input type="submit" class="button" value="+ <?php __('lblCreateForm'); ?>" />
			</form>
			<?php
		} 
		?>
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<?php
		$filter = __('filter', true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all"><?php __('lblAll'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="T"><?php echo $filter['active']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="F"><?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	<div id="dialogView" style="display: none" title=""></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;

	var myLabel = myLabel || {};
	myLabel.form_name = "<?php __('lblFormName', false, true); ?>";
	myLabel.last_submission = "<?php __('lblLastSubmission', false, true); ?>";
	myLabel.submissions = "<?php __('lblSubmissions', false, true); ?>";
	myLabel.revert_status = "<?php __('revert_status', false, true); ?>";
	myLabel.exported = "<?php __('lblExport', false, true); ?>";
	myLabel.active = "<?php __('lblActive', false, true); ?>";
	myLabel.inactive = "<?php __('lblInActive', false, true); ?>";	
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	myLabel.status = "<?php __('lblStatus', false, true); ?>";
	</script>
	<?php
}
?>