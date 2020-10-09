<?php
use Core\Framework\Components\SanitizeComponent;
?>
<p>
	<label class="title"><?php __('order_email'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['client_arr']['email']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_phone'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['client_arr']['phone']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_url'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['client_arr']['url']); ?></span>
</p>