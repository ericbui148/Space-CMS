<?php
use App\Controllers\Components\UtilComponent;
?>
<fieldset class="overflow b10 t5">
	<legend><?php __('lblAddNewField', false, true); ?></legend>
	<div class="toolbox-text b10"><?php __('lblToolboxText', false, true);?></div>
	<ul class="element-list">
		<?php
		$field_titles = __('field_titles', true);
		foreach(UtilComponent::getFields() as $k => $v)
		{
			$display = 'block';
			if($k == 'captcha' && $tpl['cnt_captcha'] > 0){
				$display = 'none';
			}
			?>
			<li style="display:<?php echo $display;?>;"><a href="javascript:void(0);" class="<?php echo $k?>-icon element-item" rev="<?php echo $k; ?>"><?php echo $field_titles[$k];?></a></li>
			<?php	
		} 
		?>
	</ul>
</fieldset>