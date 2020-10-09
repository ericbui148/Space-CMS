<?php
use Core\Framework\Components\SanitizeComponent;

foreach ($tpl['lp_arr'] as $v)
{
	?>
	<div class="section-box">
		<?php
		if(count($tpl['lp_arr']) > 1)
		{ 
			?>
			<label class="section-heading"><?php echo SanitizeComponent::clean($v['title']);?></label>
			<?php
		} 
		?>
		<div class="section-content">
			<?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['article_content']); ?>
		</div>
	</div>
	<?php
}
?>
