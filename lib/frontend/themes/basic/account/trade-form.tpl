{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{use class="frontend\design\Block"}
{Html::beginForm('', 'customer_edit', ['id' => 'customers_edit', 'enctype' => 'multipart/form-data'], false)}
    <input name="customers_id" value="{$customers_id}" type="hidden">

    {Block::widget(['name' => $page_name, 'params' => ['type' => 'trade_form', 'params' => $params]])}

{Html::endForm()}
