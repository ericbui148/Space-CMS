<?php
use App\Models\WidgetModel;
use Core\Framework\Components\SanitizeComponent;
?>
<!-- Breadcrumbs -->
<section class="breadcrumbs-custom-inset">
<div class="breadcrumbs-custom context-dark">
  <div class="container">
    <h2 class="breadcrumbs-custom-title"><?php echo $tpl['product_arr']['name'];?></h2>
    <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM_SHOP, null, [
    'page_type' => 'product'
    ]);?>
  </div>
  <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
</div>
</section>
<!-- Single Product-->
<section class="section section-sm section-first bg-default">
<div class="container">
  <div class="row row-50">
    <div class="col-lg-6">
      <div class="slick-product">
        <!-- Slick Carousel-->

        <div class="slick-slider carousel-parent" data-swipe="true" data-items="1" data-child="#child-carousel" data-for="#child-carousel">
          <?php foreach ($tpl['product_arr']['gallery_arr'] as $image):?>
              <div class="item">
                <div class="slick-product-figure"><img src="<?php echo $image['medium_path'];?>" alt="<?php echo $tpl['product_arr']['name'];?>" width="530" height="480"/>
                </div>
              </div>
          <?php endforeach;?>
        </div>

        <div class="slick-slider child-carousel" id="child-carousel" data-for=".carousel-parent" data-arrows="true" data-items="3" data-sm-items="3" data-md-items="3" data-lg-items="3" data-xl-items="3" data-slide-to-scroll="1" data-md-vertical="true">
          <?php foreach ($tpl['product_arr']['gallery_arr'] as $image):?>
          <div class="item">
            <div class="slick-product-figure"><img src="<?php echo $image['medium_path'];?>" alt="<?php echo $tpl['product_arr']['name'];?>" width="169" height="152"/>
            </div>
          </div>
           <?php endforeach;?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="single-product">
        <h3><?php echo $tpl['product_arr']['name'];?></h3>
          <?php if($controller->isAdmin()):?>
         <a href="<?php echo $controller->baseUrl();?>index.php?controller=AdminProducts&action=Update&id=<?php echo $tpl['arr']['id'];?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
    	<?php endif;?>
        <p><?php echo $tpl['product_arr']['short_desc'];?></p>
        <div class="divider divider-40"></div>
        <div class="group-md group-middle"><span class="social-title">Follow US</span>
          <div>
			<?php $controller->renderWidgetByName('footer_follow_we_on_social');?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap tabs-->
  <div class="tabs-custom tabs-horizontal tabs-corporate" id="tabs-5">
    <!-- Nav tabs-->
    <ul class="nav nav-tabs">
      <li class="nav-item" role="presentation"><a class="nav-link active" href="#tabs-5-1" data-toggle="tab"><?php __i18n('product_description');?></a></li>
    </ul>
    <!-- Tab panes-->
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tabs-5-1">
        <p><?php echo $tpl['product_arr']['full_desc'];?></p>
      </div>
    </div>
  </div>
</div>
</section>
<?php if (isset($tpl['similar_arr']) && !empty($tpl['similar_arr'])):?>
<!--====================  product single row slider area ====================-->
<!-- Single Product-->
<section class="section section-sm section-last bg-default">
<div class="container">
  <h4>Related Products</h4>
  <div class="row row-40 justify-content-center">
  <?php foreach ($tpl['similar_arr'] as $similar):?>
    <div class="col-sm-6 col-md-5 col-lg-3">
      <!-- Product-->
      <article class="product">
        <div class="product-figure"><img src="<?php echo $similar['pic'];?>" alt="" width="270" height="280"/></div>
        <h5 class="product-title"><a href="<?php echo $similar['url'];?>"><?php echo SanitizeComponent::html($similar['name']); ?></a></h5>
        <div class="product-price-wrap">
          <div class="product-price"><?php __i18n("contact_price");?></div>
        </div>
      </article>
    </div>
   <?php endforeach;?>
</div>
</section>
<?php endif;?>
