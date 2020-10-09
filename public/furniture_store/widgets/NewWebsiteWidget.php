<div class="row">
	<ul class="sample-item-list" data-plugin-masonry data-plugin-options='{"itemSelector": ".isotope-item"}'>
		<?php foreach ($arr as $website):?>
		<li class="col-sm-6 col-md-3 isotope-item">
			<div class="sample-item">
				<a href="<?php echo $this->getBaseUrl().'redirect/'.$website['domain']; ?>" class="">
					<span class="sample-item-image" data-original="<?php echo '//app7.cdn.vccloud.vn/'.$website['avatar'];?>" data-plugin-lazyload></span>
					<span class="sample-item-description">
						<h5><?php echo @$website['domainame'];?></h5>
					</span>
				</a>
			</div>
		</li>
		<?php endforeach;?>
	</ul>
</div>