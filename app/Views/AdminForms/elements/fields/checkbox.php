<?php
use Core\Framework\Components\SanitizeComponent;
?>
<label class="field-label"><?php echo !empty($v['label']) ?  SanitizeComponent::html($v['label']) : __('lblFieldLabel', true, false);?></label>
<span class="block overflow">
	<?php
	$option_arr = __('default_options', true, false);
	if($v['option_data'] != '')
	{
		$option_arr = explode("\n", str_replace("\r", "", $v['option_data']));
	} 
	$index = 1;
	foreach($option_arr as $val)
	{
		?>
		<span class="block t7">
			<input type="checkbox" name="checkbox_field[]" id="checkbox_<?php echo $index;?>" class="r3"/><label for="checkbox_<?php echo $index; ?>"><?php echo $val;?></label>
		</span>
		<?php
		$index++;
	}
	?>
</span>