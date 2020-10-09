<?php
if($tpl['arr']['label_position'] == 'right' || $tpl['arr']['label_position'] == 'left')
{
	?>
	&lt;div class="form-group"&gt;
		&lt;div class="col-sm-offset-3 col-sm-6"&gt;
			&lt;button style="pointer-events: all; cursor: pointer;" type="submit" class="btn btn-default btn-lg CF-button"&gt;<?php echo $v['label']; ?>&lt;/button&gt;
		&lt;/div&gt;
	&lt;/div&gt;
	<?php	
}else{
	?>
	&lt;div class="form-group"&gt;
		&lt;div class="col-sm-12"&gt;
			&lt;button style="pointer-events: all; cursor: pointer;" type="submit" class="btn btn-default btn-lg CF-button"&gt;<?php echo $v['label']; ?>&lt;/button&gt;
		&lt;/div&gt;
	&lt;/div&gt;
	<?php
}
?>