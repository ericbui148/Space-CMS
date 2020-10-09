<?php
use Core\Framework\Components\SanitizeComponent;
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $tpl['arr']['form_title'];?></title>
	<head>
	<body>
		<div id="CF_container_<?php echo $_GET['fid'];?>" class="CF-container CF-form">
			<?php
			$label_position = ' cfB5';
			if($tpl['arr']['label_position'] == 'right')
			{
				$label_position = ' cfFloatLeft cfAlignRight';
			}else if($tpl['arr']['label_position'] == 'left'){
				$label_position = ' cfFloatLeft';
			}
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
											?><a class="CF-file-uploaded" href="<?php echo BASE_URL . $f['file_path']?>"><?php echo SanitizeComponent::clean($f['file_name'])?></a><?php
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
		<link href="<?php echo BASE_URL; ?>/index.php?controller=Front&action=LoadCss&fid=<?php echo $tpl['arr']['id'];?>" type="text/css" rel="stylesheet" />
	</body>
</html>