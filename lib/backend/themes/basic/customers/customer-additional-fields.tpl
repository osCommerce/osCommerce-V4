<!--=== Page Content ===-->
<div id="customer_management_data">
</div>

<script type="text/javascript" src=""></script>
<link href="{$app->view->theme->baseUrl}/css/trade-form.css" rel="stylesheet" type="text/css" />

<form name="customer_edit" id="customers_edit" onSubmit="return check_form();">

    <div class="" style="max-width: calc(50% - 10px); margin-bottom: 10px">
        {*\frontend\design\boxes\account\CustomerAdditionalField::widget([
            'settings' => [['field_code' => 'trade_account_form_code']],
            'params' => ['customers_id' => $customers_id]
        ])*}
    </div>

    {*<div class="">
        <a href="{Yii::$app->urlManager->createUrl(['customers/customer-additional-fields', 'customers_id' => $customers_id, 'page_name' => 'check_form'])}" class="btn">Check form</a>
    </div>*}
<div class="" style="max-width: 1200px">
    {\frontend\design\Block::widget(['name' => 'trade_form', 'params' => ['type' => 'trade_form', 'params' => [
     'customers_id' => $customers_id,
     'inline_styles' => true,
     'show' => false
    ]]])}
</div>

    {*\frontend\design\Block::widget(['name' => 'formStep2', 'params' => ['type' => 'trade_form', 'params' => [
     'customers_id' => $customers_id,
     'inline_styles' => true
    ]]])}

    {\frontend\design\Block::widget(['name' => 'formStep3', 'params' => ['type' => 'trade_form', 'params' => [
     'customers_id' => $customers_id,
     'inline_styles' => true
    ]]])*}


    <div class="btn-bar">
        <div class="btn-left">
            <a href="javascript:history.back()" class="btn">{$smarty.const.IMAGE_BACK}</a>
        </div>
        <div class="btn-right">
            <span class="btn btn-confirm btn-save-tf">{$smarty.const.IMAGE_SAVE}</span>
        </div>
    </div>

    <input name="customers_id" value="{$customers_id}" type="hidden">

</form>

<script type="text/javascript">

    if(!entryData)entryData={};
    if(!entryData.tr)entryData.tr={};
    if(!entryData.tradeForm)entryData.tradeForm={};
    entryData.tr.TEXT_FIND_ADDRESS = "Find Address";
    entryData.tr.ENTER_YOUR_ADDRESS_MANUALLY = "Enter your address manually";
    function createJsUrl(file) {
        return "../themes/basic/js/" + file;
    }
    function getMainUrl() {
        return '';
    }

    {$js}

</script>
