<div class="check-form">
    {\frontend\design\Block::widget(['name' => 'check_form', 'params' => ['type' => 'trade_form', 'params' => [
     'customers_id' => $customers_id,
     'inline_styles' => true,
     'show' => true
    ]]])}
</div>
<div class="" style="margin-top: 20px">
    <a href="javascript:history.back()" class="btn">Back</a>
</div>


    <link href="{$app->view->theme->baseUrl}/css/trade-form.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .btn-save {
            display: none;
        }
        .w-html_box {
            margin: 10px 0 5px
        }
    </style>