<?php

use Tygh\Enum\PayselectionTransactionStatuses;
use Tygh\Payments\Processors\CscPayselection;
use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */

if ($mode == 'details') {
    $order_info = fn_get_order_info($_REQUEST['order_id']);
    if (!empty($order_info['payment_method']['processor']) && $order_info['payment_method']['processor'] == PAYSELECTION_PROCESSOR_NAME && !empty($order_info['payment_info']['transaction_id'])) {
        $payselection = new CscPayselection($order_info['payment_method'], $order_info, false);
        $status = $payselection->getTransactionStatus($order_info['payment_info']['transaction_id']);

        Tygh::$app['view']->assign('payselection_status', $status);
        if (!empty(PayselectionTransactionStatuses::STATUS_LIST[$status->transactionState])) {
            Tygh::$app['view']->assign('payselection_actions', PayselectionTransactionStatuses::STATUS_LIST[$status->transactionState]['allowed_actions']);
        }
    }
}