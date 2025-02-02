<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
?>
<div class="main container one-column">
	<div class="col-main">
		<?= Yii::$service->page->widget->render('flashmessage'); ?>

		<form action="<?= Yii::$service->url->getCurrentUrl(); ?>" method="post" id="onestepcheckout-form">
			<?= \fec\helpers\CRequest::getCsrfInputHtml(); ?>
			<div style="margin: 0;" class="group-select">
				<p class="onestepcheckout-description"><?= Yii::$service->page->translate->__('Welcome to the checkout,Fill in the fields below to complete your purchase');?> !</p>
				
				<div class="onestepcheckout-threecolumns checkoutcontainer onestepcheckout-skin-generic onestepcheckout-enterprise">
					<div class="onestepcheckout-column-left">
						<?php # address 部门
							//var_dump($address_list);
							$addressParam = [
								'cart_address_id' 	=> $cart_address_id,
								'address_list'	  	=> $address_list,
								'customer_info'	  	=> $customer_info,
								'country_select'  	=> $country_select,
								'state_html'  	  	=> $state_html,
								'cart_address'		=> $cart_address,
								//'payments' => $payments,
								//'current_payment_mothod' => $current_payment_mothod,
							];
						?>
                        <?= Yii::$service->page->widget->render('payment/paypal_express_address',$addressParam); ?>
					</div>

					<div class="onestepcheckout-column-middle">
						<div class="shipping_method_html">
                            <?= Yii::$service->page->widget->render('payment/paypal_express_shipping', ['shippings' => $shippings]); ?>
						</div>
					
						<div class="onestepcheckout-coupons">
							<div style="display: none;" id="coupon-notice"></div>
							<div class="op_block_title"><?= Yii::$service->page->translate->__('Coupon codes (optional)');?></div>
							<label for="id_couponcode"><?= Yii::$service->page->translate->__('Enter your coupon code if you have one.');?></label>
							
							<input type="hidden" class="couponType"  value="<?= $cart_info['coupon_code'] ? 1 : 2 ; ?>"  />
							<input style="color:#777;" class="input-text" id="id_couponcode" name="coupon_code" value="<?= $cart_info['coupon_code']; ?>">
							<br>
							<button style="" type="button" class="submitbutton add_coupon_submit" id="onestepcheckout-coupon-add"><?= Yii::$service->page->translate->__($cart_info['coupon_code'] ? 'Cancel Coupon' : 'Add Coupon') ; ?></button>
							<div class="clear"></div>
							<div class="coupon_add_log"></div>
						</div>
						
						<div class="onestepcheckout-coupons">
							<div class="op_block_title"><?= Yii::$service->page->translate->__('Order Remark (optional)');?></div>
							<label for="id_couponcode"><?= Yii::$service->page->translate->__('You can fill in the order remark information below');?></label>
							<textarea class="order_remark" name="order_remark" style="width:100%;height:100px;padding:10px;"></textarea>
						</div>
					</div>

					<div class="onestepcheckout-column-right">
						<div class="review_order_view">
							<?php # review order部分
								$reviewOrderParam = [
									'cart_info' => $cart_info,
									'currency_info' => $currency_info,
								];
							?>
							<?= Yii::$service->page->widget->render('payment/paypal_express_orderview', $reviewOrderParam); ?>
						</div>
						<div class="onestepcheckout-place-order">
							<a class="large orange onestepcheckout-button" href="javascript:void(0)" id="onestepcheckout-place-order"><?= Yii::$service->page->translate->__('Place order now');?></a>
							<div class="onestepcheckout-place-order-loading"><img src="<?= Yii::$service->image->getImgUrl('images/opc-ajax-loader.gif'); ?>">&nbsp;&nbsp;<?= Yii::$service->page->translate->__('Please wait, processing your order...');?></div>
						</div>
					</div>
					<div style="clear: both;">&nbsp;</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
<?php $this->beginBlock('placeOrder') ?>
	function validateEmail(email) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}
	// ajax
	function ajaxreflush(){
		shipping_method = $("input[name=shipping_method]:checked").val();
		//alert(shipping_method);
		country = $(".billing_country").val();
		address_id = $(".address_list").val();
		state   = $(".address_state").val();
		//alert(state);
		if(country || address_id){
			$(".onestepcheckout-summary").html('<div style="text-align:center;min-height:40px;"><img src="<?= Yii::$service->image->getImgUrl('images/ajax-loader.gif'); ?>"  /></div>');
			$(".onestepcheckout-shipping-method-block").html('<div style="text-align:center;min-height:40px;"><img src="<?= Yii::$service->image->getImgUrl('images/ajax-loader.gif'); ?>"  /></div>');
				
			ajaxurl = "<?= Yii::$service->url->getUrl('checkout/onepage/ajaxupdateorder');  ?>";
			
			
			$.ajax({
				async:false,
				timeout: 8000,
				dataType: 'json', 
				type:'get',
				data: {
						'country':country,
						'shipping_method':shipping_method,
						'address_id':address_id,
						'state':state,
						},
				url:ajaxurl,
				success:function(data, textStatus){ 
					status = data.status;
					if(status == 'success'){
						$(".review_order_view").html(data.reviewOrderHtml)
						$(".shipping_method_html").html(data.shippingHtml);
					
					}
						
				},
				error:function (XMLHttpRequest, textStatus, errorThrown){
						
				}
			});
		}
	}	
	$(document).ready(function(){
		currentUrl = "<?= Yii::$service->url->getUrl('checkout/onepage') ?>"
		//优惠券
		$(".add_coupon_submit").click(function(){
			coupon_code = $("#id_couponcode").val();
			coupon_type = $(".couponType").val();
			coupon_url = "";
			$succ_coupon_type = 0;
			if(coupon_type == 2){
				coupon_url = "<?=  Yii::$service->url->getUrl('checkout/cart/addcoupon'); ?>";
				$succ_coupon_type = 1;
			}else if(coupon_type == 1){
				coupon_url = "<?=  Yii::$service->url->getUrl('checkout/cart/cancelcoupon'); ?>";
				$succ_coupon_type = 2;
			}
			//alert(coupon_type);
			if(!coupon_code){
				//alert("coupon can not empty!");
			}
			//coupon_url = $("#discount-coupon-form").attr("action");
			//alert(coupon_url);
			$.ajax({
				async:true,
				timeout: 6000,
				dataType: 'json', 
				type:'post',
				data: {"coupon_code":coupon_code},
				url:coupon_url,
				success:function(data, textStatus){ 
					if(data.status == 'success'){
						$(".couponType").val($succ_coupon_type);
						hml = $('.add_coupon_submit').html();
						if(hml == 'Add Coupon'){
							$('.add_coupon_submit').html('<?= Yii::$service->page->translate->__('Cancel Coupon');?>');
						}else{
							$('.add_coupon_submit').html('<?= Yii::$service->page->translate->__('Add Coupon');?>');
						}
						$(".coupon_add_log").html("");
						ajaxreflush();
					}else if(data.content == 'nologin'){
						$(".coupon_add_log").html("<?= Yii::$service->page->translate->__('you must login your account before you use coupon');?>");
					}else{
						$(".coupon_add_log").html(data.content);
					}
				},
				error:function (XMLHttpRequest, textStatus, errorThrown){}
			});
			
		});
		
		// 对于非登录用户，可以填写密码，进行注册账户，这里进行信息的检查。
		$("#id_create_account").click(function(){
			if($(this).is(':checked')){
				email = $("input[name='billing[email]']").val();
				if(!email){
					$(this).prop('checked', false);
					$(".label_create_account").html(" <?= Yii::$service->page->translate->__('email address is empty, you must Fill in email');?>");
				}else{
					thischeckbox = this;
					if(!validateEmail(email)){
						$(this).prop('checked', false);
						$(".label_create_account").html(" <?= Yii::$service->page->translate->__('email address format is incorrect');?>");
						
					}else{
						// ajax  get if  email is register
						$.ajax({
							async:true,
							timeout: 6000,
							dataType: 'json', 
							type:'get',
							data: {"email":email},
							url:"<?= Yii::$service->url->getUrl('customer/ajax/isregister'); ?>",
							success:function(data, textStatus){ 
								if(data.registered == 2){
									$(".label_create_account").html("");
									$("#onestepcheckout-li-password").show();
									$("#onestepcheckout-li-password input").addClass("required-entry");
					
								}else{
									$(thischeckbox).prop('checked', false);
									$(".label_create_account").html(" <?= Yii::$service->page->translate->__('This email is registered , you must fill in another email');?>");
								}
							},
							error:function (XMLHttpRequest, textStatus, errorThrown){}
						});
					}
				}
			}else{
				$(".label_create_account").html("");
				$("#onestepcheckout-li-password").hide();
				$("#onestepcheckout-li-password input").removeClass("required-entry");
			}
		});
		//###########################
		//下单(这个部分未完成。)
		$("#onestepcheckout-place-order").click(function(){
			$(".validation-advice").remove();
			i = 0;
			j = 0;
			address_list = $(".address_list").val();
			// shipping
			shipment_method = $(".onestepcheckout-shipping-method-block input[name='shipping_method']:checked").val();
			//alert(shipment_method);
			if(!shipment_method){
				$(".shipment-methods").after('<div style=""  class="validation-advice"><?= Yii::$service->page->translate->__('This is a required field.');?></div>');
				j = 1;
			}
			
			$("#onestepcheckout-form .required-entry").each(function(){
				value = $(this).val();
				if(!value){
					i++;
					$(this).after('<div style=""  class="validation-advice"><?= Yii::$service->page->translate->__('This is a required field.');?></div>');
				}
			});
			user_email = $("#billing_address .validate-email").val();
			if(user_email && !validateEmail(user_email)){
				$("#billing_address .validate-email").after('<div style=""  class="validation-advice"><?= Yii::$service->page->translate->__('email address format is incorrect');?></div>');
				i++;
			}
			
			if(!i && !j){
				//alert(333);
				$(".onestepcheckout-place-order").addClass('visit');
				$("#onestepcheckout-form").submit();
			}
			
			
		});
		
		// 国家选择后，state需要清空，重新选择或者填写
		$(".billing_country").change(function(){
			country = $(this).val();
			//state   = $(".address_state").val();
			//shipping_method = $("input[name=shipping_method]:checked").val();
			//alert(shipping_method);
			
			//$(".onestepcheckout-shipping-method-block").html('<div style="text-align:center;min-height:40px;"><img src="http://www.intosmile.com/skin/default/images/ajax-loader.gif"  /></div>');
			//$(".onestepcheckout-summary").html('<div style="text-align:center;min-height:40px;"><img src="http://www.intosmile.com/skin/default/images/ajax-loader.gif"  /></div>');
			ajaxurl = "<?= Yii::$service->url->getUrl('checkout/onepage/changecountry'); ?>";
			
			$.ajax({
				async:true,
				timeout: 8000,
				dataType: 'json', 
				type:'get',
				data: {
						'country':country,
						//'shipping_method':shipping_method,
						//'state':state
						},
				url:ajaxurl,
				success:function(data, textStatus){ 
					$(".state_html").html(data.state);
					
				},
				error:function (XMLHttpRequest, textStatus, errorThrown){
						
				}
			});
			ajaxreflush();	
		});
		
		// state select 改变后的事件
		$(".input-state").off("change").on("change","select.address_state",function(){
			ajaxreflush();
		});
		// state input 改变后的事件
		$(".input-state").off("blur").on("blur","input.address_state",function(){
			ajaxreflush();
		});
		
		//改变shipping methos
		$(".onestepcheckout-column-middle").off("click").on("click","input[name=shipping_method]",function(){
			ajaxreflush();
		});
		
	});	
	//ajaxreflush();
<?php $this->endBlock(); ?> 
<?php $this->registerJs($this->blocks['placeOrder'],\yii\web\View::POS_END);//将编写的js代码注册到页面底部 ?>

</script>
    

	