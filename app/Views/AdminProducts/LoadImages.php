<?php
use Core\Framework\Components\SanitizeComponent;

if (isset($tpl['arr']) && is_array($tpl['arr']))
{
	if (count($tpl['arr']) > 0)
	{
		foreach ($tpl['arr'] as $k => $item)
		{
			?>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="stock-image" rel="<?php echo $item['id']; ?>"><img src="<?php echo BASE_URL . (!empty($item['small_path']) ? $item['small_path'] : IMG_PATH . 'no_image.png'); ?>?<?php echo rand(1, 9999999); ?>" alt="<?php echo SanitizeComponent::html($item['alt']); ?>" class="<?php echo isset($_GET['image_id']) && $_GET['image_id'] == $item['id'] ? 'current' : NULL; ?>" /></a>
			<?php
		}
	}else{
		__('lblNoImageUploaded');
	}
}
?>