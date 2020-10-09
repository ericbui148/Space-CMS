<?php
use Core\Framework\Components\SanitizeComponent;

switch ($v['heading_size']) {
	case 'medium':
		?><h2><?php echo $v['label'] != '' ? SanitizeComponent::html($v['label']) : __('lblMediumHeading', true, false);?></h2><?php 
	break;
	case 'large':
		?><h1><?php echo $v['label'] != '' ? SanitizeComponent::html($v['label']) : __('lblLargeHeading', true, false);?></h1><?php 
	break;
	case 'small':
		?><h3><?php echo $v['label'] != '' ? SanitizeComponent::html($v['label']) : __('lblSmallHeading', true, false);?></h3><?php 
	break;
	default:
		?><h2><?php echo $v['label'] != '' ? SanitizeComponent::html($v['label']) : __('lblMediumHeading', true, false);?></h2><?php
	break;
}
?>