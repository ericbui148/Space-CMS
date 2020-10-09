<?php
use Core\Framework\Components\SanitizeComponent;
?>
&lt;textarea name="CF_field_<?php echo $v['id'];?>" style="width:<?php echo $v['columns'];?>% !important;height:<?php echo $v['rows'];?>px !important;" <?php echo !empty($v['maxlength']) ? 'maxlength="'. $v['maxlength'] .'"' : null ;?> class="form-control CF-form-field CF-checked-field"&gt;<?php echo $v['default_value'] != '' ? SanitizeComponent::clean($v['default_value']): null; ?>&lt;/textarea&gt;