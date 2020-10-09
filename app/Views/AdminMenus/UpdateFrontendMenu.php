<?php
use App\Controllers\Components\UtilComponent;
?>
<div style="width: 750px;">
	<p>
	<br/>
	<label class="title">&nbsp;</label>
	<button class="button btnCreateMenuItem"><i class="fa fa-plus-circle" aria-hidden="true"></i><?php __('btnMenuItemCreate', false, true); ?></button>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Update" method="post" id="frmUpdateMenu" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="menu_update" value="1" />
		<input type="hidden" name="id" value="<?php echo @$tpl['arr']['id']; ?>" />
		<input type="hidden" name="menu_id" value="<?php echo @$tpl['arr']['id']; ?>" />
	</form>
	<div id="dialogMenuItemAdd"></div>
	<div id="dialogMenuItemDelete"></div>
	</p>
	<br/>
	<div id="grid_menu_item"></div>			

	<div id="dialogMenuItemEdit"></div>
	<div id="dialogMenuItemDelete"></div>
</div>
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