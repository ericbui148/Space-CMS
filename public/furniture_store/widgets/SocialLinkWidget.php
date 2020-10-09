<?php if (!empty($metadata)):?>
<ul class="list-inline list-inline-xs footer-social-list-2">
	<?php foreach ($metadata as $item):?>
	<li><a class="<?php echo $item['icon'];?>" href="<?php echo $item['link'];?>"></a></li>
	<?php endforeach;?>
</ul>
<?php if($this->isAdmin()):?>
  <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminWidgets&action=Update&id=<?php echo $widget_id?>" style="color:red!important; font-size: 14px !important;"><i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>
<?php endif;?>