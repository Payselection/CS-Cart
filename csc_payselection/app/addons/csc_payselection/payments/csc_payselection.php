<?php

use GuzzleHttp\Exception\GuzzleException;
use Tygh\Enum\CscPayselectionProcessorParams;
use Tygh\Payments\Processors\CscPayselection;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\CscOrderStatuses as OrderStatuses;
use Tygh\Payments\Processors\Exceptions\FiscalizationException;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode **/
if (defined('PAYMENT_NOTIFICATION')) {
    if (empty($_REQUEST['order_id']) || !fn_check_payment_script('csc_payselection.php', $_REQUEST['order_id'])) {
        die('Access denied');
    }

    if (isset($_SESSION['payselection_order_info'])) {
        unset($_SESSION['payselection_order_info']);
    }
    $order_id = $_REQUEST['order_id'];
    $order_info = fn_get_order_info($order_id);
    $pp_response = [];
    $act = '';

    switch ($mode) {
        case CscPayselectionProcessorParams::RETURN_MODE_SUCCESS:
            if (!empty($_REQUEST['payment_type'])) {
                if ($_REQUEST['payment_type'] == CscPayselection::PAYMENT_TYPE_PAY) {
                    fn_order_placement_routines('route', $order_id, false);
                } elseif ($_REQUEST['payment_type'] == CscPayselection::PAYMENT_TYPE_BLOCK) {
                    fn_set_notification(NotificationSeverity::NOTICE, __("successful"), __("csc_payselection.customer_transaction_waiting_approval"));
                    fn_order_placement_routines('route', $order_id, false);
                }
            }
            break;
        case CscPayselectionProcessorParams::RETURN_MODE_FAIL:
            fn_set_notification(NotificationSeverity::ERROR, __("fail"), __("fail"));
            fn_order_placement_routines('checkout_redirect', $order_id, false);
            break;
        case CscPayselectionProcessorParams::RETURN_MODE_NOTIFY:
            $data = json_decode(file_get_contents('php://input'));
            if (!empty($data)) {
                $order_id = $data->OrderId;
                $pp_response['transaction_id'] = $data->TransactionId;

                switch ($data->Event) {
                    case CscPayselectionProcessorParams::WEBHOOK_PAYMENT:
                        $act = CscPayselectionProcessorParams::PROCESSOR_ACTION_FINISH;
                        $pp_response['order_status'] = OrderStatuses::PAID;
                        $pp_response['reason_text'] = __('approved') . ' (' . fn_format_price($data->Amount) . '₽)';
                        break;
                    case CscPayselectionProcessorParams::WEBHOOK_FAIL:
                        $act = CscPayselectionProcessorParams::PROCESSOR_ACTION_NULL;
                        $pp_response['order_status'] = OrderStatuses::FAILED;
                        $pp_response['reason_text'] = __('fail');
                        break;
                    case CscPayselectionProcessorParams::WEBHOOK_CANCEL:
                        $act = CscPayselectionProcessorParams::PROCESSOR_ACTION_FINISH;
                        $pp_response['order_status'] = OrderStatuses::CANCELED;
                        $pp_response['reason_text'] = __('cancelled') . ' (' . fn_format_price($data->Amount) . '₽)';
                        break;
                    case CscPayselectionProcessorParams::WEBHOOK_REFUND:
                        $act = CscPayselectionProcessorParams::PROCESSOR_ACTION_SAVE_ORDER;
                        $pp_response['order_status'] = OrderStatuses::REFUNDED;
                        $pp_response['reason_text'] = __('cancelled') . ' (' . fn_format_price($data->Amount) . '₽)';
                        break;
                    case CscPayselectionProcessorParams::WEBHOOK_BLOCK:
                        if ($order_info['status'] == OrderStatuses::INCOMPLETED) {
                            $act = CscPayselectionProcessorParams::PROCESSOR_ACTION_SAVE_ORDER;
                            $pp_response['order_status'] = OrderStatuses::OPEN;
                            $pp_response['reason_text'] = __('csc_payselection.transaction_waiting_approval');
                        }
                        break;
                }

                if (!empty($pp_response)) {
                    if ($act == CscPayselectionProcessorParams::PROCESSOR_ACTION_FINISH) {
                        fn_finish_payment($order_id, $pp_response);
                    } elseif ($act == CscPayselectionProcessorParams::PROCESSOR_ACTION_SAVE_ORDER) {
                        if (!empty($pp_response['order_status'])) {
                            fn_change_order_status($order_id, $pp_response['order_status']);
                        }
                        fn_update_order_payment_info($order_id, $pp_response);
                    } elseif ($act == CscPayselectionProcessorParams::PROCESSOR_ACTION_NULL) {
                        if (!empty($pp_response['order_status'])) {
                            fn_change_order_status($order_id, $pp_response['order_status']);
                        }
                        fn_update_order_payment_info($order_id, $pp_response);
                    }
                }
            }
            break;
    }

    return [201];
}
else {
    /** @var array $order_info **/
    /** @var array $processor_data **/
    if ($processor_data['processor_params']['payment_method'] == CscPayselectionProcessorParams::PAYMENT_METHOD_PAGE) {
        try {
            $paySelection = new CscPayselection($processor_data, $order_info);
            $paySelection->processPayment();
        } catch (GuzzleException|FiscalizationException $e) {
            fn_log_event('general', 'runtime', array(
                'message' => $e->getMessage()
            ));
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __("csc_payselection.error_processing_payment"));
            fn_redirect('checkout.checkout');
        }
    } elseif ($processor_data['processor_params']['payment_method'] == CscPayselectionProcessorParams::PAYMENT_METHOD_WIDGET) {
        $_SESSION['payselection_order_info'] = $order_info;
        fn_redirect('checkout.checkout&payselection_widget');
    }
}

//TODO: remove after relise
function fn_write_r($mode = 'w'): void
{
    static $count = 0;
    $args = func_get_args();

    $fp = fopen('ajax_result.html', $mode . '+');

    if (!empty($args)) {
        fwrite($fp, '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">');

        foreach ($args as $k => $v) {
            $v = htmlspecialchars(print_r($v, true));
            if ($v == '') {
                $v = '    ';
            }

            fwrite($fp, '<li><pre>' . $v . "\n" . '</pre></li>');
        }
        fwrite($fp, '</ol><div style="clear:left;"></div>');
    }


    $count++;
}
