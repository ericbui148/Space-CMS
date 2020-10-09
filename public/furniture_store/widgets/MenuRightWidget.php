<?php if(!empty($node_arr)):?>
<ul class="list list-shop-filter">
	<?php for ($i = 0; $i < count($node_arr);):?>
		<?php $children = (int)$node_arr[$i]['children'];?>
		<?php if($children > 0):?>
			<li><label class="checkbox-inline"><a href="<?php echo $node_arr[$i]['data']['link']?>"><span><?php echo $node_arr[$i]['data']['name']; ?></span></a></label>
				<ul>
				<?php 
				$j = $i;
				$counter = 1;
				?>
				<?php while ($counter <= $children):?>
					<?php $j = $i + $counter;?>
					<li><a href="<?php echo $node_arr[$j]['data']['link']?>"><span><?php echo $node_arr[$j]['data']['name']; ?></span></a></li>
					<?php $counter++;?>
				<?php endwhile;?>
				<?php $i = $j + 1; ?>
				</ul>
			</li>
		<?php else:?>
			<li ><label class="checkbox-inline"><a href="<?php echo $node_arr[$i]['data']['link']?>"><span><?php echo $node_arr[$i]['data']['name']; ?></span></a></label></li>
			<?php $i++;?>
		<?php endif;?>
	<?php endfor;?>
	<li>
	<?php if($this->isAdmin()):?>
     <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
	<?php endif;?>
	</li>
</ul>
<?php endif;?>