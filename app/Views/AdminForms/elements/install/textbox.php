<?php
use Core\Framework\Components\SanitizeComponent;
?>
&lt;input type="text" id="CF_field_<?php echo $v['id'];?>" name="CF_field_<?php echo $v['id'];?>" value="<?php echo $v['default_value'] != '' ? SanitizeComponent::clean($v['default_value']): null; ?>" <?php echo !empty($v['maxlength']) ? 'maxlength="'. $v['maxlength'] .'"' : null ;?> style="width: <?php echo $v['size']; ?>% !important;" class="form-control CF-form-field<?php echo $v['validation'] == 'url' ? null : ' CF-checked-field';?>" placeholder="<?php echo SanitizeComponent::html($v['hint']);?>" /&gt;