<?php
use Core\Framework\Components\SanitizeComponent;
?>
<div class="loader"></div>
<fieldset class="fieldset white">
	<legend><?php __('lblInstallTheme'); ?></legend>
	<br/>
	<?php
	$theme_arr = __('option_themes', true);
	ksort($theme_arr);
	$selected_theme = $tpl['option_arr']['o_theme'];
	if(isset($_GET['theme']))
	{
		$selected_theme = 'theme' . $_GET['theme'];
	}
	foreach($theme_arr as $k => $v)
	{
		$is_used = false;
		if('theme' . $k == $selected_theme)
		{
			$is_used = true;
		}
		$img = IMG_PATH . 'backend/themes/theme' . $k . '.jpg';
		if(!is_file($img))
		{
			$img = IMG_PATH . 'backend/themes/theme.png';
		}
		?>
		<div class="theme-box-outer">
			<div class="theme-box">
				<div id="theme_image_<?php echo $k;?>" class="theme-image<?php echo $is_used ? ' active' : null;?>">
					<a target="_blank" href="preview.php?theme=<?php echo 'theme' . $k; ?>" class="preview_install">
						<img src="<?php echo $img;?>"/>
					</a>
					<span></span>
				</div>
				<label><?php echo SanitizeComponent::html($v);?></label>
				<?php
				if($is_used)
				{
					?><label class="inused"><?php __('lblCurrentlyInUse');?></label><?php
				}else{ 
					?>
					<input type="button" value="<?php __('btnUseThisTheme', false, true); ?>" class="button use-theme" data-theme="<?php echo $k;?>"/>
					<?php
				} 
				?>
			</div>
		</div>
		<?php
	} 
	?>
</fieldset>