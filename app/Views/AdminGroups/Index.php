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
	UtilComponent::printNotice(__('infoGroupTitle', true), __('infoGroupBody', true));
	$subscribed_arr = __('subscribed_arr', true);
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminGroups" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="<?php __('btnCreateList'); ?>" />
		</form>
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
		<?php
		$filter = __('filter', true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all"><?php __('lblAll');?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="T"><?php echo $filter['active']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="F"><?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	
	<script type="text/javascript">
		var Grid = Grid || {};
		Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
		var myLabel = myLabel || {};
		myLabel.group = "<?php __('lblGroup'); ?>";
		myLabel.subscribers = "<?php __('lblSubscribers'); ?>";
		myLabel.total = "<?php __('lblTotal'); ?>";
		myLabel.subscribed = "<?php echo $subscribed_arr['T']; ?>";
		myLabel.unsubscribed = "<?php echo $subscribed_arr['F']; ?>";
		myLabel.active = "<?php __('lblActive'); ?>";
		myLabel.inactive = "<?php __('lblInactive'); ?>";
		myLabel.revert_status = "<?php __('revert_status'); ?>";
		myLabel.exported = "<?php __('lblExport'); ?>";
		myLabel.delete_selected = "<?php __('delete_selected'); ?>";
		myLabel.delete_confirmation = "<?php __('gridActionList'); ?>";
		myLabel.status = "<?php __('lblStatus'); ?>";
		myLabel.same_group = "<?php __('same_group'); ?>";
	</script>
	<?php
}
?>