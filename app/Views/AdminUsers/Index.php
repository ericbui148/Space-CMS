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
	$u_statarr = __('u_statarr', true);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Index"><?php __('menuUsers'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Create"><?php __('lblAddUser'); ?></a></li>
		</ul>
	</div>
	
	<div class="b10">
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
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.jsDateFormat = "<?php echo UtilComponent::jsDateFormat($tpl['option_arr']['o_date_format']); ?>";
	Grid.currentUserId = <?php echo (int) $_SESSION[$controller->defaultUser]['id']; ?>;
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('lblName', false, true); ?>";
	myLabel.email = "<?php __('email', false, true); ?>";
	myLabel.created = "<?php __('lblUserCreated', false, true); ?>";
	myLabel.role = "<?php __('lblRole', false, true); ?>";
	myLabel.confirmed = "<?php __('lblIsActive', false, true); ?>";
	myLabel.revert_status = "<?php __('revert_status', false, true); ?>";
	myLabel.exported = "<?php __('lblExport', false, true); ?>";
	myLabel.active = "<?php echo $u_statarr['T']; ?>";
	myLabel.inactive = "<?php echo $u_statarr['F']; ?>";	
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	myLabel.status = "<?php __('lblStatus', false, true); ?>";
	</script>
	<?php
}
?>