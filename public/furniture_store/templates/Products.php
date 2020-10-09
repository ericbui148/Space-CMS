<?php
use App\Models\WidgetModel;
?>
  <!-- Breadcrumbs -->
  <section class="breadcrumbs-custom-inset">
    <div class="breadcrumbs-custom context-dark">
      <div class="container">
        <?php if (!empty($tpl['category'])):?>
        <h2 class="breadcrumbs-custom-title"><?php echo $tpl['category']['name'];?></h2>
        <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM_SHOP, null, [
        'page_type' => 'product_category'
        ]);?>
        <?php else: ?>
         <h2 class="breadcrumbs-custom-title"><?php __i18n('Product Search Results');?></h2>
         <ul class="breadcrumbs-custom-path">
          	<li><a href="/"><?php __i18n('home');?></a> </li>
          	<li><?php __i18n('Product Search Results');?></li>
         </ul>        
        <?php endif;?>
      </div>
      <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
    </div>
  </section>
  <!-- Shop-->
  <section class="section section-xl bg-default">
    <div class="container">
      <div class="row row-90 justify-content-center">
        <div class="col-lg-8 col-xl-9">
          <div class="product-top-panel group-lg">
            <div class="product-top-panel-title"><?php echo sprintf(__i18n("Showing %d–%d of %d results", true), $tpl['paginator']['offset'], $tpl['paginator']['offset'] + $tpl['paginator']['row_count'] > $tpl['paginator']['count'] ?$tpl['paginator']['count'] :  $tpl['paginator']['offset'] + $tpl['paginator']['row_count'],  $tpl['paginator']['count']);?></div>
            <div class="product-top-panel-select">
              <!--Select 2-->
              <select class="form-input select-filter" data-minimum-results-for-search="Infinity" data-constraints="@Required">
                <option value="1"><?php __i18n('Sort by new');?></option>
                <option value="2"><?php __i18n('Sort by featured');?></option>
              </select>
            </div>
          </div>
          <?php if (!empty($tpl['category']) && !empty(@$tpl['category']['description'])):?>
           <div class="row row-lg row-40">
           		 <div class="col-md-12">
           		 	<h3 class="product-title"><?php echo @$tpl['category']['name'];?></h3>
           		 	<?php echo @$tpl['category']['description'];?>
           		 </div>
           </div>
           <?php endif;?>
          <div class="row row-lg row-40">
          	<?php foreach ($tpl['product_arr'] as $product):?>
            <div class="col-sm-6 col-md-4">
              <!-- Product-->
              <article class="product">
              	<?php $imagePath = !empty($product['pic'])? $product['pic'] : 'app/web/img/frontend/noimg.jpg';?>
                <div class="product-figure"><img src="<?php echo $imagePath;?>" alt="<?php echo $product['name'];?>" width="270" height="280"/>
                </div>
                <h5 class="product-title"><a href="<?php echo $product['url'];?>"><?php echo $product['name'];?></a></h5>
                <div class="product-price-wrap">
                  <div class="product-price"><?php __i18n('contact')?></div>
                </div>
              </article>
            </div>
            <?php endforeach;?>
          </div>
      <!--=======  pagination  =======-->
        <?php
        	if (isset($tpl['paginator']))
        	{
        	 	if($tpl['paginator']['count'] > $tpl['paginator']['row_count'])
        	 	{
        			?>
        			<div class="pagination-wrap">
        				<nav aria-label="Page navigation">
    					<ul class="pagination">
    						<?php
    						if ($tpl['paginator']['pages'] > 1 && $tpl['paginator']['page'] > 1)
    						{ 
    							$i = $tpl['paginator']['page'] - 1;
    							?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						for ($i = 1; $i <= $tpl['paginator']['pages']; $i++)
    						{
    						    ?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						if ($tpl['paginator']['pages'] > $tpl['paginator']['page'])
    						{
    							$i = $tpl['paginator']['page'] + 1;
    							?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						?>
    					</ul>
    					</nav>
        			</div>
        			<?php
        	 	}
        	}
        ?>
        <!--=======  End of pagination  =======-->
        </div>
        <div class="col-sm-10 col-md-12 col-lg-4 col-xl-3">
          <!-- RD Search Form-->
          <form class="form-search rd-search form-product-search" action="<?php echo BASE_URL?>products" method="GET">
            <div class="form-wrap">
              <label class="form-label" for="search-form"><?php __i18n('Product Search..')?></label>
              <input class="form-input" id="search-form" type="text" name="q" autocomplete="off">
              <button class="button-search fl-bigmug-line-search74" type="submit"></button>
            </div>
          </form>
          <div class="row row-lg row-50 product-sidebar">
            <div class="col-md-6 col-lg-12">
              <h5><?php $controller->renderWidgetByName('products_cat_title');?></h5>
              <?php $controller->renderWidgetByName('products_right_menu');?>
            </div>
            <div class="col-md-6 col-lg-12">
              <?php $controller->renderWidgetByName('product_right_images');?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
