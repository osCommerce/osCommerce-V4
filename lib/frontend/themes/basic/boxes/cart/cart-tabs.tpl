{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('tabs')}
<div class="tabs w-tabs">

    {if $samples || $quotations || $logged}
    <div class="tab-navigation">
        <div class="tab-li tab-cart{if $page == 'cart'} active{/if}">
            <a class="tab-a{if $page == 'cart'} active{/if}" href="{Yii::$app->urlManager->createUrl('shopping-cart')}">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a>
        </div>

        {*if $logged}
            <div class="tab-li tab-wishlist{if $page == 'wishlist'} active{/if}">
                <a class="tab-a{if $page == 'wishlist'} active{/if}" href="{Yii::$app->urlManager->createUrl('account/wishlist')}">{$smarty.const.BOX_HEADING_CUSTOMER_WISHLIST}</a>
            </div>
        {/if*}

        {if $quotations}
        <div class="tab-li tab-quote{if $page == 'quote'} active{/if}">
            <a class="tab-a{if $page == 'quote'} active{/if}" href="{Yii::$app->urlManager->createUrl('quote-cart')}">{$smarty.const.TEXT_HEADING_QUOTE_CART}</a>
        </div>
        {/if}

        {if $samples}
        <div class="tab-li tab-sample{if $page == 'sample'} active{/if}">
            <a class="tab-a{if $page == 'sample'} active{/if}" href="{Yii::$app->urlManager->createUrl('sample-cart')}">{$smarty.const.TEXT_HEADING_SAMPLE_CART}</a>
        </div>
        {/if}
    </div>
    {/if}

    {Block::widget(['name' => 'block-'|cat:$id, 'params' => ['params' => $params]])}

</div>