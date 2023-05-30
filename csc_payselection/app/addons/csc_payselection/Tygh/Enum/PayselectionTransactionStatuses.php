<?php

namespace Tygh\Enum;

class PayselectionTransactionStatuses
{
    const STATUS_PREAUTHORISED = 'preauthorized';
    const STATUS_SUCCESS = 'success';
    const STATUS_DECLINED = 'declined';
    const STATUS_PENDING = 'pending';
    const STATUS_VOIDED = 'voided';
    const STATUS_WAIT_FOR_3DS = 'wait_for_3ds';
    const STATUS_REDIRECT = 'redirect';

    const STATUS_LIST = [
        self::STATUS_PREAUTHORISED => [
            'allowed_actions' => [
                PaySelectionActions::ACTION_CHARGE,
                PaySelectionActions::ACTION_CANCEL,
            ]
        ],
        self::STATUS_SUCCESS => [
            'allowed_actions' => [
                PaySelectionActions::ACTION_REFUND
            ]
        ]
    ];
}