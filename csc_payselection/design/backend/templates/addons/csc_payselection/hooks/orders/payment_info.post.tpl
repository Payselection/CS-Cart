{if !empty($payselection_status)}
    <div class="payselection_info">
        <div>
            Payselection Status: {__("csc_payselection.transaction_status.`$payselection_status->transactionState`")}
        </div>
        {if !empty($payselection_actions)}
            <input type="hidden" name="payselection_transaction_id" value="{$order_info.payment_info.transaction_id}">
            {foreach $payselection_actions as $action}
                {include file="addons/csc_payselection/components/actions/`$action`.tpl"}
            {/foreach}
        {/if}
    </div>
{/if}