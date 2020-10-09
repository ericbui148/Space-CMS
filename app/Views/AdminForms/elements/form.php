<?php
if(!empty($tpl['field_arr']))
{ 
	?>
	<ul id="field_list" class="field-list">
		<?php
		foreach($tpl['field_arr'] as $k => $v)
		{
			?>
			<li id="field_item_<?php echo $v['id'];?>" class="field-row<?php echo isset($tpl['field_id']) ? ($tpl['field_id'] == $v['id']) ? ' focus' : null : null;?>">
				<div class="field-elements">
					<?php
					include VIEWS_PATH . 'AdminForms/elements/fields/'.$v['type'].'.php';
					?>
				</div>
				
				<div class="field-icons">
					<a href="javascript:void(0);" class="field-edit-icon" rev="<?php echo $v['id'];?>"></a>
					<a href="javascript:void(0);" class="field-delete-icon" rev="<?php echo $v['id'];?>" rel="<?php echo $v['type'];?>"></a>
					<a href="javascript:void(0);" class="field-move-icon"></a>
				</div>
			</li>
			<?php
		} 
		?>	
	</ul>
	<?php
}else{
	__('lblFormHint', false, true);
} 
?>