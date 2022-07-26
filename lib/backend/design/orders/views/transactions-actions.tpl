{use class="yii\helpers\Html"}

{if $transaction}
    <label>{$transaction->transaction_id}</label>
    <label class="checking-refunds"></label>
    {if $data['can_refund']}
        Can be refund
        {*Html::a('Refund', 'http://')*}
    {/if}
    
    {if $data['can_void']}
        Can be void
        {*Html::a('Void', 'http://')*}
    {/if}
    
    <script>
        $(document).ready(function(){
            $('.checking-refunds').html('Checking refunds...');            
        })
    </script>
{/if}