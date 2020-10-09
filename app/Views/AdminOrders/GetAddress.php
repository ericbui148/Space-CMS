<?php
use Core\Framework\Components\SanitizeComponent;
?>
<p>
	<label class="title"><?php __('order_country'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['country_name']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_state'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['state']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_city'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['city']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_zip'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['zip']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_name'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['name']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_address_1'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['address_1']); ?></span>
</p>
<p>
	<label class="title"><?php __('order_address_2'); ?>:</label>
	<span class="left"><?php echo SanitizeComponent::html(@$tpl['address_arr']['address_2']); ?></span>
</p>