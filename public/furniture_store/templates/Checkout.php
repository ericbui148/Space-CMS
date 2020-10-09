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
                        <li>Thanh toán</li>
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
    	                    's_country_id' => !empty($address['country_id'])? $address['country_id'] : 242,
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
    	                    'b_country_id' => !empty($address['country_id'])? $address['country_id'] : 242,
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
                <form action="#" class="checkout-form" method="post">
                	<input type="hidden" name="sc_checkout" value="1">
                    <div class="row row-40">
                        
                        <div class="col-lg-7 mb-20">
                            
                            <!-- Billing Address -->
                            <div id="billing-form" class="mb-40">
                                <h4 class="checkout-title">Thông tin khách hàng</h4>

                                <div class="row">

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Email*</label>
                                        <input name="email" type="email" value="<?php echo SanitizeComponent::html(@$STORAGE['email']); ?>" placeholder="Nhập email" required>
                                    </div>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mật khẩu*</label>
                                        <input name="password" type="password" value="<?php echo SanitizeComponent::html(@$STORAGE['password']); ?>" placeholder="Nhập mật khẩu" required>
                                    </div>

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Họ Tên<?php echo (int) $tpl['option_arr']['o_bf_c_name'] === 3 ? '*' : null;?></label>
                                        <input name="client_name" type="text" value="<?php echo SanitizeComponent::html(@$STORAGE['client_name']); ?>" placeholder="Ho tên" <?php echo (int) $tpl['option_arr']['o_bf_c_name'] === 3 ? 'required' : null;?> minlength="2">
                                    </div>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Số điện thoại<?php echo (int) $tpl['option_arr']['o_bf_c_phone'] === 3 ? '*' : null;?></label>
                                        <input name="phone" type="text" value="<?php echo SanitizeComponent::html(@$STORAGE['phone']); ?>" placeholder="Số điện thoại" <?php echo (int) $tpl['option_arr']['o_bf_c_phone'] === 3 ? 'required' : null;?>>
                                    </div>
                                    <div class="col-md-12 col-12 mb-20">
                                        <label>Website<?php echo (int) $tpl['option_arr']['o_bf_c_url'] === 3 ? '*' : null;?></label>
                                        <input name="url" type="text" value="<?php echo SanitizeComponent::html(@$STORAGE['url']); ?>" placeholder="Nhập website của bạn" <?php echo (int) $tpl['option_arr']['o_bf_c_url'] === 3 ? 'required' : null;?>>
                                    </div>
									<h4 class="checkout-title">Chi tiết thanh toán</h4>
									<div class="col-12 mb-20">
                                        <label>Tên thanh toán<?php echo (int) $tpl['option_arr']['o_bf_c_name'] === 3 ? '*' : null;?></label>
                                        <input name="b_name" type="text" value="<?php echo SanitizeComponent::html(@$billing['b_name']); ?>" placeholder="Nhập tên thanh toán của bạn" <?php echo (int) $tpl['option_arr']['o_bf_c_name'] === 3 ? 'required' : null;?> minlength="2">
                                    </div>
                                    <div class="col-12 mb-20">
                                        <label>Địa chỉ<?php echo (int) $tpl['option_arr']['o_bf_b_address_1'] === 3 || (int) $tpl['option_arr']['o_bf_b_address_2'] === 3 ? '*' : null;?></label>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_b_address_1'], array(2,3))): ?>
                                        <input name="b_address_1" type="text" value="<?php echo SanitizeComponent::html(@$billing['b_address_1']); ?>" placeholder="Địa chỉ dòng 1" <?php echo (int) $tpl['option_arr']['o_bf_b_address_1'] === 3 ? 'required' : null;?>>
                                        <?php endif;?>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_b_address_2'], array(2,3))):?>
                                        <input name="b_address_2" type="text" value="<?php echo SanitizeComponent::html(@$billing['b_address_2']); ?>" placeholder="Địa chỉ dòng 2" <?php echo (int) $tpl['option_arr']['o_bf_b_address_2'] === 3 ? 'required' : null;?>>
                                        <?php endif;?>
                                    </div>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_b_country_id'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Quốc gia<?php echo (int) $tpl['option_arr']['o_bf_b_country_id'] === 3 ? '*' : NULL; ?></label>
                                        <select name="b_country_id" class="form-control" data-original="<?php echo SanitizeComponent::html(@$billing['b_country_id']); ?>" <?php echo (int) $tpl['option_arr']['o_bf_b_country_id'] === 3 ? 'required' : NULL; ?>>
                                        	<option value="default">Chọn quốc gia</option>
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
                                        <label>Tỉnh/Thành phố<?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? '*' : null;?></label>
                                        <input name="b_city" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_city']); ?>" placeholder="Tỉnh/Thành phố" <?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? 'required' : null;?>>
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_state'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Bang<?php echo (int) $tpl['option_arr']['o_bf_s_state'] === 3 ? '*' : null;?></label>
                                        <input name="b_state" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_state']); ?>" placeholder="Bang" <?php echo (int) $tpl['option_arr']['o_bf_s_state'] === 3 ? 'required' : null;?>>
                                    </div>
                                    <?php endif;?>
                                    
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_zip'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mã bưu cục<?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? '*' : null;?></label>
                                        <input name="b_zip" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_zip']); ?>" placeholder="Mã bưu cục" <?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? 'required' : null;?>>
                                    </div>
                                    <?php endif;?>
                                    <?php if (in_array((int) $tpl['option_arr']['o_bf_notes'], array(2,3))):?>
									<div class="col-md-12 col-12 mb-20">
                                        <label>Ghi chú<?php echo (int) $tpl['option_arr']['o_bf_notes'] === 3 ? '*' : null;?></label>
                                        <textarea name="notes" rows="4" cols="76" <?php echo (int) $tpl['option_arr']['o_bf_notes'] === 3 ? 'required' : null;?>><?php echo SanitizeComponent::html(@$STORAGE['notes']); ?></textarea>
                                    </div>
                                    <?php endif;?>
                                    <div class="col-12 mb-20">
                                        <div class="check-box">
                                            <input type="checkbox" id="shiping_address" data-shipping>
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
                                        <input name="s_name" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_name']); ?>" placeholder="Nhập tên vận chuyển" <?php echo (int) $tpl['option_arr']['o_bf_s_name'] === 3 ? 'required' : null;?> minlength="2">
                                    </div>
                                    <?php endif;?>

                                    <div class="col-12 mb-20">
                                        <label>Địa chỉ<?php echo (int) $tpl['option_arr']['o_bf_s_address_1'] === 3 ? '*' : NULL; ?></label>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_s_address_1'], array(2,3))):?>
                                        <input name="s_address_1" value="<?php echo SanitizeComponent::html(@$shipping['s_address_1']); ?>" type="text" placeholder="Địa chỉ dòng 1" <?php echo (int) $tpl['option_arr']['o_bf_s_address_1'] === 3 ? 'required' : NULL; ?>>
                                        <?php endif;?>
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_s_address_2'], array(2,3))):?>
                                        <input name="s_address_2" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_address_2']); ?>" placeholder="Địa chỉ dòng 2" <?php echo (int) $tpl['option_arr']['o_bf_s_address_2'] === 3 ? 'required' : null;?>>
                                        <?php endif;?>
                                    </div>

                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Quốc gia<?php echo (int) $tpl['option_arr']['o_bf_s_country_id'] === 3 ? '*' : NULL; ?></label>
                                		<select name="s_country_id" class="form-control" data-original="<?php echo SanitizeComponent::html(@$billing['s_country_id']); ?>" <?php echo (int) $tpl['option_arr']['o_bf_s_country_id'] === 3 ? 'required' : NULL; ?>>
											<option value="default">Chọn quốc gia</option>
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
                                        <label>Tỉnh/Thành phố<?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? '*' : null;?></label>
                                        <input name="s_city" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_city']); ?>" placeholder="Tỉnh/Thành phố" <?php echo (int) $tpl['option_arr']['o_bf_s_city'] === 3 ? '1' : null;?>>
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_state'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Bang<?php echo (int) $tpl['option_arr']['o_bf_s_state'] === 3 ? '*' : null;?></label>
                                        <input name="s_state" value="<?php echo SanitizeComponent::html(@$shipping['s_state']); ?>" type="text" placeholder="Bang" <?php echo (int) $tpl['option_arr']['o_bf_s_state'] === 3 ? '1' : null;?>>
                                    </div>
                                    <?php endif;?>
									<?php if (in_array((int) $tpl['option_arr']['o_bf_s_zip'], array(2,3))):?>
                                    <div class="col-md-6 col-12 mb-20">
                                        <label>Mã bưu cục<?php echo (int) $tpl['option_arr']['o_bf_s_zip'] === 3 ? '*' : null;?></label>
                                        <input name="s_zip" type="text" value="<?php echo SanitizeComponent::html(@$shipping['s_zip']); ?>" placeholder="Mã bưu cục" <?php echo (int) $tpl['option_arr']['o_bf_s_zip'] === 3 ? '1' : null;?>>
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
										
										<?php if ((int) $tpl['option_arr']['o_allow_creditcard'] === 1):?>
										<?php 
    										if (empty(@$STORAGE['payment_method'])) {
    										    $STORAGE['payment_method'] = 'bank';
										    }
										 ?>
                                        <div class="single-method">
                                            <input type="radio" id="payment_check" name="payment_method" value="creditcard"<?php echo @$STORAGE['payment_method'] == 'creditcard'? ' checked="checked"' : NULL;?>>
                                            <label for="payment_check">Thẻ tín dụng</label>
                                            <p data-method="creditcard">
    									    	<label class="control-label">Loại thẻ*</label>
    									    	<select name="cc_type" class="form-control required">
    									    		<option value="">Chọn kiểu</option>
    									    		<?php
    												foreach (__('cc_types', true) as $k => $v)
    												{
    													?><option value="<?php echo $k; ?>"<?php echo @$STORAGE['cc_type'] != $k ? NULL : ' selected="selected"'; ?>><?php echo $v; ?></option><?php
    												}
    												?>
    									    	</select>
    									    	<br/>
    									    	<label class="control-label">Số thẻ*</label>
									    		<input type="text" name="cc_num" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['cc_num']); ?>" placeholder="Nhập số thẻ" />
									    		<br/>
									    		<label class="control-label">CCV*</label>
									    		<input type="text" name="cc_code" class="form-control required" value="<?php echo SanitizeComponent::html(@$STORAGE['cc_code']); ?>" placeholder="<?php __('front_placeholder_cc_code', false, true); ?>" />
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
										<?php endif;?>

										<?php if ((int) $tpl['option_arr']['o_allow_bank'] === 1):?>
                                        <div class="single-method">
                                            <input type="radio" id="payment_bank" name="payment_method" value="bank"<?php echo @$STORAGE['payment_method'] == 'bank'? ' checked="checked"' : NULL;?>>
                                            <label for="payment_bank">Chuyển khoản</label>
                                            <p data-method="bank">
                                            	<?php echo $tpl['option_arr']['o_bank_account']; ?>
                                            </p>
                                        </div>
                                        <?php endif;?>
										
										<?php if ((int) $tpl['option_arr']['o_allow_cod'] === 1):?>
                                        <div class="single-method">
                                            <input type="radio" id="payment_cash" name="payment_method" value="cod"<?php echo @$STORAGE['payment_method'] == 'cod'? ' checked="checked"' : NULL;?>>
                                            <label for="payment_cash">Trả tiền mặt khi nhận hàng</label>
                                        </div>
                                        <?php endif;?>

										<?php if ((int) $tpl['option_arr']['o_allow_paypal'] === 1):?>
                                        <div class="single-method">
                                            <input type="radio" id="payment_paypal" name="payment_method" value="paypal"<?php echo @$STORAGE['payment_method'] == 'paypal'? ' checked="checked"' : NULL;?>>
                                            <label for="payment_paypal">Paypal</label>
                                        </div>
                                        <?php endif;?>

										<?php if ((int) $tpl['option_arr']['o_allow_authorize'] === 1):?>
                                        <div class="single-method">
                                            <input type="radio" id="payment_authorize" name="payment_method" value="authorize"<?php echo @$STORAGE['payment_method'] == 'authorize'? ' checked="checked"' : NULL;?>>
                                            <label for="payment_authorize">Authorize.net</label>
                                        </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                                <br/>
                                <div class="col-12">
                                
                                    <h4 class="checkout-title">Thông tin khác</h4>
                            
                                    <div class="checkout-payment-method">
                                        <?php if (in_array((int) $tpl['option_arr']['o_bf_captcha'], array(3))):?>
                                        <div class="single-method">
                                            <div class="form-group<?php echo (int) $tpl['option_arr']['o_bf_captcha'] === 3 ? ' required' : null;?>">
            								  	<label class="control-label">Mã Captcha*</label>
            									<div class="row">
            									  	<div class="col-xs-6 col-md-6">
            									    	<input type="text" name="captcha" class="form-control" maxlength="6" <?php echo (int) $tpl['option_arr']['o_bf_captcha'] === 3 ? 'required' : NULL; ?>>
            									  	</div>
            									  	<div class="col-xs-6">
            									    	<img src="<?php echo BASE_URL; ?>index.php?controller=BaseShopCart&amp;action=Captcha&amp;rand=<?php echo rand(1, 99999); ?>" alt="Captcha" style="vertical-align: middle" />
            									  	</div>
            									</div>
            								</div>
                                        </div>
                                        <img id="loading-image" src="<?php echo BASE_URL;?>public/heaven_garden/assets/img/ajax-loader.gif" style="display:none;"/>
                                        <?php endif;?>
                                        <div class="single-method">
											<input type="checkbox" id="create_account" class="form-control" name="create_account">
											<label for="create_account">Tạo tài khoản?</label>
                                        </div>
                                        <div class="single-method">
											<input type="checkbox" name="terms" class="form-control" id="accept_terms" disabled="disabled" checked="checked">
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
<?php 
$validateMessages = [
	'captcha' => 'Chưa nhập captcha',
	'password' => 'Chưa nhập password',
	'name' => 'Chưa nhập tên',
	'name_minlength' => 'Tên phải tối thiểu 2 ký tự',
	'email' => 'Chưa nhập email',
	'email_invalid' => 'Email chưa hợp lệ',
	'phone' => 'Chưa nhập số điện thoại',
	'captcha_wrong' => 'Captcha chưa chính xác',
	'voucher' => 'Chưa nhập mã voucher',
	'tax' => 'Thuế là trống',
	'country' => 'Chưa chọn quốc gia',
	'city' => 'Chưa nhập city',
	'state' => 'Chưa nhập bang',
	'zip' => 'Chưa nhập mã bưu cục',
	'address_1' => 'Địa chỉ dòng 1 trống',
	'address_2' => 'Địa chỉ dòng 2 trống',
	'notes' => 'Chưa nhập ghi chú',
	'payment' => 'Chưa chọn phương thức thanh toán',
	'terms' => 'Chưa chấp nhận điều khoản sử dụng',
	'url' => 'Chưa nhập url',
	'cc_code' => 'Chưa nhập mã',
	'cc_num' => 'Chưa nhập số thẻ',
	'cc_type' => 'Chon chọn kiểu'
];
?>
<script type="text/javascript">
	var $checkout_validate = <?php echo json_encode($validateMessages, true);?>
</script>

