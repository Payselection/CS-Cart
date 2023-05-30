<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_csc_payselection_install(): void
{
    $processor_data = [
        'processor' => PAYSELECTION_PROCESSOR_NAME,
        'processor_script' => 'csc_payselection.php',
        'processor_template' => 'views/orders/components/payments/payselection.tpl',
        'admin_template' => 'csc_payselection.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 40,
        'addon' => 'csc_payselection',
    ];

    $processor_id = db_get_field('SELECT processor_id FROM ?:payment_processors WHERE admin_template = ?s', $processor_data['admin_template']);

    if (empty($processor_id)) {
        db_query('INSERT INTO ?:payment_processors ?e', $processor_data);
    } else {
        db_query('UPDATE ?:payment_processors SET ?u WHERE processor_id = ?i', $processor_data, $processor_id);
    }
}

function fn_csc_payselection_uninstall(): void
{
    $processors = [];
    $processors []= [
        'processor' => PAYSELECTION_PROCESSOR_NAME,
        'processor_script' => 'csc_payselection.php',
        'processor_template' => 'views/orders/components/payments/payselection.tpl',
        'admin_template' => 'csc_payselection.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 40,
        'addon' => 'csc_payselection',
    ];

    foreach ($processors as $processor_data) {
        db_query('DELETE FROM ?:payment_processors WHERE admin_template = ?s', $processor_data['admin_template']);
    }

    if (function_exists('fn_rus_payments_disable_payments')) {
        fn_rus_payments_disable_payments($processors, true);
    }
}