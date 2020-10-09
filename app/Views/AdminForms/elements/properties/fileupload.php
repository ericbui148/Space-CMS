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
	<label><?php __('lblMaxFileSize', false, true);?>:</label>
	<span class="block">
		<input name="max_file_size" class="form-field" value="<?php echo $tpl['field_arr']['max_file_size'] != '' ? $tpl['field_arr']['max_file_size'] : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblMaxsizeErrorMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_maxsize" class="form-field" value="<?php echo $tpl['field_arr']['error_maxsize'] != '' ? $tpl['field_arr']['error_maxsize'] : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblExtensions', false, true);?>:</label>
	<span class="block">
		<textarea name="extensions" class="form-field h100"><?php echo $tpl['field_arr']['extensions'] != '' ? SanitizeComponent::clean($tpl['field_arr']['extensions']) : NULL; ?></textarea>
	</span>
</p>
<p>
	<label><?php __('lblExtensionErrorMessage', false, true);?>:</label>
	<span class="block">
		<input name="error_extensions" class="form-field" value="<?php echo $tpl['field_arr']['error_extensions'] != '' ? SanitizeComponent::clean($tpl['field_arr']['error_extensions']) : null; ?>"/>
	</span>
</p>
<p>
	<label><?php __('lblAllowMultiple', false, true);?>:</label>
	<span class="block">
		<select name="allow_mulitple" class="form-field w150">
			<?php
			foreach(__('_yesno', true, false) as $k => $v)
			{
				?>
				<option value="<?php echo $k;?>" <?php echo $k == $tpl['field_arr']['allow_mulitple'] ? 'selected="selected"' : null;?>><?php echo $v;?></option>
				<?php
			} 
			?>
		</select>
	</span>
</p>