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
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Index"><?php __('menuSectionMenu'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Create"><?php __('menuAddMenu'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(@$titles['MENU01'], @$bodies['MENU01']);
	?>
	<div class="b10">
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch', false, true); ?>" />
		</form>
		<?php
		$filter = __('filter', true, true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all"><?php __('lblAll'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="is_active" data-value="1"><?php echo $filter['active']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="is_active" data-value="0"><?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>

	<div id="grid"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('lblMenuName', false, true); ?>";
	myLabel.created = "<?php __('lblMenuCreated'); ?>";
	myLabel.modified = "<?php __('lblMenuModified'); ?>";
	myLabel.status = "<?php __('lblMenuStatus', false, true); ?>";
	myLabel.active = "<?php echo $filter['active']; ?>";
	myLabel.inactive = "<?php echo $filter['inactive']; ?>";
	myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	</script>
	<?php
}
?>