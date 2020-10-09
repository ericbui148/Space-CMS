<?php
use Core\Framework\Components\SanitizeComponent;
?>

<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="inline-block">
	<textarea class="form-field w320 h100"><?php echo $v['default_value'] != '' ? $v['default_value']: null; ?></textarea>
</span>