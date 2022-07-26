{use class="\yii\helpers\Html"}
{use class="\backend\design\editor\Formatter"}
<div class="{$field}">
    <center>
        {if $isEditInGrid}
            {Html::textInput($field, Formatter::priceClear($price, $tax, $qty, $currency, $currency_value), ['class' => 'form-control '|cat:$class])}
        {else}        
            <label>{Formatter::price($price, $tax, $qty, $currency, $currency_value)}</label>
        {/if}
    </center>
</div>