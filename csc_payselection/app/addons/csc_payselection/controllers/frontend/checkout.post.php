<?php

use Tygh\Payments\Processors\CscPayselection;
use Tygh\Tygh;

/** @var string $mode */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

} else {
    if ($mode == 'checkout' && isset($_REQUEST['payselection_widget'])) {
        if (!empty(Tygh::$app['session']['cart']['payment_method_data']['processor']) && Tygh::$app['session']['cart']['payment_method_data']['processor'] == PAYSELECTION_PROCESSOR_NAME) {
            $payselection = new CscPayselection(Tygh::$app['session']['cart']['payment_method_data'], Tygh::$app['session']['payselection_order_info']);

            Tygh::$app['view']->assign('payselection_obj', $payselection);
        }
    }
}