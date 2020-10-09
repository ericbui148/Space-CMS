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