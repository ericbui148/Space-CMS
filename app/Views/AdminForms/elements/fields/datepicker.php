<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="inline-block">
	<span class="form-field-custom form-field-custom-after">
		<input type="text" class="form-field pointer w80 datepick" readonly="readonly" />
		<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
	</span>
</span>