<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label">&nbsp;</label>
<span class="inline-block">
	<input type="button" value="<?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('btnSubmit', true, false); ?>" class="button" />
</span>