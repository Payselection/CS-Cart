<?php

namespace PaySelection\Hook;

use PaySelection\BaseHook;
use PaySelection\Model\ReceiptData;
use PaySelection\Model\RecurrentDetails;

class HookPay extends BaseHook
{
    public ?string $event                 = null;
    public ?string $transactionId         = null;
    public ?string $orderId               = null;
    public ?string $amount                = null;
    public ?string $currency              = null;
    public ?string $service_Id            = null;
    public ?string $dateTime              = null;
    public ?bool   $isTest                = null;
    public ?string $email                 = null;
    public ?string $phone                 = null;
    public ?string $description           = null;
    public ?string $customFields          = null;
    public ?string $brand                 = null;
    public ?string $country_Code_Alpha2   = null;
    public ?string $bank                  = null;
    public ?string $cardMasked            = null;
    public ?string $cardHolder            = null;
    public ?string $payoutToken           = null;
    public ?string $rebillId              = null;
    public ?string $expirationDate        = null;
    public ?string $rRN                   = null;
    public ?string $errorMessage          = null;
    public ?string $newAmount             = null;
    public ?string $acsUrl                = null;
    public ?string $paReq                 = null;
    public ?string $mD                    = null;
    public ?string $redirectUrl           = null;
    public ?string $redirectMethod        = null;
    public ?string $webhookUrl            = null;
    public ?string $accountId             = null;
    public ?string $startDate             = null;
    public ?string $interval              = null;
    public ?string $period                = null;
    public ?string $maxPeriods            = null;
    public ?string $recurringId           = null;
    public ?string $recurringStatus       = null;
    public ?ReceiptData $receiptData      = null;
    public ?RecurrentDetails $recurrent   = null;

}
