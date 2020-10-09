<?php 
use App\Controllers\Components\UtilComponent;
use App\Controllers\AppController;
?>
<?php if(!empty($product_arr)):?>
<?php foreach ($product_arr as $product):?>
	<?php 
	$slug = NULL;
	if ((int) $this->option_arr['o_seo_url'] === 1)
	{
		# Seo friendly URLs ---------
		$slug = sprintf("%s-%u.html", AppController::friendlyURL($product['name']), $product['id']);
		$href = $this->getBaseUrl() .'chi-tiet-san-pham/'.$slug;
	} else {
		# Non-Seo friendly URLs ---------------------
		$href = UtilComponent::getReferer() .__('prefix_product_category', true, false). '#!/Product/' . $product['id'];
	}
	$src = $this->getBaseUrl() . IMG_PATH . 'frontend/noimg.png';
	if (!empty($product['pic']))
	{
		$src = $product['pic'];
	}
	$moneyFormatPattern = UtilComponent::getMoneyFormatPattern();
	?>
	<div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
		<div class="inner">
		   <a class="MagicZoom" href="<?php echo $src;?>" data-options="zoomPosition: bottom; expand:false;"><img src="<?php echo $src;?>" alt="<?php echo $product['name']?>" /></a>
		   <div class="desc">
		        <h3><a href="<?php echo $href;?>"><?php echo $product['name']?></a></h3>
		        <div class="priceNew">
		        <?php echo UtilComponent::formatNumberByPattern((float)$product['min_price'], $moneyFormatPattern);?><strong></strong> <?php echo $this->option_arr['o_currency'];?>
		        </div>
		        <a href="<?php echo $href;?>" class="btn btn-readmore">Chi tiết</a>
		   </div>
	    </div>
	</div>
	<?php endforeach;?>
<?php endif;?>