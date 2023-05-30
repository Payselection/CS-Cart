<div class="control-group">
    <label class="control-label cm-required" for="site_id">{__("csc_payselection.site_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][site_id]" id="site_id" value="{$processor_params.site_id}">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="public_key">{__("csc_payselection.public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][public_key]" id="public_key" value="{$processor_params.public_key}">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="secret_key">{__("csc_payselection.secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="payment_method">{__("csc_payselection.payment_method")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][payment_method]" id="payment_method">
            <option value="">-</option>
            <option value="page"{if $processor_params.payment_method == 'page'} selected="selected"{/if}>{__("csc_payselection.payment_method.page")}</option>
            <option value="widget"{if $processor_params.payment_method == 'widget'} selected="selected"{/if}>{__("csc_payselection.payment_method.widget")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="payment_type">{__("csc_payselection.payment_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][payment_type]" id="payment_type">
            <option value="">-</option>
            <option value="Pay"{if $processor_params.payment_type == 'Pay'} selected="selected"{/if}>{__("csc_payselection.payment_type.pay")}</option>
            <option value="Block"{if $processor_params.payment_type == 'Block'} selected="selected"{/if}>{__("csc_payselection.payment_type.block")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="enable_fiscalization">{__("csc_payselection.enable_fiscalization")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][enable_fiscalization]" value="N">
        <input type="checkbox" name="payment_data[processor_params][enable_fiscalization]" id="enable_fiscalization" value="Y"{if $processor_params.enable_fiscalization == "Y"} checked{/if}>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="fiscalization_email">{__("csc_payselection.fiscalization_email")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][fiscalization_email]" id="fiscalization_email" value="{$processor_params.fiscalization_email}">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="sno">{__("csc_payselection.sno")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][sno]" id="sno">
            <option value="">-</option>
            <option value="osn"{if $processor_params.sno == 'osn'} selected="selected"{/if}>{__("csc_payselection.sno.osn")}</option>
            <option value="usn_income"{if $processor_params.sno == 'usn_income'} selected="selected"{/if}>{__("csc_payselection.sno.usn_income")}</option>
            <option value="usn_income_outcome"{if $processor_params.sno == 'usn_income_outcome'} selected="selected"{/if}>{__("csc_payselection.sno.usn_income_outcome")}</option>
            <option value="envd"{if $processor_params.sno == 'envd'} selected="selected"{/if}>{__("csc_payselection.sno.envd")}</option>
            <option value="esn"{if $processor_params.sno == 'esn'} selected="selected"{/if}>{__("csc_payselection.sno.esn")}</option>
            <option value="patent"{if $processor_params.sno == 'patent'} selected="selected"{/if}>{__("csc_payselection.sno.patent")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="inn">{__("csc_payselection.inn")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][inn]" id="inn" value="{$processor_params.inn}">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="payment_place">{__("csc_payselection.payment_place")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][payment_place]" id="payment_place" value="{$processor_params.payment_place}">
    </div>
</div>