
<?php if (!empty($link_arr)):?>
<!--====================  breadcrumb area ====================-->
<ul class="breadcrumbs-custom-path">
  	<li><a href="/"><?php __i18n('home');?></a> </li>
	<?php if(!empty($link_arr)):?>
	<?php for ($i = count($link_arr) - 1; $i >= 0; $i--):?>
		<li><a href="<?php echo $link_arr[$i]['link'];?>"><?php echo $link_arr[$i]['name'];?></a></li>
	<?php endfor;?>
	<?php endif;?>
 </ul>
<!--====================  End of breadcrumb area  ====================-->
<?php endif;?>