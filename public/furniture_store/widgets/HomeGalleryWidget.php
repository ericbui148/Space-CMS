<?php if($gallery_arr):?>
<div class="row row-10 gutters-10" data-lightgallery="group">
	<?php foreach ($gallery_arr as $gallery):?>
	<div class="col-6 col-lg-<?php echo 12/(!empty($metadata['column'])? $metadata['column']: 2)?>">
		<!-- Thumbnail Classic-->
		<article class="thumbnail thumbnail-mary">
			<div class="thumbnail-mary-figure">
				<img src="<?php echo $gallery['medium_path']?>"
					alt="" width="124" height="124">
			</div>
			<div class="thumbnail-mary-caption">
				<a class="icon fl-bigmug-line-zoom60"
					href="<?php echo $gallery['source_path']?>"
					data-lightgallery="item"><img
					src="<?php echo $gallery['medium_path']?>" alt="<?php echo $gallery['title'];?>"
					width="124" height="124"></a>
			</div>
		</article>
	</div>
	<?php endforeach;?>
</div>
<?php if($this->isAdmin()):?>
<a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminSliders&action=Update&id=<?php echo $slider_id?>"><i
	class="fa fa-pencil" style="color: red;"></i></a>
<?php endif;?>
<?php endif;?>