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
	
	UtilComponent::printNotice(__('infoAddGroupTitle', true), __('infoAddGroupBody', true)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminGroups&amp;action=Create" method="post" id="frmCreateGroup" class="form" autocomplete="off">
		<input type="hidden" name="group_create" value="1" />
		<p>
			<label class="title"><?php __('lblGroup'); ?></label>
			<span class="inline_block">
				<input type="text" name="group_title" id="group_title" class="form-field w300 required" />
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
						?><option value="<?php echo $k; ?>"<?php echo $k == 'T' ? ' selected="selected"' : null;?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminGroups&action=Index';" />
		</p>
	</form>
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.same_group = "<?php echo __('same_group', true); ?>";
	</script>
	<?php
}
?>