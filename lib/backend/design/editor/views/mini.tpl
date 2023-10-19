<div class="unstored_carts dropdown-item">
    {if !$opened}
        <a href="{\yii\helpers\Url::to(['editor/order-edit', 'orders_id' => $orders_id, 'currentCart'=>$cart])}">
    {else}
        <span>
    {/if}
    {if $customer}
        {$customer['customers_firstname']|escape:'html'} {$customer['customers_lastname']|escape:'html'}'
    {else}
        {$basketId}
    {/if}
     Cart 
    {if $opened}
        (Opened)
        </span>
    {else}
        </a>
    {/if}
    <div class="del-pt" data-id="{$cart}"></div>
 </div>