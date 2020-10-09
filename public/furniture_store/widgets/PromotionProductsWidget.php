<?php
use App\Controllers\Components\UtilComponent;
?>
<?php if (!empty($product_arr)):?>
<div class="product-single-row-double-slider-area mb-40">
    <div class="container">

        <div class="row align-items-center">
            <div class="col-lg-7">
                <!--=======  section title  =======-->
                
                <div class="section-title mb-20">
                    <h2>Khuyến mãi mới</h2>
                </div>
                
                <!--=======  End of section title  =======-->
            </div>

            <div class="col-lg-5">
                <div class="counter-deal">
                    Kết thúc: <div class="deal-countdown" data-countdown="<?php echo date('Y/m/d', strtotime($product_arr[0]['date_to_sale']));?>"></div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
				<!--=======  product single row slider wrapper  =======-->

                <div class="product-single-row-double-slider-wrapper">
                    <div class="ht-slick-slider"
                    data-slick-setting='{
                        "slidesToShow": 2,
                        "slidesToScroll": 1,
                        "dots": false,
                        "autoplay": false,
                        "autoplaySpeed": 5000,
                        "speed": 1000,
                        "arrows": true,
                        "prevArrow": {"buttonClass": "slick-prev", "iconClass": "ion-ios-arrow-left" },
                        "nextArrow": {"buttonClass": "slick-next", "iconClass": "ion-ios-arrow-right" }
                    }'
                    data-slick-responsive='[
                        {"breakpoint":1501, "settings": {"slidesToShow": 2} },
                        {"breakpoint":1199, "settings": {"slidesToShow": 2} },
                        {"breakpoint":991, "settings": {"slidesToShow": 1} },
                        {"breakpoint":767, "settings": {"slidesToShow": 1, "arrows": false} },
                        {"breakpoint":575, "settings": {"slidesToShow": 1, "arrows": false} },
                        {"breakpoint":479, "settings": {"slidesToShow": 2, "arrows": false} }
                    ]'
                    >
                    <?php foreach ($product_arr as $product):?>
                	<?php 
                	   $moneyFormatPattern = UtilComponent::getMoneyFormatPattern(); 
                	?>
                    <!--=======  double slider single item  =======-->
                    
                    <div class="double-slider-single-item-wrapper">
                        <div class="double-slider-single-item">
                
                            <div class="double-slider-single-item__inner-slider">
                                <div class="big-image-slider-wrapper">
                                    <div class="ht-slick-slider big-image-double-slider"
                                    data-slick-setting='{
                                        "slidesToShow": 1,
                                        "slidesToScroll": 1,
                                        "dots": false,
                                        "autoplay": false,
                                        "autoplaySpeed": 5000,
                                        "speed": 1000
                                    }'
                                    data-slick-responsive='[
                                        {"breakpoint":1501, "settings": {"slidesToShow": 1} },
                                        {"breakpoint":1199, "settings": {"slidesToShow": 1} },
                                        {"breakpoint":991, "settings": {"slidesToShow": 1} },
                                        {"breakpoint":767, "settings": {"slidesToShow": 1} },
                                        {"breakpoint":575, "settings": {"slidesToShow": 1} },
                                        {"breakpoint":479, "settings": {"slidesToShow": 1} }
                                    ]'
                                    >
                                    	<?php foreach($product['gallery_arr'] as $gallery):?>
                                        <!--=======  big image slider single item  =======-->
                                        <div class="big-image-slider-single-item">
                                            <img src="<?php echo $gallery['medium_path'];?>" class="img-fluid" alt="<?php echo $product['name'];?>">
                                        </div>
                                        <!--=======  End of big image slider single item  =======-->
                                        <?php endforeach;?>
                                    
                                    </div>
                                </div>
                
                
                                <div class="small-image-slider-wrapper">
                                    <div class="ht-slick-slider small-image-double-slider"
                                    data-slick-setting='{
                                        "slidesToShow": 3,
                                        "slidesToScroll": 1,
                                        "dots": false,
                                        "autoplay": false,
                                        "autoplaySpeed": 5000,
                                        "speed": 1000,
                                        "infinite": false,
                                        "arrows": true,
                                        "asNavFor": ".big-image-double-slider",
                                        "focusOnSelect": true,
                                        "prevArrow": {"buttonClass": "slick-prev", "iconClass": "ion-ios-arrow-left" },
                                        "nextArrow": {"buttonClass": "slick-next", "iconClass": "ion-ios-arrow-right" },
                                        "infinite": false
                                    }'
                                    data-slick-responsive='[
                                        {"breakpoint":1501, "settings": {"slidesToShow": 3} },
                                        {"breakpoint":1199, "settings": {"slidesToShow": 2} },
                                        {"breakpoint":991, "settings": {"slidesToShow": 3} },
                                        {"breakpoint":767, "settings": {"slidesToShow": 3} },
                                        {"breakpoint":575, "settings": {"slidesToShow": 4} },
                                        {"breakpoint":479, "settings": {"slidesToShow": 3} }
                                    ]'
                                    >
                                    	<?php foreach($product['gallery_arr'] as $gallery):?>
                                        <!--=======  small image slider single item  =======-->
                                        <div class="small-image-slider-single-item">
                                            <img src="<?php echo $gallery['small_path'];?>" class="img-fluid" alt="<?php echo $product['name'];?>">
                                        </div>
                                        <!--=======  End of big image slider single item  =======-->
                                        <?php endforeach;?>
                                    </div>
                                </div>
                            </div>
                
                            <div class="double-slider-single-item__content mt-20">
                                <p class="product-title mb-15"><a href="<?php echo $product['url'];?>"><?php echo $product['name'];?></a></p>
                                <p class="product-short-desc mb-25"><?php echo $product['short_desc']?></p>
                                <?php if (!empty($product['type_sale']) && $product['type_sale'] == 'percent'):?>
                                    <span class="discount-label discount-label--static discount-label--green mb-10">-<?php echo (int)$product['discount_sale'];?>%</span>
                                 <?php endif;?>
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
                                	<p class="product-price product-price--medium"><span style="font-size: 14px !important;" class="discounted-price"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$priceNew, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span> <span class="main-price discounted"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span></p>
                                <?php else:?>
                                	<p class="product-price product-price--medium"><span style="font-size: 14px !important;" class="discounted-price"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price, $moneyFormatPattern), $this->option_arr['o_currency'], " "); ?></span></p>
                                <?php endif;?>
                            </div>
                
                        </div>
                    </div>
                    
                    <!--=======  End of double slider single item  =======-->
                    <?php endforeach;?>
                
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php if($this->isAdmin()):?>
  <center>
  <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminWidgets&action=Update&id=<?php echo $widget_id?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
  </center>
<?php endif;?>
<?php endif;?>
