
{if \common\helpers\Acl::checkExtensionAllowed('TradeForm')}
<a href="{$app->urlManager->createUrl('trade-form/trade-print')}?customers_id={$customersId}" target="_blank" class="btn-1 btn-pdf">PDF</a>
{/if}