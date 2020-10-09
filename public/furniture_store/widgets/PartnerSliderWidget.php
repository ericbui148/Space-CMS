<?php if($gallery_arr):?>
<div class="owl-carousel owl-clients" data-items="1"
	data-sm-items="2" data-md-items="3" data-lg-items="4"
	data-margin="30" data-dots="true" data-animation-in="fadeIn"
	data-animation-out="fadeOut" data-autoplay="true">
	<?php foreach($gallery_arr as $image):?>
	<a class="clients-modern" href="<?php echo $image['link'];?>"><img
		src="<?php echo $image['source_path'];?>" alt="<?php echo $image['title'];?>" width="270"
		height="145"></a>
	<?php endforeach;?>
</div>
<?php if($this->isAdmin()):?>
<a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminSliders&action=Update&id=<?php echo $slider_id?>"><i
	class="fa fa-pencil" style="color: red;"></i></a>
<?php endif;?>
<?php endif;?>
