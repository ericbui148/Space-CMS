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
	UtilComponent::printNotice(__('infoAddFormTitle', true, false), __('infoAddFormBody', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminForms&amp;action=Create" method="post" id="frmCreateForm" class="form -form" autocomplete="off">
		<input type="hidden" name="form_create" value="1" />
		
		<p>
			<label class="title"><?php __('lblFormName'); ?></label>
			<span class="inline_block">
				<input type="text" name="form_title" id="form_title" class="form-field w500 required" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminForms&action=Index';" />
		</p>
	</form>
	<?php
}
?>