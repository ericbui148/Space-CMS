<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="inline-block">
	<input type="text" value="<?php echo $v['default_value'] != '' ? $v['default_value']: null; ?>" class="form-field w320" placeholder="<?php echo $v['hint'] != '' ? $v['hint']: null; ?>" />
</span>