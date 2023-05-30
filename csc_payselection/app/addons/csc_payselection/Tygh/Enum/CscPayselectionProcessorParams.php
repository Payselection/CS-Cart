<?php

namespace Tygh\Enum;

class CscPayselectionProcessorParams
{
    const WEBHOOK_PAYMENT = 'Payment';
    const WEBHOOK_FAIL = 'Fail';
    const WEBHOOK_CANCEL = 'Cancel';
    const WEBHOOK_REFUND = 'Refund';
    const WEBHOOK_BLOCK = 'Block';

    const RETURN_MODE_SUCCESS = 'success';
    const RETURN_MODE_FAIL = 'fail';
    const RETURN_MODE_RETURN = 'return';
    const RETURN_MODE_NOTIFY = 'notify';
    const PROCESSOR_ACTION_FINISH = 'finish';
    const PROCESSOR_ACTION_SAVE_ORDER = 'save_order';
    const PROCESSOR_ACTION_NULL = 'null';

    const PAYMENT_METHOD_PAGE = 'page';
    const PAYMENT_METHOD_WIDGET = 'widget';
}