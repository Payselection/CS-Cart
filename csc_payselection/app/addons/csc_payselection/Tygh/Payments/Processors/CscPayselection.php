<?php

namespace Tygh\Payments\Processors;

require_once DIR_ROOT . '/app/addons/csc_payselection/lib/SDK/autoload.php';
require_once DIR_ROOT . '/app/addons/csc_payselection/lib/MyCLabs/Enum.php';

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PaySelection\Library;
use Tygh\Enum\CscPayselectionProcessorParams;
use Tygh\Payments\Processors\Exceptions\FiscalizationException;
use Tygh\Enum\YesNo;

class CscPayselection {
    private string $site_id;
    private string $public_key;
    private string $secret_key;
    private string $payment_method;
    private string $payment_type;
    private bool $enable_fiscalization;
    private string $fiscalization_email;
    private string $sno;
    private string $inn;
    private string $payment_place;
    private array $order_info;
    private Library $apiClient;
    private string $returnUrl;
    private string $successUrl;
    private string $declineUrl;
    private string $webhookUrl;

    const WEBPAY_URL = 'https://webform.payselection.com';
    const API_URL = 'https://gw.payselection.com';
    const PAYMENT_TYPE_PAY = 'Pay';
    const PAYMENT_TYPE_BLOCK = 'Block';

    //тип оплаты безнал
    const PAYMENT_TYPE = 1;
    const PAYMENT_METHOD = 'full_prepayment';
    const PAYMENT_OBJECT_PRODUCT = 'commodity';
    const PAYMENT_OBJECT_SHIPPING = 'service';
    const VAT_NONE = 'none';


    /**
     * @throws FiscalizationException
     */
    public function __construct($processor_data, $order_info, bool $is_payment = true)
    {
        $this->enable_fiscalization = $processor_data['processor_params']['enable_fiscalization'] == YesNo::YES;
        $this->fiscalization_email = $processor_data['processor_params']['fiscalization_email'] ?? '';
        $this->sno = $processor_data['processor_params']['sno'] ?? '';
        $this->inn = $processor_data['processor_params']['inn'] ?? '';
        $this->payment_place = $processor_data['processor_params']['payment_place'] ?? '';
        $this->order_info = $order_info;

        //is_payment false для страницы заказа
        if ($is_payment && $this->enable_fiscalization && (
                empty($this->fiscalization_email) ||
                empty($this->sno ||
                empty($this->inn) ||
                empty($this->payment_place)))
        ) {
            throw new FiscalizationException(__("csc_payselection.empty_fiscalization_data"));
        }

        $this->site_id = $processor_data['processor_params']['site_id'];
        $this->public_key = $processor_data['processor_params']['public_key'];
        $this->secret_key = $processor_data['processor_params']['secret_key'];
        //page or widget
        $this->payment_method = $processor_data['processor_params']['payment_method'] ?? CscPayselectionProcessorParams::PAYMENT_METHOD_PAGE;
        //single or two-step
        $this->payment_type = $processor_data['processor_params']['payment_type'] ?? self::PAYMENT_TYPE_PAY;

        self::calculateTaxesAndDiscounts($this->order_info);
        $this->setupApiClient();
        $this->setupRedirectUrls();
    }

    private function setupApiClient():void
    {
        $this->apiClient = (new Library())
            ->setConfiguration([
                'webpay_url' => self::WEBPAY_URL,
                'api_url' => self::API_URL,
                'site_id' => $this->site_id,
                'secret_key' => $this->secret_key,
                'webhook_url' => fn_url('payment_notification.notify&payment=csc_payselection', 'C')
        ]);
    }

    private function setupRedirectUrls(): void
    {
        $this->returnUrl = fn_url('checkout.checkout');

        $this->successUrl = fn_url("payment_notification." . CscPayselectionProcessorParams::RETURN_MODE_SUCCESS . "&payment_type={$this->payment_type}&payment=csc_payselection&order_id={$this->order_info['order_id']}", 'C');

        $this->declineUrl = fn_url("payment_notification." . CscPayselectionProcessorParams::RETURN_MODE_FAIL . "&payment_type={$this->payment_type}&payment=csc_payselection&order_id={$this->order_info['order_id']}", 'C');

        $this->webhookUrl = fn_url("payment_notification." . CscPayselectionProcessorParams::RETURN_MODE_NOTIFY . "&payment_type={$this->payment_type}&payment=csc_payselection&order_id={$this->order_info['order_id']}", 'C');
    }

    /**
     * @throws GuzzleException
     */
    public function getTransactionStatus(string $transaction_id)
    {
        try {
            return $this->apiClient->getTransactionStatus($transaction_id);
        } catch (ClientException $e) {
            return '';
        }
    }

    /**
     * @throws GuzzleException
     */
    public function chargeTransaction(string $transaction_id, int $amount): void
    {
        $this->apiClient->chargePayment([
            'TransactionId' => $transaction_id,
            'Amount' => (string) $amount,
            'Currency' => 'RUB',
            'WebhookUrl' => $this->webhookUrl
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function cancelTransaction(string $transaction_id, int $amount): void
    {
        $this->apiClient->cancelPayment([
            'TransactionId' => $transaction_id,
            'Amount' => (string) $amount,
            'Currency' => 'RUB',
            'WebhookUrl' => $this->webhookUrl
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function refundTransaction(string $transaction_id, int $amount): void
    {
        $this->apiClient->createRefund([
            'TransactionId' => $transaction_id,
            'Amount' => (string) $amount,
            'Currency' => 'RUB',
            'WebhookUrl' => $this->webhookUrl,
            'ReceiptData' => $this->getReceiptData()
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function processPayment(): void
    {
        $payment = [
            'MetaData' => [
                'PaymentType' => $this->payment_type
            ],
            'PaymentRequest' => [
                'OrderId' => $this->order_info['order_id'],
                'Amount' => $this->order_info['total'],
                'Currency' => 'RUB',
                'Description' => 'Оплата заказа №' . $this->order_info['order_id'],
                'RebillFlag' => false,
                'ExtraData' => [
                    'ReturnUrl' => $this->returnUrl,
                    'SuccessUrl' => $this->successUrl,
                    'DeclineUrl' => $this->declineUrl,
                    'WebhookUrl' => $this->webhookUrl
                ],
            ],
            'CustomerInfo' => [
                'IP' => $_SERVER['REMOTE_ADDR']
            ],
        ];

        //TODO: этот стремный блок можно заменить когда пропадет ошибка на пустую строку)
        if (!empty($this->order_info['email'])) {
            $payment['CustomerInfo']['Email'] = $this->order_info['email'];
        }
        if (!empty($this->order_info['phone'])) {
            $payment['CustomerInfo']['Phone'] = self::checkPhoneFormat($this->order_info['phone']);
        }
        if (!empty($this->order_info['s_address'])) {
            $payment['CustomerInfo']['Address'] = trim($this->order_info['s_address'] . ' ' . $this->order_info['s_address_2'] ?? '');
        }
        if (!empty($this->order_info['s_zipcode'])) {
            $payment['CustomerInfo']['ZIP'] = $this->order_info['s_zipcode'];
        }

        if ($this->enable_fiscalization) {
            $payment['ReceiptData'] = $this->getReceiptData();
        }

        fn_redirect($this->apiClient->createWebPay($payment)->redirectUrl, true);
    }

    private function getReceiptData(): array
    {
        $receiptData = [
            'timestamp' => date('d.m.Y H:i:s'),
            'external_id' => uniqid(),
            'receipt' => [
                'client' => [
                    'name' => $this->order_info['firstname'] ?? ' ' . $this->order_info['lastname'] ?? '',
                    'email' => $this->order_info['email'] ?? '',
                    'phone' => self::checkPhoneFormat($this->order_info['phone']) ?? '',
                ],
                'company' => [
                    'email' => $this->fiscalization_email,
                    'sno' => $this->sno,
                    'inn' => $this->inn,
                    'payment_address' => $this->payment_place,
                ],
                'payments' => [
                    [
                        'type' => self::PAYMENT_TYPE,
                        'sum' => (float) $this->order_info['total']
                    ]
                ],
                'total' => (float) $this->order_info['total']
            ]
        ];

        //products
        foreach ($this->order_info['products'] as $product) {
            $receiptData['receipt']['items'] []= [
                'name' => $product['product'],
                'price' => (float) number_format($product['price'], 2, '.', ''),
                'quantity' => (float) $product['amount'],
                'sum' => (float) number_format($product['price'] * $product['amount'], 2, '.', ''),
                'payment_method' => self::PAYMENT_METHOD,
                'payment_object' => self::PAYMENT_OBJECT_PRODUCT,
                'vat' => [
                    'type' => $product['tax_type'] ?? self::VAT_NONE,
                ]
            ];
        }

        //shipping
        if (!empty($this->order_info['shipping'])) {
            foreach ($this->order_info['shipping'] as $shipping) {
                $shipping_vat = self::VAT_NONE;
                $shipping_tax_sum = 0;
                if (!empty($shipping['taxes'])) {
                    $tax_id = current(array_keys($shipping['taxes']));
                    if (!empty($tax_id)) {
                        $tax = fn_get_tax($tax_id);
                        $shipping_vat = $tax['tax_type'] ?? self::VAT_NONE;
                        $shipping_tax_sum = $shipping['taxes'][$tax_id]['tax_subtotal'] ?? 0;
                    }
                }
                $receiptData['receipt']['items'] []= [
                    'name' => __("shipping"),
                    'price' => (float) number_format($shipping['rate'], 2, '.', ''),
                    'quantity' => 1,
                    'sum' => (float) number_format($shipping['rate'], 2, '.', ''),
                    'payment_method' => self::PAYMENT_METHOD,
                    'payment_object' => self::PAYMENT_OBJECT_SHIPPING,
                    'vat' => [
                        'type' => $shipping_vat,
                        'sum' => (float) number_format($shipping_tax_sum, 2, '.', '')
                    ]
                ];
            }
        }

        return $receiptData;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    public function getDeclineUrl(): string
    {
        return $this->declineUrl;
    }

    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    //helpers

    public static function checkPhoneFormat(&$phone): string
    {
        if (empty($phone)) {
            return '';
        }

        return '+' . preg_replace("/[^0-9]/", '', $phone);
    }

    public static function calculateTaxesAndDiscounts(&$order_info): void
    {
        foreach ($order_info['products'] as &$product) {
            $product['tax_ids'] = explode(',', db_get_field('select tax_ids from ?:products where product_id = ?i', $product['product_id']));
            if (!empty($product['tax_ids'])) {
                $product['tax_type'] = self::getTaxType(current($product['tax_ids']));
                if (empty($product['tax_type'])) {
                    $product['tax_type'] = 'none';
                }
            }
            $_shipping_mod = $order_info['shipping_cost'] / sizeof($order_info['products']);
            if (!empty($order_info['subtotal_discount'])) {
                $discount = round(($product['subtotal'] + $_shipping_mod) / ($order_info['total'] + $order_info['subtotal_discount']) * $order_info['subtotal_discount'], 2);
                $product['subtotal'] = $product['subtotal'] - $discount;
            }
        }
    }

    public static function getTaxType($tax_id): string
    {
        return db_get_field('select tax_type from ?:taxes where tax_id = ?i', $tax_id);
    }
}