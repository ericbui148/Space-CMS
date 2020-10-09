<?php
use Core\Framework\Components\SanitizeComponent;
?>
<input type="hidden" name="field_id" value="<?php echo $tpl['field_arr']['id'];?>" />
<p>
	<label><?php __('lblLabel', false, true);?>:</label>
	<span class="block">
		<input name="label" class="form-field" value="<?php echo $tpl['field_arr']['label'] != '' ? SanitizeComponent::clean($tpl['field_arr']['label']) : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblRequired', false, true);?>:</label>
	<span class="block">
		<select name="required" class="form-field w150 field-required">
			<?php
			foreach(__('_yesno', true, false) as $k => $v)
			{
				?>
				<option value="<?php echo $k;?>" <?php echo $k == $tpl['field_arr']['required'] ? 'selected="selected"' : null;?>><?php echo $v;?></option>
				<?php
			} 
			?>
		</select>
	</span>
</p>
<p class="required-message-container" style="display:<?php echo $tpl['field_arr']['required'] == 'T' ? 'block' : 'none';?>;">
	<label><?php __('lblRequiredErrorMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_required" class="form-field" value="<?php echo $tpl['field_arr']['error_required'] != '' ? SanitizeComponent::clean($tpl['field_arr']['error_required']) : null; ?>"/>
	</span>
</p>
<?php
$option_arr = __('default_options', true, false); 
$option_data = implode("\n", $option_arr);
?>
<p>
	<label><?php __('lblOptionData', false, true);?>:</label>
	<span class="block">
		<textarea name="option_data" class="form-field h100"><?php echo $tpl['field_arr']['option_data'] != '' ? SanitizeComponent::clean($tpl['field_arr']['option_data']) : $option_data; ?></textarea>
	</span>
</p>