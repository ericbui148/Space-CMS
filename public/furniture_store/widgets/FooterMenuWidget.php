<?php if(!empty($node_arr)):?>
<?php if(!empty($metadata) && !empty($metadata["class"])):?>
<ul class="<?php echo $metadata["class"];?>">
<?php else: ?>
	<ul>
<?php endif;?>
	<?php foreach ($node_arr as $node):?>
			<li><a href="<?php echo $node['data']['link']?>"><?php echo $node['data']['name']; ?></a></li>
	<?php endforeach;?>

</ul>
<?php if($this->isAdmin()):?>
     <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" data-rte-cmd="removeElement" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>
<?php endif;?>