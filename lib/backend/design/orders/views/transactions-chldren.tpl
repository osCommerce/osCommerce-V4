<table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tabl-res table-transactions-children table-colored">
    <thead>
        <tr>
            <th>Child Transaction id</th>
            <th>Transaction Amount</th>
            <th>Status</th>
            <th>Transaction Date</th>
            <th>Comments</th>
        </tr>
    </thead>
    <tbody>
    {if $transaction->transactionChildren}
        {foreach $transaction->transactionChildren as $trans}
            <tr>
                <td>{$trans->transaction_id}<input type="hidden" value="{$trans->orders_transactions_id}"></td>
                <td>{$currencies->format($trans->transaction_amount, false, $trans->transaction_currency)}</td>
                <td>{\yii\helpers\Inflector::id2camel($trans->transaction_status)}</td>
                <td>{\common\helpers\Date::formatDateTime($trans->date_created)}</td>
                <td>{$trans->comments}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="5">No children transactions</td>
        </tr>
    {/if}
    </tbody>
</table>