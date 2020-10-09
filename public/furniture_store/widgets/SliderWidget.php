<?php if($gallery_arr):?>
<section
	class="section swiper-container swiper-slider swiper-slider-modern"
	data-loop="true" data-autoplay="5000" data-simulate-touch="true"
	data-nav="true" data-slide-effect="fade">
	<div class="swiper-wrapper text-left">
	<?php foreach ($gallery_arr as $gallery):?>
		<div class="swiper-slide"
			data-slide-bg="<?php echo $gallery['source_path']?>">
			<div class="swiper-slide-caption">
				<div class="container">
					<div class="row">
						<div class="col-11 col-sm-9 col-md-8 col-lg-7 col-xl-6 col-xxl-5">
							<div class="slider-modern-box">
								<?php echo @$gallery['title'];?>
								<?php echo @$gallery['description'];?>
								<?php echo @$gallery['link'];?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
 	<?php endforeach;?>
	</div>
	<!-- Swiper Navigation-->
	<div class="swiper-button-prev"></div>
	<div class="swiper-button-next"></div>
	<!-- Swiper Pagination-->
	<div class="swiper-pagination swiper-pagination-style-2"></div>
</section>
<?php if($this->isAdmin()):?>
<a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminSliders&action=Update&id=<?php echo $slider_id?>"><i
	class="fa fa-pencil" style="color: red;"></i></a>
<?php endif;?>
<?php endif;?>

