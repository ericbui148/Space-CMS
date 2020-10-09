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
			<li class="ui-state-default ui-corner-top ui-tabs-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Create"><?php __('menuAddMenu'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Update&id=<?php echo $_GET['id'];?>"><?php __('lblMenuUpdate'); ?></a></li>
		</ul>
	</div>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if(@$controller->option_arr ['o_design_theme'] == 'pro') {
		UtilComponent::printNotice(@$titles['MENU05'], @$bodies['MENU05']);
	}
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Update" method="post" id="frmUpdateMenu" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="menu_update" value="1" />
		<input type="hidden" name="id" value="<?php echo @$tpl['arr']['id']; ?>" />
		<input type="hidden" name="menu_id" value="<?php echo @$tpl['arr']['id']; ?>" />
			<p>
				<label class="title"><?php __('lblMenuName')?>:</label>
				<span class="inline_block">
					<input type="text" name="name" class="form-field w400 required"  value="<?php echo @$tpl['arr']['name']; ?>"/>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblDescription'); ?>:</label>
				<span class="inline_block">
					<textarea name="description" class="form-field w500 h50"><?php echo @$tpl['arr']['description']; ?></textarea>
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
							if(@$tpl['arr']['status'] == $k) {
								?><option value="<?php echo $k; ?>" selected="selected"><?php echo $v; ?></option><?php
							} else {
								?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
							}
							
						}
						?>
					</select>
				</span>
			</p>
				
			
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
			</p>
			<?php UtilComponent::printNotice(@$titles['MENU03'], @$bodies['MENU03']);?>
	</form>
	<div id="dialogMenuItemAdd"></div>
	<div id="dialogMenuItemDelete"></div>
	<p>
		<label class="title">&nbsp;</label>
		<input type="submit" value="<?php __('btnMenuItemCreate', false, true); ?>" class="button btnCreateMenuItem" />
	</p>
	<br/>
	<div id="grid_menu_item"></div>			
	
	<div id="dialogMenuItemEdit"></div>
	<div id="dialogMenuItemDelete"></div>
	<script type="text/javascript">
	var Grid = Grid || {};
	var myLabel = myLabel || {};
	var myGallery = myGallery || {};
	myLabel.name = "<?php __('lblMenuItemName'); ?>";
	myLabel.link = "<?php __('lblMenuLink'); ?>";
	
	myGallery.foreign_id = "<?php echo $tpl['arr']['id']; ?>";
	myGallery.hash = "";
	myLabel.checkAllText = "<?php __('multiselect_check_all', false, true); ?>";
	myLabel.uncheckAllText = "<?php __('multiselect_uncheck_all', false, true); ?>";
	myLabel.noneSelectedText = "<?php __('multiselect_none_selected', false, true); ?>";
	myLabel.selectedText = "<?php __('multiselect_selected', false, true); ?>";
	myLabel.positiveNumber = "<?php __('positive_number', false, true); ?>";
	myLabel.down = "<?php __('_down'); ?>";
	myLabel.up = "<?php __('_up'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('gridDeleteConfirmation'); ?>";
	myLabel.status = "<?php __('status'); ?>";
	myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
	myLabel.btn_delete = "<?php __('lblDelete'); ?>";
	myLabel.delete_image_confirm = "<?php __('lblDeleteImageConfirm'); ?>";
	</script>
	<?php
}
?>