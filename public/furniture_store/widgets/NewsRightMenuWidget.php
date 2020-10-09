<?php if(!empty($node_arr)):?>
<ul class="list list-categories">
	<?php for ($i = 0; $i < count($node_arr); $i++):?>
		<li><a href="<?php echo $node_arr[$i]['data']['link']?>"><?php echo $node_arr[$i]['data']['name']; ?></a></li>
	<?php endfor;?>
</ul>
<?php if($this->isAdmin()):?>
 <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" style="color: red;font-size: 14 !important;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>
<?php endif;?>