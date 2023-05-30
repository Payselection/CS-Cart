<?php

use GuzzleHttp\Exception\GuzzleException;
use Tygh\Payments\Processors\CscPayselection;
use Tygh\Enum\NotificationSeverity;

/** @var string $mode */

if (empty($_REQUEST['order_id'])) {
    die('Access denied');
}

$order_info = fn_get_order_info($_REQUEST['order_id']);
if (empty($order_info['payment_method']['processor']) || $order_info['payment_method']['processor'] != PAYSELECTION_PROCESSOR_NAME || empty($order_info['payment_info']['transaction_id'])) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (in_array($mode, ['charge', 'cancel', 'refund'])) {
        $method = "{$mode}Transaction";
        $hash = "payselection_amount_{$mode}";
        if (empty($_REQUEST[$hash]) || !is_numeric($_REQUEST[$hash])) {
            fn_set_notification(NotificationSeverity::ERROR, __("error"), __("csc_payselection.error_order_amount"));
        } else {
            $payselection = new CscPayselection($order_info['payment_method'], $order_info, false);
            try {
                $payselection->$method($order_info['payment_info']['transaction_id'], $_REQUEST[$hash]);
                fn_set_notification(NotificationSeverity::NOTICE, __("successful"), __("csc_payselection.status_changed"));
            } catch (GuzzleException $e) {
                fn_set_notification(NotificationSeverity::ERROR, __("error"), __("csc_payselection.error_changing_status"));
            }
        }
    }
}

return [CONTROLLER_STATUS_OK, 'orders.details?order_id=' . $_REQUEST['order_id']];