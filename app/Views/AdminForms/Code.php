<?php
use Core\Framework\Components\SanitizeComponent;
?>
<textarea class="form-field textarea_install" style="overflow: auto; width: 726px; height:300px;">&lt;div id="WrapperContactForm_<?php echo $_GET['id'];?>"&gt;
	&lt;div class="container-fluid CF-container"&gt;
		&lt;img id="CF_loader_<?php echo $_GET['id'];?>" src="<?php echo BASE_URL . IMG_PATH?>frontend/loader.gif" /&gt;
		&lt;div id="CF_container_<?php echo $_GET['id'];?>" style="display:none;" class="panel-body"&gt;
			<?php
			$horizontal_form = true;
			$label_class = 'col-sm-3 control-label';
			if($tpl['arr']['label_position'] == 'top')
			{
				$horizontal_form = false;
			}else{
				if($tpl['arr']['label_position'] == 'left')
				{
					$label_class = 'col-sm-3 control-label CfLeftAlign';
				}
			} 
			?>
			&lt;form action="<?php echo BASE_URL;?>index.php?controller=Front&action=Submit" name="CF_form_<?php echo $_GET['id'];?>" id="CF_form_<?php echo $_GET['id'];?>" class="CF-form<?php echo $horizontal_form == true ? ' form-horizontal' : null; ?>" data-toggle="validator" role="form" method="post" enctype="multipart/form-data"&gt;
				&lt;input type="hidden" name="id" value="<?php echo $_GET['id'];?>" /&gt;
				<?php
				foreach($tpl['field_arr'] as $v)
				{
					if($v['type'] == 'heading')
					{
						?>
						&lt;div class="form-group"&gt;
							&lt;label class="col-sm-12 cf-heading cf-heading-<?php echo $v['heading_size'];?>"&gt;<?php echo SanitizeComponent::html($v['label']);?>&lt;/label&gt;
						&lt;/div&gt;
						<?php
					}else if($v['type'] == 'button'){
						include VIEWS_PATH . 'AdminForms/elements/install/button.php';
					}else{
						?>
						&lt;div class="form-group"&gt;
							&lt;label class="<?php echo $horizontal_form == true ? $label_class : null;?>"&gt;<?php echo SanitizeComponent::html($v['label']);?>&lt;/label&gt;
							&lt;div class="<?php echo $horizontal_form == true ? 'col-sm-9 col-xs-12' : null;?><?php echo $v['type'] == 'fileupload' ? ' CfUploadField' : null;?>"&gt;
								<?php
								include VIEWS_PATH . 'AdminForms/elements/install/'.$v['type'].'.php'; 
								?>
								&lt;div class="help-block with-errors"&gt;&lt;ul class="list-unstyled"&gt;&lt;/ul&gt;&lt;/div&gt;
							&lt;/div&gt;
						&lt;/div&gt;
						<?php
					}
				} 
				?>
			&lt;/form&gt;
			&lt;div class="form-group"&gt;
				&lt;div id="CF_message_container_<?php echo $_GET['id'];?>" class="alert" role="alert"&gt;&lt;/div&gt;
			&lt;/div&gt;
		&lt;/div&gt;
	&lt;/div&gt;
&lt;link href="<?php echo BASE_URL.FRAMEWORK_LIBS_PATH; ?>css/bootstrap.min.css" type="text/css" rel="stylesheet" /&gt;
&lt;link href="<?php echo BASE_URL; ?>index.php?controller=Front&action=LoadCss&fid=<?php echo $_GET['id'];?>" type="text/css" rel="stylesheet" /&gt;
&lt;script type="text/javascript" src="<?php echo BASE_URL; ?>index.php?controller=Front&action=LoadJs&fid=<?php echo $_GET['id'];?>"&gt;&lt;/script&gt;
&lt;/div&gt;</textarea>