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
	<label><?php __('lblRequiredErrorMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_required" class="form-field" value="<?php echo $tpl['field_arr']['error_required'] != '' ? SanitizeComponent::clean($tpl['field_arr']['error_required']) : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblCaptchaIncorrectMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_incorrect" class="form-field" value="<?php echo $tpl['field_arr']['error_incorrect'] != '' ? SanitizeComponent::clean($tpl['field_arr']['error_incorrect']) : null; ?>"/>
	</span>
</p>