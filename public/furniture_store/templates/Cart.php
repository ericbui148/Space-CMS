<?php 
use App\Controllers\CartController;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
$moneyFormatPattern = UtilComponent::getMoneyFormatPattern();
?>
<!--====================  breadcrumb area ====================-->

<div class="breadcrumb-area pt-10 pb-10 border-bottom mb-40">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <!--=======  breadcrumb content  =======-->
                
                <div class="breadcrumb-content">
                    <ul>
                        <li class="has-child"><a href="/">Trang chủ</a></li>
                        <li>Giỏ hàng</li>
                    </ul>
                </div>
                
                <!--=======  End of breadcrumb content  =======-->
            </div>
        </div>
    </div>
</div>

<!--====================  End of breadcrumb area  ====================-->

<!--==================== page content ====================-->

<div class="page-section pb-40">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form action="#">				
                    <!--=======  cart table  =======-->
                    
                    <div class="cart-table table-responsive mb-40">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="pro-thumbnail">Ảnh</th>
                                    <th class="pro-title">Sản phẩm</th>
                                    <th class="pro-price">Giá</th>
                                    <th class="pro-quantity">SL</th>
                                    <th class="pro-subtotal">Thành tiền</th>
                                    <th class="pro-remove"></th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php 
                            	$price_arr = CartController::CalcPrice($tpl['option_arr'], $tpl['cart_arr'], @$tpl['stock_arr'], @$tpl['extra_arr'], isset($tpl['o_shipping']) ? $tpl['o_shipping'] : null, isset($tpl['o_tax']) ? $tpl['o_tax'] : null, isset($tpl['o_fee']) ? $tpl['o_fee'] : null, @$_SESSION[$controller->defaultVoucher]);
                            	foreach ($tpl['cart_arr'] as $key => $cart_item)
                            	{
                            	    $item = unserialize($cart_item['key_data']);
                            	    $product = NULL;
                            	    foreach ($tpl['arr'] as $p)
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
        				      				foreach ($tpl['attr_arr'] as $attr)
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
        				      					foreach ($tpl['extra_arr'] as $extra)
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
        				      					foreach ($tpl['extra_arr'] as $extra)
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
        				      		$remaining_qty = (int) $tpl['stock_arr'][$cart_item['stock_id']]['qty'] - (int) @$tpl['order_arr'][$cart_item['stock_id']];
        				      		$max_qty = $cart_item['qty'] + $remaining_qty;
        				      		$product_amount = (float)$price_arr['p_arr'][$key] * (int) $cart_item['qty'];
        				      		?>
        				      		<tr>
                                        <td class="pro-thumbnail"><a href="<?php echo $href;?>"><img src="<?php echo BASE_URL . (!empty($tpl['image_arr'][$cart_item['stock_id']]) && is_file($tpl['image_arr'][$cart_item['stock_id']]) ? $tpl['image_arr'][$cart_item['stock_id']] : IMG_PATH . 'frontend/80x106.png'); ?>" class="img-fluid" alt="<?php echo $product['name'];?>"></a></td>
                                        <td class="pro-title"><a href="<?php echo $href;?>"><?php echo $product['name'];?></a></td>
                                        <td class="pro-price"><span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['p_arr'][$key], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></td>
                                        <td class="pro-quantity"><div class="pro-qty"><input type="text" name="qty[<?php echo $hash; ?>]" value="<?php echo (int) $cart_item['qty']; ?>" data-step="1" data-min="1" data-max="<?php echo (int) $max_qty; ?>" maxlength="<?php echo strlen($max_qty); ?>" readonly="readonly"></div></td> 
                                        <td class="pro-subtotal"><span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$product_amount, $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></td>
                                        <td class="pro-remove"><a href="#" class="scSelectorRemoveFromCart" data-hash="<?php echo $hash; ?>" ><i class="fa fa-trash-o"></i></a></td>
                                	</tr>
        				      		<?php
                            	   }
                            	?>

                            </tbody>
                        </table>
                    </div>
                    
                    <!--=======  End of cart table  =======-->
                    
                    
                </form>	
                    
                <div class="row">

                    <div class="col-lg-6 col-12">
                    	<?php if (!empty($tpl['tax_arr'])):?>
                        <!--=======  Calculate Shipping  =======-->
                        
                        <div class="calculate-shipping">
                            <h4>Vận chuyển & Thuế</h4>
                            <form action="#">
                                <div class="row">
                                    <div class="col-md-6 col-12 mb-25">
                                        <select class="nice-select">
                                        	<?php foreach ($tpl['tax_arr'] as $tax):?>
                                            <option value="<?php echo $tax['id'];?>"><?php echo $tax['location'];?></option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-12 mb-25">
                                        <input type="submit" value="Ước tính">
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!--=======  End of Calculate Shipping  =======-->
                        <?php endif;?>
                        
                        <!--=======  Discount Coupon  =======-->
                        
                        <div class="discount-coupon">
                            <h4>Khuyến mãi</h4>
                            <form action="#">
                                <div class="row">
                                    <div class="col-md-6 col-12 mb-25">
                                        <?php if (empty($_SESSION[$controller->defaultVoucher]['voucher_code'])):?>
                                        <input class="voucher_code" type="text" placeholder="Mã khuyến mãi">
                                        <?php else: ?>
                                        <input class="voucher_code" value="<?php echo @$_SESSION[$controller->defaultVoucher]['voucher_code'];?>" type="text" readonly="readonly">
                                        <?php endif;?>
                                        
                                    </div>
                                    <div class="col-md-6 col-12 mb-25">
                                    	<?php if (empty($_SESSION[$controller->defaultVoucher]['voucher_code'])):?>
                                        <input type="submit" class="apply_voucher_code" value="Áp dụng">
                                        <?php else: ?>
                                        <input type="submit" class="remove_voucher_code" value="Xoá mã">
                                        <?php endif;?>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!--=======  End of Discount Coupon  =======-->
                        
                    </div>

                    
                    <div class="col-lg-6 col-12 d-flex">
                        <!--=======  Cart summery  =======-->
                    
                        <div class="cart-summary">
                            <div class="cart-summary-wrap">
                                <h4>Chi phí</h4>
                                <p>Tiền hàng <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['amount'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></p>
                                
                                <?php if ($price_arr['insurance'] > 0):?>
                                <p>Bảo hiểm <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['insurance'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></p>
                                <?php endif;?>
                                
                                <?php if ($price_arr['shipping'] > 0):?>
                                <p>Phí vận chuyển <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['shipping'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></p>
                                <?php endif;?>
                                
                                <?php if ($price_arr['tax'] > 0):?>
                                <p>Thuế <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['tax'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></p>
                                <?php endif;?>
                                
                                <?php if ($price_arr['discount'] > 0):?>
                                <p>Giảm giá <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['discount'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></p>
                                <?php endif;?>
                                
                                <h2>Tổng tiền <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['total'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></h2>
                            </div>
                            <div class="cart-summary-button">
                                <button class="checkout-btn">Thanh toán</button>
                                <button class="update-cart-btn">Mua thêm</button>
                            </div>
                        </div>
                    
                        <!--=======  End of Cart summery  =======-->
                        
                    </div>

                </div>
                
            </div>
        </div>
    </div>
</div>