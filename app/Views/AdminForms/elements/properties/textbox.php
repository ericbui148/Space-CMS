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
<p>
	<label><?php __('lblDefaultValue', false, true);?>:</label>
	<span class="block">
		<input name="default_value" class="form-field" value="<?php echo $tpl['field_arr']['default_value'] != '' ? SanitizeComponent::clean($tpl['field_arr']['default_value']) : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblHintText', false, true);?>:</label>
	<span class="block">
		<input name="hint" class="form-field" value="<?php echo $tpl['field_arr']['hint'] != '' ? SanitizeComponent::clean($tpl['field_arr']['hint']) : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblSize', false, true);?>(%):</label>
	<span class="block">
		<input name="size" class="form-field" value="<?php echo $tpl['field_arr']['size'] != '' ? $tpl['field_arr']['size'] : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblMaxLength', false, true);?>:</label>
	<span class="block">
		<input name="maxlength" class="form-field" value="<?php echo $tpl['field_arr']['maxlength'] != '' ? $tpl['field_arr']['maxlength'] : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblValidation', false, true);?>:</label>
	<span class="block">
		<select name="validation" class="form-field w150">
			<?php
			foreach(__('validations', true, false) as $k => $v)
			{
				?>
				<option value="<?php echo $k;?>" <?php echo $k == $tpl['field_arr']['validation'] ? 'selected="selected"' : null;?>><?php echo $v;?></option>
				<?php
			} 
			?>
		</select>
	</span>
</p>
<p>
	<label><?php __('lblValidationErrorMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_validation" class="form-field" value="<?php echo $tpl['field_arr']['error_validation'] != '' ? SanitizeComponent::clean($tpl['field_arr']['error_validation']) : null; ?>"/>
	</span>
</p>