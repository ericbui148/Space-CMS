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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSliders&amp;action=Index"><?php __('menuSliders'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSliders&amp;action=Create"><?php __('sliderAdd'); ?></a></li>
		</ul>
	</div>
	<?php
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice(@$titles['SLI02'], @$bodies['SLI02']);
	?>

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSliders&amp;action=Create" method="post" id="frmCreateSlider" class="form form" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="slider_create" value="1" />
		<p>
			<label class="title"><?php __('lblSliderName'); ?>:</label>
			<span class="inline_block">
				<input type="text" name="name" class="form-field w400 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblDescription'); ?>:</label>
			<span class="inline_block">
				<textarea name="description" class="form-field w500 h150"></textarea>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblSliderType'); ?></label>
			<span class="inline_block">
				<select name="type" id="type" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('stype_statarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
					}
					?>
				</select>
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