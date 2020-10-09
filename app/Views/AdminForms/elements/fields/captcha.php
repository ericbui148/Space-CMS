<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="inline-block">
	<input type="text" class="form-field w80 float_left r3" /><img class="captcha" src="<?php echo IMG_PATH ?>backend/<?php echo $tpl['arr']['captcha_type'] == 'string' ? 'captcha' : 'math_captcha';?>.png" />
</span>