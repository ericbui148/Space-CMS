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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Send"><?php __('lblSend'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminQueues&amp;action=Index"><?php __('lblMailQueue'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoMailQueueTitle', true), __('infoMailQueueBody', true)); 
	?>
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
		</form>
		<?php
		$queue_arr = __('queue_arr', true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all">All</a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="inprogress"><?php echo $queue_arr['inprogress']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="status" data-value="completed"><?php echo $queue_arr['completed']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	
	<script type="text/javascript">
		var Grid = Grid || {};
		Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
		Grid.queryString = "";
		<?php
		if (isset($_GET['subscriber_id']) && (int) $_GET['subscriber_id'] > 0)
		{
			?>Grid.queryString += "&subscriber_id=<?php echo (int) $_GET['subscriber_id']; ?>";<?php
		}
		?>
		var myLabel = myLabel || {};
		myLabel.message = "<?php __('lblMessage'); ?>";
		myLabel.email = "<?php __('lblEmail'); ?>";
		myLabel.date_sent = "<?php __('lblDateSent'); ?>";
		myLabel.inprogress = "<?php __('lblInProgress'); ?>";
		myLabel.completed = "<?php __('lblCompleted'); ?>";
		myLabel.revert_status = "<?php __('revert_status'); ?>";
		myLabel.exported = "<?php __('lblExport'); ?>";
		myLabel.delete_selected = "<?php __('delete_selected'); ?>";
		myLabel.delete_confirmation = "<?php __('gridActionQueue'); ?>";
		myLabel.status = "<?php __('lblStatus'); ?>";
	</script>
	<?php
}
?>