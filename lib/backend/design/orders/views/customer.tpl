{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<div class="pr-add-det-box pr-add-det-box01 after">
    {if $customer_id}
        <div class="cr-ord-cust">
            <span class="heading">{$smarty.const.T_CUSTOMER}
            {if \common\helpers\Acl::checkExtensionAllowed('Communication')}
                &nbsp;&nbsp;<a style="font-size: 14px; text-transform: lowercase" href="{\Yii::$app->urlManager->createUrl(['extensions/index', 'module' => 'Communication', 'action' => 'adminActionTopicInsert', 'ctCustomer' => $order->customer['name']|default, 'ctCustomerId' => $customer_id])}">Communicate</a>
            {/if}
            </span>
            {foreach \common\helpers\Hooks::getList('orders/process-order', 'customer-block') as $filename}
                {include file=$filename}
            {/foreach}
            <div class="order-customer-address">
            {if $customerExists}
                {Html::a($customerLink, Url::to(['customers/customeredit', 'customers_id' => $customer_id]))}
            {else}
                {$order->customer['name']|escape:'html'}
            {/if}
            </div>
        </div>
        <div class="cr-ord-cust cr-ord-cust-email">
            <div>{Html::a($order->customer['email_address'], 'mailto:'|cat:$order->customer['email_address'])}</div>
        </div>
		{if $order->customer['telephone']}
        <div class="cr-ord-cust cr-ord-cust-phone">
            <div>{$order->customer['telephone']|escape:'html'}</div>
        </div>
		{/if}
        {$uci = \common\helpers\Extensions::isAllowed('UploadCustomerId')}
        {if $customerExists && $uci}
            {$uci::downloadCustomerIdBlock($customer_id)}
        {/if}
    {else}
        <div class="cr-ord-cust walkin-order">
            <div>{$smarty.const.TEXT_WALKIN_ORDER} {$admin_name}</div>
        </div>
    {/if}
</div>