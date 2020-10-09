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
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Index"><?php __('lblListPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Create"><?php __('lblAddPage'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPageCategories&amp;action=Index"><?php __('menuPageCategories'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=History"><?php __('lblChanges'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(@$titles['CMSCATO03'], @$bodies['CMSCATO03']);
	?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminPageCategories" />
			<input type="hidden" name="action" value="Create" />
			<input type="submit" class="button" value="<?php __('btnAddCategory'); ?>" />
		</form>
		<br class="clear_both" />
	</div>
	
	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('stock_category_name'); ?>";
	myLabel.page = "Trang";
	myLabel.down = "<?php __('_down'); ?>";
	myLabel.up = "<?php __('_up'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	</script>
	<?php
}
?>