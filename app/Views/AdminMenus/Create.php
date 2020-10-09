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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Index"><?php __('menuSectionMenu'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Create"><?php __('menuAddMenu'); ?></a></li>
		</ul>
	</div>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice(@$titles['MENU02'], @$bodies['MENU02']);
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Create" method="post" id="frmCreateMenu" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="menu_create" value="1" />
		<input type="hidden" name="hash" id="hash" value="<?php echo $tpl['hash'];?>" />
		<p>
			<label class="title"><?php __('lblMenuName'); ?>:</label>
			<span class="inline_block">
				<input type="text" name="name" class="form-field w400 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblDescription'); ?>:</label>
			<span class="inline_block">
				<textarea name="description" class="form-field w500 h50"></textarea>
			</span>
		</p>		
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="status" id="status" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('u_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>		
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
		</p>
		</form>
			
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.checkAllText = "<?php __('multiselect_check_all', false, true); ?>";
	myLabel.uncheckAllText = "<?php __('multiselect_uncheck_all', false, true); ?>";
	myLabel.noneSelectedText = "<?php __('multiselect_none_selected', false, true); ?>";
	myLabel.selectedText = "<?php __('multiselect_selected', false, true); ?>";
	myLabel.positiveNumber = "<?php __('positive_number', false, true); ?>";	
	
	</script>
	<?php
}
?>