<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="inline-block">
	<input type="file" class="form-field w320" />
</span>