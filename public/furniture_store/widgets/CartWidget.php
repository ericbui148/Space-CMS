<?php
use App\Controllers\CartController;
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\Components\UtilComponent;
$moneyFormatPattern = UtilComponent::getMoneyFormatPattern();
?>
<?php if (!empty($cart_arr)):?>
<!--=======  cart icon  =======-->

<div class="header-cart-icon">
    <a href="javascript:void(0)" id="small-cart-trigger" class="small-cart-trigger">
        <i class="icon-shopping-cart"></i>
        <span class="cart-counter"><?php echo count($cart_arr);?></span>
    </a>

    <!--=======  small cart  =======-->
    
    <div class="small-cart deactive-dropdown-menu">
        <div class="small-cart-item-wrapper">
        <?php 
        $tpl = $this->controller->tpl;
        $price_arr = CartController::CalcPrice($tpl['option_arr'], @$cart_arr, @$stock_arr, @$extra_arr, isset($tpl['option_arr']['o_shipping']) ? $tpl['option_arr']['o_shipping'] : null, isset($tpl['option_arr']['o_tax']) ? $tpl['option_arr']['o_tax'] : null, isset($tpl['option_arr']['o_fee']) ? $tpl['option_arr']['o_fee'] : null, @$_SESSION[$this->controller->defaultVoucher]);
        foreach ($cart_arr as $key => $cart_item)
    	{
    	    $item = unserialize($cart_item['key_data']);
    	    $product = NULL;
    	    foreach ($arr as $p)
    	    {
    	        if ($p['id'] == $cart_item['product_id'])
    	        {
    	            $product = $p;
    	            break;
    	        }
    	    }
    	    if (is_null($product))
    	    {
    	        continue;
    	    }                            	    
    	    $hash = md5($cart_item['key_data']);
    	    $href = $product['url'];
      		if (isset($item['attr']) && !empty($item['attr']))
      		{
      			$attributes = array();
      			foreach ($item['attr'] as $attr_parent_id => $attr_id)
      			{
      				foreach ($attr_arr as $attr)
      				{
      					if ($attr['id'] == $attr_parent_id && isset($attr['child']) && is_array($attr['child']))
      					{
      						foreach ($attr['child'] as $child)
      						{
      							if ($child['id'] == $attr_id)
      							{
      								$attributes[] = sprintf('%s: %s', SanitizeComponent::html($attr['name']), SanitizeComponent::html($child['name']));
      								break;
      							}
      						}
      					}
      				}
      			}
      			
      		}
      		if (isset($item['extra']) && !empty($item['extra']))
      		{
      			$extras = array();
      			foreach ($item['extra'] as $eid)
      			{
      				if (strpos($eid, ".") === FALSE)
      				{
      					//single
      					foreach ($extra_arr as $extra)
      					{
      						if ($extra['id'] == $eid)
      						{
      							$extras[] = sprintf("%s %s", SanitizeComponent::html($extra['name']), UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $tpl['option_arr']['o_currency']));
      							break;
      						}
      					}
      				} else {
      					//multi
      					list($e_id, $ei_id) = explode(".", $eid);
      					foreach ($extra_arr as $extra)
      					{
      						if ($extra['id'] == $e_id && isset($extra['extra_items']) && !empty($extra['extra_items']))
      						{
      							foreach ($extra['extra_items'] as $extra_item)
      							{
      								if ($extra_item['id'] == $ei_id)
      								{
      									$extras[] = sprintf("%s %s", SanitizeComponent::html($extra_item['name']), UtilComponent::formatCurrencySign(number_format($extra_item['price'], 2), $tpl['option_arr']['o_currency']));
      									break;
      								}
      							}
      							break;
      						}
      					}
      				}
      			}
      		}
      		?>
            <div class="single-item">
                <div class="image">
                    <a href="<?php echo $href;?>">
                        <img src="<?php echo BASE_URL . (!empty($image_arr[$cart_item['stock_id']]) && is_file($image_arr[$cart_item['stock_id']]) ? $image_arr[$cart_item['stock_id']] : IMG_PATH . 'frontend/80x106.png'); ?>" class="img-fluid" alt="<?php echo $product['name'];?>">
                    </a>
                </div>
                <div class="content">
                    <p class="cart-name"><a href="<?php echo $href;?>"><?php echo $product['name'];?></a></p>
                    <p class="cart-quantity"><span class="quantity-mes"><?php echo (int) $cart_item['qty']; ?> x </span> <?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['p_arr'][$key], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></p>
                </div>
                <a href="#" data-hash="<?php echo $hash; ?>" class="remove-icon homeRemoveFromCart"><i class="ion-close-round"></i></a>
            </div>
			<?php
    	       }
    	   ?>
        </div>

        <div class="cart-calculation-table">
            <table class="table mb-25">
                <tbody>
                    <tr>
                        <td class="text-left">Tiền hàng :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['amount'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                    <?php if ($price_arr['insurance'] > 0):?>
                    <tr>
                        <td class="text-left">Bảo hiểm :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['insurance'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                    <?php endif;?>
                    <?php if ($price_arr['shipping'] > 0):?>
                    <tr>
                        <td class="text-left">Phí vận chuyển :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['shipping'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                    <?php endif;?>
                    <?php if ($price_arr['tax'] > 0):?>
                    <tr>
                        <td class="text-left">Thuế :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['tax'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                    <?php endif;?>
                    <?php if ($price_arr['discount'] > 0):?>
                    <tr>
                        <td class="text-left">Giảm giá :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['discount'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                    <?php endif;?>
                    <tr>
                        <td class="text-left">Tổng tiền :</td>
                        <td class="text-right"><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['total'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="cart-buttons">
                <a href="giohang" class="theme-button">Xem giỏ hàng</a>
                <a href="thanhtoan" class="theme-button">Thanh toán</a>
            </div>
        </div>

    </div>
    
    <!--=======  End of small cart  =======-->
</div>

<!--=======  End of cart icon  =======-->
<?php endif;?>