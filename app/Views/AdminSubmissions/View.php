<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\AppController;

ob_start(); 
?>
<div id="CF_container_<?php echo $_GET['fid'];?>" class="CF-container CF-form">
	<?php
	$label_position = ' cfB5';
	if($tpl['arr']['label_position'] == 'right')
	{
		$label_position = ' cfFloatLeft cfAlignRight';
	}else if($tpl['arr']['label_position'] == 'left'){
		$label_position = ' cfFloatLeft';
	}
	?>
	<p>
		<label class="cf-title<?php echo $label_position;?>"><?php __('lblDateSplashTime');?>:</label>
		<span class="cfBlock cfOverflow cfT6"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['submission_arr']['submitted_date'])). ', ' . date($tpl['option_arr']['o_time_format'], strtotime($tpl['submission_arr']['submitted_date']));?></span>
	</p>
	<p>
		<label class="cf-title<?php echo $label_position;?>"><?php __('lblIp');?>:</label>
		<span class="cfBlock cfOverflow cfT6"><?php echo $tpl['submission_arr']['ip'];?></span>
	</p>
	<br/>
	<?php
	foreach($tpl['field_arr'] as $v)
	{
		if($v['type'] == 'heading')
		{
			?>
			<p>
				<label class="cf-heading cf-heading-<?php echo $v['heading_size'];?>"><?php echo SanitizeComponent::html($v['label']);?></label>
			</p>
			<?php
		}else if($v['type'] != 'button' && $v['type'] != 'captcha'){
			?>
			<p>
				<label class="cf-title<?php echo $label_position;?>"><?php echo SanitizeComponent::html($v['label']);?>:</label>
				<span class="cfBlock cfOverflow cfT6">
					<?php
					if($v['type'] == 'checkbox' || $v['type'] == 'fileupload'){
						if($v['type'] == 'fileupload'){
							if(!empty($tpl['file_arr'][$v['field_id']])){
								foreach($tpl['file_arr'][$v['field_id']] as $f){
									?><a class="CF-file-uploaded" href="<?php echo BASE_URL . 'file.php?id='.$f['id'].'&amp;hash=' .$f['hash']; ?>"><?php echo SanitizeComponent::clean($f['file_name'])?></a><?php
								}
							}	
						}else{
							echo str_replace("|", "<br/>", SanitizeComponent::html($v['value']));
						}
					}else{
						echo SanitizeComponent::html($v['value']);
					}
					?>
				</span>
			</p>
			<?php
		}
	} 
	?>
</div>
<style>
<!--
.CF-form p{
	display: block;
	line-height: 24px;
}
-->
</style>
<?php
$ob_view = ob_get_contents();
ob_end_clean();
$form_title = __('lblForm', true) . ': ' . SanitizeComponent::html($tpl['arr']['form_title']); 
AppController::jsonResponse(compact('ob_view', 'form_title'));
?>