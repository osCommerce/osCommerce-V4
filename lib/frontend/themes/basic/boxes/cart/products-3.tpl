<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')} type-3">

  {foreach $products as $product}
    <div class="item{if strlen($product.parent) > 0} subitem{/if}">
      <div class="image">
          {if $product._status}
              <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name}"></a>
          {else}
              <span><img src="{$product.image}" alt="{$product.name}"></span>
          {/if}
      </div>
      <div class="name">
        <table class="wrapper"><tr><td>{$product.quantity} x {$product.name}</td></tr></table> {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}
        <div class="attributes">
					{use class="\frontend\design\boxes\product\Packs"}          
					{Packs::widget(['product' => $product])}
          {if isset($product.attr)}
          {foreach $product.attr as $attr}
            <div class="">
              <strong>{$attr.products_options_name}:</strong>
                {if $attr.products_options_values_text}
                    <span>{$attr.products_options_values_text}</span>
                {else}
                    <span>{$attr.products_options_values_name}</span>
                {/if}
            </div>
          {/foreach}
          {/if}
          {if isset($product.gift_wrapped) && $product.gift_wrapped}
            <div class="">
              <strong>{$smarty.const.GIFT_WRAP_OPTION}:</strong>
              <span>{$smarty.const.GIFT_WRAP_VALUE_YES}</span>
            </div>
          {/if}
        </div>
        {if isset($product.is_bundle) && $product.is_bundle}
          {foreach $product.bundles_info as $bundle_product }
            <div class="bundle_product">
              <table class="wrapper"><tr><td>{$bundle_product.x_name}</td></tr></table>
              {if $bundle_product.with_attr}
                <div class="attributes">
                  {foreach $bundle_product.attr as $attr}
                    <div class="">
                      <strong>{$attr.products_options_name}:</strong>
                      <span>{$attr.products_options_values_name}</span>
                    </div>
                  {/foreach}
                </div>
              {/if}
            </div>
          {/foreach}
        {/if}
        {if isset($product.stock_info) && $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
      </div>
      <div class="right-area">
        <div class="price">{$product.final_price}{if $product.standard_price !== false}<br/><small><i>(<strike>{$product.standard_price}</strike>)</i></small>{/if}
            {$BonusActions=\common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')}
            {if $BonusActions && $product.bonus_points_cost}
                <div class="points">
                    <div class="points-earn">
                        {if $PremiumAccountClass = \common\helpers\Acl::checkExtensionAllowed('PremiumAccount', 'allowed')}
                            {$PremiumAccountClass::showRewardPointsCost($product.bonus_points_cost * $product.quantity)}
                        {/if}
                        {$product.bonus_points_cost_formatted} {$smarty.const.EXT_BONUS_ACTIONS_TEXT_PRODUCT_REWARD_POINTS}
                    </div>
                </div>
            {/if}
        </div>
      </div>
    </div>
  {/foreach}

</div>
