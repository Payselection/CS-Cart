{if isset($smarty.request.payselection_widget)}
    {$processor_params = unserialize($payment.processor_params)}
    {$order_info = $smarty.session.payselection_order_info}
    {Tygh\Payments\Processors\CscPayselection::calculateTaxesAndDiscounts($order_info)}

    <script type="text/javascript">
     var widget = new pw.PayWidget();
     var config = {
         serviceId: "{$processor_params.site_id}",
         key: "{$processor_params.public_key}",
         logger:true,
     };
     var pay_obj = {
        MetaData: {
            PaymentType: "{$processor_params.payment_type}",
        },
        PaymentRequest: {
            OrderId: "{$order_info.order_id}",
            Amount: "{$order_info.total}",
            Currency: "RUB",
            Description: "Оплата заказа №{$order_info.order_id}",
            RebillFlag: false,
            ExtraData: {
               ReturnUrl: "{$payselection_obj->getReturnUrl() nofilter}",
               SuccessUrl: "{$payselection_obj->getSuccessUrl() nofilter}",
               DeclineUrl: "{$payselection_obj->getDeclineUrl() nofilter}",
               WebhookUrl: "{$payselection_obj->getWebhookUrl() nofilter}",
            }
        },
        CustomerInfo: {
            IP: "{$smarty.server.REMOTE_ADDR}"
        }
     };

     {if $processor_params.enable_fiscalization == Tygh\Enum\YesNo::YES}
        var ps_products = [
            {foreach $order_info.products as $product}
                {
                    name: "{$product.product}",
                    price: {number_format($product.price, 2, '.', '')},
                    quantity: {$product.amount},
                    sum: {number_format($product.price * $product.amount, 2, '.', '')},
                    payment_method: "{Tygh\Payments\Processors\CscPayselection::PAYMENT_METHOD}",
                    payment_object: "{Tygh\Payments\Processors\CscPayselection::PAYMENT_OBJECT_PRODUCT}",
                    vat: {
                        type: "{if !empty($product.tax_type)}{$product.tax_type}{else}{Tygh\Payments\Processors\CscPayselection::VAT_NONE}{/if}",
                    }
                },
            {/foreach}
        ];
        {if !empty($order_info.shipping)}
            {foreach $order_info.shipping as $shipping}
                {$shipping_vat = Tygh\Payments\Processors\CscPayselection::VAT_NONE}
                {$shipping_tax_sum = 0}
                {if !empty($shipping.taxes)}
                    {$tax_id = current(array_keys($shipping.taxes))}
                    {if !empty($tax_id)}
                        {$tax = fn_get_tax($tax_id)}
                        {if !empty($tax.tax_type)}
                            {$shipping_vat = $tax.tax_type}
                        {else}
                            {$shipping_vat = Tygh\Payments\Processors\CscPayselection::VAT_NONE}
                        {/if}

                        {if !empty($shipping['taxes'][$tax_id]['tax_subtotal'])}
                            {$shipping_tax_sum = $shipping.taxes.$tax_id.tax_subtotal};
                        {else}
                            {$shipping_tax_sum = 0};
                        {/if}
                    {/if}
                {/if}
                ps_products.push({
                    name: "{__("shipping")}",
                    price: {number_format($shipping.price, 2, '.', '')},
                    quantity: 1,
                    sum: {number_format($shipping.price, 2, '.', '')},
                    payment_method: "{Tygh\Payments\Processors\CscPayselection::PAYMENT_METHOD}",
                    payment_object: "{Tygh\Payments\Processors\CscPayselection::PAYMENT_OBJECT_SHIPPING}",
                    vat: {
                        type: "{$shipping_vat}",
                        sum: {number_format($shipping_tax_sum, 2, '.', '')},
                    }
                });
            {/foreach}
        {/if}
        pay_obj['ReceiptData'] = {
            timestamp: "{date('d.m.Y H:i:s')}",
            external_id: "{uniqid()}",
            receipt: {
                client: {
                    name: "{"`$order_info.firstname` `$order_info.lastname`"}",
                    email: "{$order_info.email}",
                    phone: "{Tygh\Payments\Processors\CscPayselection::checkPhoneFormat($order_info.phone)}",
                },
                company: {
                    email: "{$processor_params.fiscalization_email}",
                    sno: "{$processor_params.sno}",
                    inn: "{$processor_params.inn}",
                    payment_address: "{$processor_params.payment_place}",
                },
                payments: [
                    {
                        type: {Tygh\Payments\Processors\CscPayselection::PAYMENT_TYPE},
                        sum: {$order_info.total}
                    }
                ],
                items: ps_products,
                total: {$order_info.total}
            }
        };
     {/if}

     var callback_obj = {
        onSuccess: function(res) {
            window.location.href = res.returnUrl;
        },
        onError: function(res) {
            window.location.href = res.returnUrl;
        },
     };

     console.log(config, pay_obj)
     widget.pay(config, pay_obj, callback_obj);
    </script>
{/if}