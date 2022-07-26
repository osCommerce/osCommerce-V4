{use class="\backend\design\editor\Formatter"}
{use class="yii\helpers\Html"}
{if $rma}
    {assign var = "refund_amount" value = abs($rma->getSplitter()->getDue($rma->totals))}
    <div><label>{$smarty.const.IMAGE_DETAILS}:</label></div>
    {if $rma->products}
        {foreach $rma->products as $product}
            {$product['qty']} * {$product['name']}  {Formatter::price($product['final_price'], $product['tax'], $product['qty'], $rma->info['currency'], $rma->info['currency_value'])}
        {/foreach}
    {/if}
    <div>
        <label>{$smarty.const.TEXT_REFUND_AMOUNT}: <span id="total_due" data-value="{$refund_amount}">-{Formatter::price($refund_amount, 0, 1, $rma->info['currency'], $rma->info['currency_value'])}</span>, </label>
        <label>{$smarty.const.TEXT_REFUNDED_AMOUNT}: <span id="total_refunded">{Formatter::price(0,0,0,$rma->info['currency'], $rma->info['currency_value'])}</span></label>
    </div>    
    <div class="transactions-list" style="">
        {if $preffered}
            {assign var ="less_amount" value = $refund_amount}
            {assign var ="cref_amount" value=0}
            {foreach $preffered as $key => $transaction}
                {if $less_amount >0}
                <div data-class="parent-{$transaction->orders_transactions_id}">
                    <label style="display:block;">
                        {$smarty.const.BOX_MODULES_PAYMENT}: {$manager->getPaymentCollection()->get($transaction->payment_class, true)->title}, {$smarty.const.TEXT_TRANSACTION_ID} {$transaction->transaction_id} {if number_format($refund_amount,2) eq number_format($transaction->transaction_amount,2)}<span style="color:green;">({$smarty.const.TEXT_PREFFERED})</span>{/if}
                    </label>
                    {if $transaction->transaction_amount > $less_amount}
                        {$cref_amount = $less_amount}
                    {else}
                        {$cref_amount = $transaction->transaction_amount}
                    {/if}
                    {Html::textInput('transaction['|cat:$transaction->orders_transactions_id|cat:']', $cref_amount, ['class' => 'form-control trans-amount','data-currency' => $transaction->currency_id, 'style'=>"width:150px;display:inline-block", 'data-full-amount' => $transaction->transaction_amount])}
                    {$less_amount = $less_amount - $transaction->transaction_amount}
                </div>
                {/if}
            {/foreach}
            <span class="btn-box"></span>
        {/if}
    </div>
    <div class="transactions-log"></div>
{/if}