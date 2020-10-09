<?php if(!empty($node_arr)):?>
<ul class="top-nav">
	<?php for ($i = 0; $i < count($node_arr);):?>
		<?php $children = (int)$node_arr[$i]['children'];?>
		<?php if($children > 0):?>
			<li class="menu-item-has-children">
				<a href="<?php echo $node_arr[$i]['data']['link']?>"><?php echo $node_arr[$i]['data']['name']; ?></a>
				<ul class="sub-menu">
				<?php 
				$j = $i;
				$counter = 1;
				?>
				<?php while ($counter <= $children):?>
					<?php $j = $i + $counter;?>
					<li><a href="<?php echo $node_arr[$j]['data']['link']?>"><?php echo $node_arr[$j]['data']['name']; ?></a></li>
					<?php $counter++;?>
				<?php endwhile;?>
				<?php $i = $j + 1; ?>
				</ul>
			</li>
		<?php else:?>
			<li ><a href="<?php echo $node_arr[$i]['data']['link']?>"><?php echo $node_arr[$i]['data']['name']; ?></a></li>
			<?php $i++;?>
		<?php endif;?>
	<?php endfor;?>
	<li>
	<?php if($this->isAdmin()):?>
     <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" data-rte-cmd="removeElement" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
	<?php endif;?>
	</li>
</ul>
<?php endif;?>