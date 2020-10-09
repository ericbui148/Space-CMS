<?php if(!empty($node_arr)):?>
<ul class="isotope-filters-list" id="isotope-1">
	<?php for ($i = 0; $i < count($node_arr);):?>
		<?php $children = (int)$node_arr[$i]['children'];?>
		<?php if($children > 0):?>
			<li class="rd-nav-item rd-navbar--has-dropdown rd-navbar-submenu">
				<a class="rd-nav-link" href="<?php echo $node_arr[$i]['data']['link']?>"><?php echo $node_arr[$i]['data']['name']; ?></a>
				<ul class="rd-menu rd-navbar-dropdown">
				<?php 
				$j = $i;
				$counter = 1;
				?>
				<?php while ($counter <= $children):?>
					<?php $j = $i + $counter;?>
					<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="<?php echo $node_arr[$j]['data']['link']?>"><?php echo $node_arr[$j]['data']['name']; ?></a></li>
					<?php $counter++;?>
				<?php endwhile;?>
				<?php $i = $j + 1; ?>
				</ul>
			</li>
		<?php else:?>
			<li><a <?php echo ($_SERVER['REQUEST_URI'] == '/'.$node_arr[$i]['data']['link'] || $_SERVER['REQUEST_URI'] == $node_arr[$i]['data']['link'])? 'class="active" ' : '';?>href="<?php echo $node_arr[$i]['data']['link']?>"><?php echo $node_arr[$i]['data']['name']; ?></a></li>
			<?php $i++;?>
		<?php endif;?>
	<?php endfor;?>
</ul>
<?php if($this->isAdmin()):?>
 <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" data-rte-cmd="removeElement" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>
<?php endif;?>