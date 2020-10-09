<?php
use Core\Framework\Components\SanitizeComponent;
use App\Controllers\CartController;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\TimeComponent;
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
                        <li>Xem lại đơn hàng</li>
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
	<?php if (isset($tpl['status']) && $tpl['status'] == 'OK'): ?>
	<?php 
    	$bSaveReady = $sSaveReady = $bSaveChecked = $sSaveChecked = false;
    	
    	$STORAGE = @$_SESSION[$controller->defaultForm];
    	$billing = $shipping = $STORAGE;
    	$isLoged = $controller->isLoged();
    	if ($isLoged && is_null($STORAGE))
    	{
    	    if (isset($tpl['address_arr']) && !empty($tpl['address_arr']))
    	    {
    	        foreach ($tpl['address_arr'] as $address)
    	        {
    	            if ((int) $address['is_default_shipping'] === 1)
    	            {
    	                $shipping = array(
    	                    's_address_id' => $address['id'],
    	                    's_name' => $address['name'],
    	                    's_country_id' => $address['country_id'],
    	                    's_city' => $address['city'],
    	                    's_state' => $address['state'],
    	                    's_zip' => $address['zip'],
    	                    's_address_1' => $address['address_1'],
    	                    's_address_2' => $address['address_2']
    	                );
    	            }
    	            
    	            if ((int) $address['is_default_billing'] === 1)
    	            {
    	                $billing = array(
    	                    'b_address_id' => $address['id'],
    	                    'b_name' => $address['name'],
    	                    'b_country_id' => $address['country_id'],
    	                    'b_city' => $address['city'],
    	                    'b_state' => $address['state'],
    	                    'b_zip' => $address['zip'],
    	                    'b_address_1' => $address['address_1'],
    	                    'b_address_2' => $address['address_2']
    	                );;
    	            }
    	        }
    	    }
    	    
    	    if (empty($shipping))
    	    {
    	        $shipping = array(
    	            's_name' => $_SESSION[$controller->defaultUser]['client_name']
    	        );
    	    }
    	    if (empty($billing))
    	    {
    	        $billing = array(
    	            'b_name' => $_SESSION[$controller->defaultUser]['client_name']
    	        );
    	    }
    	}
    	if ($isLoged && isset($tpl['address_arr']) && !empty($tpl['address_arr']) && isset($STORAGE['b_save']))
    	{
    	    $bSaveReady = true;
    	    $bSaveChecked = true;
    	}
    	
    	if ($isLoged && isset($tpl['address_arr']) && !empty($tpl['address_arr']) && isset($STORAGE['s_save']))
    	{
    	    $sSaveReady = true;
    	    $sSaveChecked = true;
    	}
	?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                
                <!-- Checkout Form s-->
                <form action="<?php echo BASE_URL;?>thanhtoan" class="checkout-form">
                	<input type="hidden" name="sc_checkout" value="1">
                    <div class="row row-40">
                        
                        <div class="col-lg-7 mb-20">
                            
                            <!-- Billing Address -->
                            <div id="billing-form" class="mb-40">
                                <h4 class="checkout-title">Thông tin khách hàng</h4>

                                <div class="row">

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Email*</label>
                                        <input name="email" type="email" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['email']); ?>" readonly="readonly">
                                    </div>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mật khẩu*</label>
                                        <input name="password" type="password" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['password']); ?>" readonly="readonly">
                                    </div>

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Họ Tên*</label>
                                        <input name="client_name" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['client_name']); ?>" readonly="readonly">
                                    </div>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Số điện thoại*</label>
                                        <input name="phone" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['phone']); ?>" readonly="readonly">
                                    </div>
                                    <div class="col-md-12 col-12 mb-20">
                                        <label>Website</label>
                                        <input name="url" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['url']); ?>" readonly="readonly">
                                    </div>
									<h4 class="checkout-title">Chi tiết thanh toán</h4>
									<div class="col-12 mb-20">
                                        <label>Tên thanh toán*</label>
                                        <input name="b_name" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$billing['b_name']); ?>" readonly="readonly">
                                    </div>
                                    <div class="col-12 mb-20">
                                        <label>Địa chỉ*</label>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_b_address_1'], array(2,3))): ?>
                                        <input name="b_address_1" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$billing['b_address_1']); ?>" readonly="readonly">
                                        <?php endif;?>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_b_address_2'], array(2,3))):?>
                                        <input name="b_address_2" type="text" class="form-control" value="<?php echo SanitizeComponent::html(@$billing['b_address_2']); ?>" readonly="readonly">
                                        <?php endif;?>
                                    </div>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_b_country_id'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Quốc gia*</label>
                                        <select class="form-control required" readonly="readonly">
                                        	<option value="">Chọn quốc gia</option>
											<?php
											foreach ($tpl['country_arr'] as $country)
											{
											    ?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] != @$billing['b_country_id'] ? NULL : ' selected="selected"'; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
											}
											?>
                                        </select>
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_city'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Tỉnh/Thành phố*</label>
                                        <input name="s_city" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$shipping['s_city']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_state'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Bang*</label>
                                        <input name="s_state" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$shipping['s_state']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>
                                    
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_zip'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mã bưu cục*</label>
                                        <input name="s_zip" type="text" class="form-control required" value="<?php echo SanitizeComponent::html(@$shipping['s_zip']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>
                                    <?php if (in_array((int) $tpl['option_arr']['o_bf_notes'], array(2,3))):?>
									<div class="col-md-12 col-12 mb-20">
                                        <label>Ghi chú*</label>
                                        <textarea name="notes" class="form-control" rows="4" cols="76" readonly="readonly"><?php echo SanitizeComponent::html(@$STORAGE['notes']); ?></textarea>
                                    </div>
                                    <?php endif;?>
                                    <div class="col-12 mb-20">
                                        <div class="check-box">
                                            <input type="checkbox" id="shiping_address" readonly="readonly">
                                            <label for="shiping_address">Vận chuyển tới địa chỉ khác</label>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            
                            <!-- Shipping Address -->
                            <div id="shipping-form" class="mb-40">
                                <h4 class="checkout-title">Địa chỉ vận chuyển</h4>

                                <div class="row">
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_name'], array(2,3))):?>
                                    <div class="col-md-12 col-12 mb-20">
                                        <label>Tên*</label>
                                        <input name="s_name" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_name']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>

                                    <div class="col-12 mb-20">
                                        <label>Địa chỉ*</label>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_s_address_1'], array(2,3))):?>
                                        <input name="s_address_1" value="<?php echo SanitizeComponent::html(@$shipping['s_address_1']); ?>" type="text" readonly="readonly">
                                        <?php endif;?>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_s_address_2'], array(2,3))):?>
                                        <input name="s_address_2" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_address_2']); ?>" readonly="readonly">
                                        <?php endif;?>
                                    </div>

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Quốc gia*</label>
                                		<select name="s_country_id" class="nice-select" data-original="<?php echo SanitizeComponent::html(@$billing['s_country_id']); ?>" data-err="<?php echo $validate['country'];?>" readonly="readonly">
											<option value="">Chọn quốc gia</option>
											<?php
											foreach ($tpl['country_arr'] as $country)
											{
											    ?><option value="<?php echo $country['id']; ?>"<?php echo $country['id'] != @$shipping['s_country_id'] ? NULL : ' selected="selected"'; ?>><?php echo SanitizeComponent::html($country['name']); ?></option><?php
											}
											?>
										</select>
                                    </div>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_city'], array(2,3))): ?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Tỉnh/Thành phố*</label>
                                        <input name="s_city" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_city']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_state'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Bang*</label>
                                        <input name="s_state" value="<?php echo SanitizeComponent::html(@$shipping['s_state']); ?>" type="text" readonly="readonly">
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_zip'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mã bưu cục*</label>
                                        <input name="s_zip" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_zip']); ?>" readonly="readonly">
                                    </div>
                                    <?php endif;?>

                                </div>

                            </div>
                            
                        </div>
                        
                        <div class="col-lg-5">
                            <div class="row">
                                
                                <!-- Cart Total -->
                                <div class="col-12 mb-60">
                                
                                    <h4 class="checkout-title">Đơn hàng</h4>
                            
                                    <div class="checkout-cart-total">

                                        <h4>Sản phẩm<span>Tổng số</span></h4>
                                        
                                        <ul>
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
                				      		<li><?php echo $product['name'];?> X <?php echo (int) $cart_item['qty']; ?> <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$product_amount, $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></li>
                				      		<?php
                                    	   }
                                    	?>
                                        </ul>
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
                                        <br/>
                                        <h3>Tổng tiền: <span><?php echo UtilComponent::formatCurrencySign(UtilComponent::formatNumberByPattern((float)$price_arr['total'], $moneyFormatPattern), $tpl['option_arr']['o_currency'], " "); ?></span></h3>
                                </div>
                                <br/>
                                <!-- Payment Method -->
                                <div class="col-12">
                                
                                    <h4 class="checkout-title">Phương thức thanh toán</h4>
                            
                                    <div class="checkout-payment-method">
                                        
                                        <div class="single-method">
                                            <input type="radio" id="payment_check" name="payment-method" value="check">
                                            <label for="payment_check">Thẻ tín dụng</label>
                                            <p data-method="check">
    									    	<label class="control-label">Loại thẻ*</label>
    									    	<select name="cc_type" class="form-control required">
    									    		<option value="">---</option>
    									    		<?php
    												foreach (__('cc_types', true) as $k => $v)
    												{
    													?><option value="<?php echo $k; ?>"<?php echo @$STORAGE['cc_type'] != $k ? NULL : ' selected="selected"'; ?>><?php echo $v; ?></option><?php
    												}
    												?>
    									    	</select>
    									    	<br/>
    									    	<label class="control-label">Số thẻ*</label>
									    		<input type="text" name="cc_num" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['cc_num']); ?>" readonly="readonly" />
									    		<br/>
									    		<label class="control-label">CCV*</label>
									    		<input type="text" name="cc_code" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['cc_code']); ?>" readonly="readonly" />
									    		<br/>
									    		<label class="control-label">Hết hạn*</label>
        									    	<?php
        											$rand = rand(1, 99999);
        											$time = TimeComponent::factory()
        												->attr('name', 'cc_exp_month')
        												->attr('id', 'cc_exp_month_' . $rand)
        												->attr('class', 'form-control required')
        												->prop('format', 'F');
        											if (isset($STORAGE['cc_exp_month']) && !is_null($STORAGE['cc_exp_month']))
        											{
        												$time->prop('selected', $STORAGE['cc_exp_month']);
        											}
        											echo $time->month();
        											?>
        											<br/>
        									    	<?php
        											$time = TimeComponent::factory()
        												->attr('name', 'cc_exp_year')
        												->attr('id', 'cc_exp_year_' . $rand)
        												->attr('class', 'form-control required')
        												->prop('left', 0)
        												->prop('right', 10);
        											if (isset($STORAGE['cc_exp_year']) && !is_null($STORAGE['cc_exp_year']))
        											{
        												$time->prop('selected', $STORAGE['cc_exp_year']);
        											}
        											echo $time->year();
        											?>
                                            </p>
                                        </div>
                                        
                                        <div class="single-method">
                                            <input type="radio" id="payment_bank" name="payment-method" value="bank">
                                            <label for="payment_bank">Chuyển khoản</label>
                                            <p data-method="bank">
                                            	<?php echo $tpl['option_arr']['o_bank_account']; ?>
                                            </p>
                                        </div>
                                        
                                        <div class="single-method">
                                            <input type="radio" id="payment_cash" name="payment-method" value="cash">
                                            <label for="payment_cash">Trả tiền mặt khi nhận hàng</label>
                                        </div>
                                        
                                        <div class="single-method">
                                            <input type="radio" id="payment_paypal" name="payment-method" value="paypal">
                                            <label for="payment_paypal">Paypal</label>
                                        </div>
                                        
                                        <div class="single-method">
                                            <input type="radio" id="payment_authorize" name="payment-method" value="payoneer">
                                            <label for="payment_authorize">Authorize.net</label>
                                        </div>
                                        
                                    </div>
                                </div>
                                <br/>
                                <div class="col-12">
                                
                                    <h4 class="checkout-title">Thông tin khác</h4>
                            
                                    <div class="checkout-payment-method">
                                        <div class="single-method">
                                            <input type="checkbox" id="create_account" checked="checked">
                                            <label for="create_account">Tạo tài khoản?</label>
                                        </div>
                                        <div class="single-method">
                                            <input type="checkbox" id="accept_terms" checked="checked">
                                            <label for="accept_terms">Chấp nhận điều khoản sử dụng</label>
                                        </div>

                                    </div>
                                    
                                    <button class="place-order">Đặt hàng</button>
                                    
                                </div>
                                
                            </div>
                        </div>
                        
                    </div>
                </form>
                
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="container">
        <?php 
        if (isset($tpl['code']))
        {
            switch ($tpl['code'])
            {
                case 100:
                    ?><div class="alert alert-warning" role="alert">Vị trí vận chuyển trống</div><?php
        					break;
    			case 101:
    				?><div class="alert alert-warning" role="alert">Giỏ hàng trống</div><?php
    				break;
    		}
        }
        ?>
     </div>
    <?php endif;?>
</div>

<!--====================  End of page content  ====================-->
