<!-- Swiper-->
<?php $controller->renderWidgetByName('home slider');?>

<!-- In the spotlight-->
<section class="section section-sm section-first bg-default">
	<div class="container">
		<div class="oh">
			<div class="title-decoration-lines wow slideInUp"
				data-wow-delay="0s">
				<h6 class="title-decoration-lines-content"><?php $controller->renderWidgetByName('what_we_offer_title');?></h6>
			</div>
		</div>
		<div class="row row-30 justify-content-center">
			<?php $controller->renderWidgetByName('what_we_offer_menu');?>
		</div>
	</div>
</section>
<!-- Icons Ruby-->
<section class="section section-sm bg-default">
	<div class="container">
		<div class="oh">
			<div class="title-decoration-lines wow slideInUp"
				data-wow-delay="0s">
				<h6 class="title-decoration-lines-content"><?php $controller->renderWidgetByName("home_service_us_title");?></h6>
			</div>
		</div>
		<div class="row row-30 justify-content-center">
			<div class="col-sm-6 col-lg-4 wow fadeInRight" data-wow-delay="0s">
				<article class="box-icon-ruby">
					<div
						class="unit box-icon-ruby-body flex-column flex-md-row text-md-left flex-lg-column text-lg-center flex-xl-row text-xl-left">
						<div class="unit-left">
							<div class="box-icon-ruby-icon fl-bigmug-line-airplane86"></div>
						</div>
						<div class="unit-body">
							<?php $controller->renderWidgetByName('home_free_ship');?>
						</div>
					</div>
				</article>
			</div>
			<div class="col-sm-6 col-lg-4 wow fadeInRight" data-wow-delay=".1s">
				<article class="box-icon-ruby">
					<div
						class="unit box-icon-ruby-body flex-column flex-md-row text-md-left flex-lg-column text-lg-center flex-xl-row text-xl-left">
						<div class="unit-left">
							<div class="box-icon-ruby-icon fl-bigmug-line-circular220"></div>
						</div>
						<div class="unit-body">
							<?php $controller->renderWidgetByName('home_high_quality_prodduct');?>
						</div>
					</div>
				</article>
			</div>
			<div class="col-sm-6 col-lg-4 wow fadeInRight" data-wow-delay=".2s">
				<article class="box-icon-ruby">
					<div
						class="unit box-icon-ruby-body flex-column flex-md-row text-md-left flex-lg-column text-lg-center flex-xl-row text-xl-left">
						<div class="unit-left">
							<div class="box-icon-ruby-icon fl-bigmug-line-hot67"></div>
						</div>
						<div class="unit-body">
							<?php $controller->renderWidgetByName('home_discount_prodduct');?>
						</div>
					</div>
				</article>
			</div>
		</div>
	</div>
</section>
<!-- Improve your interior with deco-->
<section class="section section-sm bg-default">
	<div class="container">
		<div class="oh">
			<div class="title-decoration-lines wow slideInUp"
				data-wow-delay="0s">
				<h6 class="title-decoration-lines-content"><?php $controller->renderWidgetByName("home_best_project_title");?></h6>
			</div>
		</div>
		<div class="row row-30" data-lightgallery="group">
			<?php $controller->renderWidgetByName('featured_projects');?>
		</div>
	</div>
</section>

<section class="section section-sm section-last bg-default">
	<div class="container">
		<div class="oh">
			<div class="title-decoration-lines wow slideInUp"
				data-wow-delay="0s">
				<h6 class="title-decoration-lines-content"><?php $controller->renderWidgetByName('home_partner_us');?></h6>
			</div>
		</div>
		<!-- Owl Carousel-->
		<?php $controller->renderWidgetByName('home_partners_slider');?>
	</div>
</section>