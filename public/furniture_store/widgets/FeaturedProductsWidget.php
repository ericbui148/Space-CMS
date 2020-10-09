<?php
use App\Controllers\Components\UtilComponent;
?>
<?php if (!empty($product_arr)):?>
<div class="product-single-row-slider-wrapper">
                        <div class="ht-slick-slider"
                        data-slick-setting='{
                            "slidesToShow": 4,
                            "slidesToScroll": 1,
                            "dots": false,
                            "autoplay": false,
                            "autoplaySpeed": 5000,
                            "speed": 1000,
                            "arrows": true,
                            "infinite": false,
                            "prevArrow": {"buttonClass": "slick-prev", "iconClass": "ion-ios-arrow-left" },
                            "nextArrow": {"buttonClass": "slick-next", "iconClass": "ion-ios-arrow-right" }
                        }'
                        data-slick-responsive='[
                            {"breakpoint":1501, "settings": {"slidesToShow": 4} },
                            {"breakpoint":1199, "settings": {"slidesToShow": 4} },
                            {"breakpoint":991, "settings": {"slidesToShow": 3} },
                            {"breakpoint":767, "settings": {"slidesToShow": 2, "arrows": false} },
                            {"breakpoint":575, "settings": {"slidesToShow": 2, "arrows": false} },
                            {"breakpoint":479, "settings": {"slidesToShow": 2, "arrows": false} }
                        ]'
                        >
 		<?php foreach ($product_arr as $product):?>
    	<?php 
    	   $moneyFormatPattern = UtilComponent::getMoneyFormatPattern(); 
    	?>
        <!--=======  single slider product  =======-->
        
        <div class="single-slider-product-wrapper">
            <div class="single-slider-product">
                <div class="single-slider-product__image">
                    <a href="<?php echo $product['url'];?>">
                        <img src="<?php echo $product['pic'];?>" class="img-fluid" alt="">
                    </a>

                    <?php if (!empty($product['type_sale']) && $product['type_sale'] == 'percent'):?>
                    	<span class="discount-label discount-label--green">-<?php echo (int)$product['discount_sale'];?>%</span>
                    <?php endif;?>
                </div>
                
                <div class="single-slider-product__content">
                    <p class="product-title"><a href="<?php echo $product['url'];?>"><?php echo $product['name'];?></a></p>
                    <div class="rating">
                        <i class="ion-android-star active"></i>
                        <i class="ion-android-star active"></i>
                        <i class="ion-android-star active"></i>
                        <i class="ion-android-star active"></i>
                        <i class="ion-android-star"></i>
                    </div>
                    <?php
                        $priceNew = 0;
                        if (!empty($product['type_sale'])) {
                            switch ($product['type_sale']) {
                                case "percent":
                                    if ((float)$product['min_price'] > 0) {
                                        $priceNew = (float)$product['min_price'] - (float)$product['min_price'] * ((float)$product['discount_sale'] / 100);
                                    }
                                    break;
                                case "amount":
                                    if ((float)$product['min_price'] > (float)$product['discount_sale']) {
                                        $priceNew = (float)$product['min_price'] - (float)$product['discount_sale'];
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                        $price = $product['min_price'];
                    ?>

                    <?php if ($priceNew > 0):?>
                    	<p class="product-price"><span class="discounted-price"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$priceNew, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span> <span class="main-price discounted"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span></p>
                    <?php else:?>
                    	<?php if($price == 0):?>
                    	<p class="product-price"><span class="discounted-price">Giá: Liên hệ</span></p>
                    	<?php else:?>
                    	<p class="product-price"><span class="discounted-price"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span></p>
                    	<?php endif;?>
                    <?php endif;?>

                </div>
            </div>

        </div>
        <?php endforeach;?>

    </div>
</div>
<?php if($this->isAdmin()):?>
  <center>
  <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminWidgets&action=Update&id=<?php echo $widget_id?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
  </center>
<?php endif;?>
<?php endif;?>