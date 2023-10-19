{use class="frontend\design\Info"}
{use class="common\helpers\Html"}
{use class="Yii"}
{if ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart || $settings[0].list_demo) && !GROUPS_DISABLE_CART}
  {$can_buy = true}
{else}
  {$can_buy = false}
{/if}
{if ((!$product.product_has_attributes && !Yii::$app->user->isGuest) || $settings[0].list_demo) && !GROUPS_DISABLE_CART}
  {$can_save = true}
{else}
  {$can_save = false}
{/if}
{if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
    {$can_buy = false}
{/if}
<div class="item"><div class="item-holder" data-item="{$product.products_id}">
        {if $products_carousel}
            <script>tl('{Info::themeFile('/js/main.js')}' , function(){ pCarousel.addItem('{$product.products_id}', '{$product.link}', '{addslashes(str_replace("\n", '', $product.products_name))}', '<img\
                  src="{$product.image}"\
                  alt="{addslashes(str_replace("\n", '', str_replace('"', '″', strip_tags($product.image_alt))))}"\
                  title="{addslashes(str_replace("\n", '', str_replace('"', '″', strip_tags($product.image_title))))}"\
                  {if $product.srcset}srcset="{$product.srcset}"{/if}\
                  {if $product.sizes}sizes="{$product.sizes}"{/if}\
          >', '<div class="price">\
                    {if $product.price_special}<span class="old">{$product.price_old}</span>{/if}\
                    {if $product.price_special}<span class="specials">{$product.price_special}</span>{/if}\
                    {if !$product.price_special}<span class="current">{$product.price}</span>{/if}\
    </div>'); })</script>
        {/if}

  {if !$settings[0].show_image}
    <div class="image">

      <a href="{$product.link}">
          <img
                  {if $settings[0].lazy_load} data-{/if}src="{$product.image}"
                  alt="{str_replace('"', '″', strip_tags($product.image_alt))}"
                  title="{str_replace('"', '″', strip_tags($product.image_title))}"
                  {if $product.srcset}{if $settings[0].lazy_load}data-{/if}srcset="{$product.srcset}"{/if}
                  {if $product.sizes}{if $settings[0].lazy_load}data-{/if}sizes="{$product.sizes}"{/if}
                  {if $settings[0].lazy_load}class="lazy" {/if}
          >
      </a>

    </div>
  {/if}
  {if !$settings[0].show_stock}
    <div class="stock js-stock">
      <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
    </div>
  {/if}

  <div class="name">
  {if !$settings[0].show_name}
      <div class="title"><table class="wrapper"><tr><td>
                      <a href="{$product.link}">
                          {if $product.products_name_teg}
                              {$product.products_name_teg}
                          {else}
                              {$product.products_name}
                          {/if}
                      </a></td></tr></table></div>
  {/if}
  {if !$settings[0].show_description}
    <div class="description"><table class="wrapper"><tr><td>{if $product.products_description_short}{strip_tags($product.products_description_short)|truncate:90:"...":false}{else}{strip_tags($product.products_description)|truncate:90:"...":true}{/if}</td></tr></table></div>
  {/if}
  {if !$settings[0].show_model && $product.products_model}
    <div class="products-model"><strong>{$smarty.const.TEXT_MODEL}<span class="colon">:</span></strong> <span>{$product.products_model}</span></div>
  {/if}
  {if !$settings[0].show_bonus_points && ($product.bonus_points_price > 0 || $product.bonus_points_cost > 0)}
        {if $product.bonus_coefficient === false && $product.bonus_points_price > 0}
            <div>{$product.bonus_points_price} {$smarty.const.TEXT_POINTS_REDEEM}</div>
        {/if}
        {if $product.bonus_points_cost > 0}
            <div>
                {$product.bonus_points_cost} {$smarty.const.TEXT_POINTS_EARN}
                {if $product.bonus_coefficient}
                    ({$product.bonus_price_cost_currency_formatted})
                {/if}
            </div>
        {/if}
  {/if}
  {if !$settings[0].show_properties}
    {if is_array($product.properties) && count($product.properties) > 0}
      <div class="properties">
        {foreach $product.properties as $key => $property}
          {if {$property['values']|@count} > 0}
            {if $property['properties_type'] == 'flag' && $property['properties_image']}
              <div class="property-image">
                {if $property['values'][1] == 'Yes'}
                  <span class="hover-box">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                  <span class="hover-box-content">
                    <strong>{$property['properties_name']}</strong>
                    {\common\helpers\Properties::get_properties_description($property['properties_id'], $languages_id)}
                  </span>
                </span>
                {else}
                  <span class="disable">
                  <img src="{$app->request->baseUrl}/images/{$property['properties_image']}" alt="{$property['properties_name']}">
                </span>
                {/if}
              </div>
            {else}
              <div class="property">
                <strong>{$property['properties_name']}<span class="colon">:</span></strong>
                {foreach $property['values'] as $value_id => $value}{if $value@index > 0}, {/if}<span>{$value}</span>{/foreach}
              </div>
            {/if}
          {/if}
        {/foreach}
      </div>
    {/if}

  {/if}
  </div>
  <div class="add-height">
  {if !$settings[0].show_rating_counts}
    <div class="rating-count">
    ({Info::getProductsRating($product.id, 'count')})
    </div>
  {/if}
  {if !$settings[0].show_rating}
  <div class="rating">
    <span class="rating-{Info::getProductsRating($product.id)}"></span>
  </div>
  {/if}
  {if !$settings[0].show_price && $smarty.const.GROUPS_IS_SHOW_PRICE !== false}
    <div class="price">
        {if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
            <span class="current">{sprintf($smarty.const.TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL'))}</span>
        {else}
        <span class="old" {if !$product.price_special} style="display:none;"{/if}>{$product.price_old}</span>
        <span class="specials" {if !$product.price_special} style="display:none;"{/if}>{$product.price_special}</span>
        <span class="current" {if $product.price_special} style="display:none;"{/if}>{$product.price}</span>
        {/if}
    </div>
  {/if}

  {if !$settings[0].show_qty_input && $can_buy && Info::pageBlock() != 'product' && Info::pageBlock() != 'cart'}
    {Html::beginForm($product.action_buy, 'post', ['class' => 'form-buy item-form', 'data-form' => $product.products_id, 'data-item' => $product.products_id])}
    {if !$settings[0].show_attributes && $product.product_has_attributes}
        {$product.product_attributes}
    {/if}
      {*
      {if \common\helpers\Acl::checkExtensionAllowed('TypicalOperatingTemp', 'allowed')}
          {\common\extensions\TypicalOperatingTemp\ProductListView\ProductListView::widget(['params'=>['products_id'=>$product.id]])}
      {/if}
      *}
      <div class="qty-input"{if $product.product_in_cart} style="display: none"{/if}>
        {if $can_buy}
          {*<label>{output_label const="QTY"}</label>*}
          <input
                  type="text"
                  name="qty"
                  value="1"
                  class="qty-inp"
                  {if $product.stock_indicator.quantity_max > 0 }
                      data-max="{$product.stock_indicator.quantity_max}"
                  {/if}
                  {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                  {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
          />
          <input type="hidden" name="products_id" value="{$product.id}"/>
        {/if}
      </div>
    {elseif  $settings[0].show_cart_button}
      {if !$settings[0].show_qty_input && $can_buy}
        {if !$settings[0].show_attributes && $product.product_has_attributes}
          {$product.product_attributes}
      {/if}
          <div class="qty-input qty-input-nf"{if $product.product_in_cart} style="display: none"{/if}>
                <label>{output_label const="QTY"}</label>
                <input type="text" name="listqty[]" value="1" class="qty-inp qty-inp-nf" data-prid="{$product.id}"
                    {if $product.stock_indicator.quantity_max > 0 }
                        data-max="{$product.stock_indicator.quantity_max}"
                    {/if}
                        {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                        {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if} />
                <input type="hidden" name="pid[]" value="{$product.id}"/>
          </div>
      {/if}
    {/if}
  {if !$settings[0].show_buy_button}
    <div class="buy-button">
      {if $can_buy}
        {if !$settings[0].show_qty_input && Info::pageBlock() != 'product'}
            {if $product.stock_indicator.request_for_quote == 1}
                {Html::hiddenInput('add_to_quote', 1)}
            {/if}
            {if !$settings[0].show_attributes || $product.stock_indicator.add_to_cart == 1}
                <button type="submit" class="btn-1 btn-buy add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></button>
        {else}
            {if $product.product_has_attributes || $product.stock_indicator.request_for_quote == 1}
                    <a href="{$product.link}" class="btn-1 btn-cart add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
            {else}
                    <a href="{$product.link_buy}" class="btn-1 btn-buy add-to-cart" rel="nofollow"  title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart || $product.stock_indicator.flags.notify_instock}style="display: none"{/if}></a>
                {/if}
            {/if}
        {else}
          {if !$settings[0]['show_cart_button']}
            {if $product.product_has_attributes || $product.stock_indicator.request_for_quote == 1}
                <a href="{$product.link}" class="btn-1 btn-cart add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
            {else}
                <a href="{$product.link_buy}" class="btn-1 btn-buy add-to-cart" rel="nofollow" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart || $product.stock_indicator.flags.notify_instock} style="display: none"{/if}></a>
            {/if}
          {elseif !$settings[0].show_qty_input }
            {* add to cart by ajax or add-all button *}

            {if $product.stock_indicator.request_for_quote == 1}
                {Html::hiddenInput('add_to_quote', 1)}
        {/if}
            {if !$settings[0].show_attributes || $product.stock_indicator.add_to_cart == 1}
              <a class="btn-1 btn-buy btn-buy-aj add-to-cart" href="{Yii::$app->urlManager->createUrl([FILENAME_SHOPPING_CART, 'action'=>'add_all'])}" data-item="{$product.id}" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
            {*else}
              {if $product.product_has_attributes || $product.stock_indicator.request_for_quote == 1}
                 <a href="{$product.link}" class="btn-1 btn-cart add-to-cart" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}></a>
              {else}
                 <a href="{$product.link_buy}" class="btn-1 btn-buy add-to-cart" rel="nofollow"  title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart || $product.stock_indicator.flags.notify_instock}style="display: none"{/if}></a>
              {/if*}
            {/if}

          {/if}
        {/if}
        <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class=" btn-1 btn-cart in-cart" rel="nofollow" title="{$smarty.const.TEXT_IN_YOUR_CART}"{if !$product.product_in_cart} style="display: none"{/if}></a>
        {if $product.product_in_cart}<div class="loaded-qty">({$product.product_in_cart} {$smarty.const.TEXT_LISTING_ADDED})</div>{/if}
      {/if}
    </div>
  {/if}

  <div class="other_payments">
    {if !$settings[0].show_paypal_button && $can_buy && !$product.stock_indicator.request_for_quote && (!$product.product_has_attributes || ($product.product_has_attributes && !$settings[0].show_attributes)) && !$settings[0].show_qty_input}
        <div class="paypal_button" style="clear:both;">
        {$product.show_paypal_button}
        </div>
    {/if}
  </div>
  {if !$settings[0].show_qty_input && $can_buy && Info::pageBlock() != 'product' && Info::pageBlock() != 'cart'}

      {Html::endForm()}
  {/if}
 
  </div>
  {if (!$settings[0].show_wishlist_button) || !$settings[0].show_view_button}
    <div class="buttons">
  {/if}
  {if !$settings[0].show_wishlist_button && Info::pageBlock() != 'cart'}
    <div class="button-wishlist">
      {if $can_save && Info::pageBlock() != 'product'}
      <form action="{$product.action_buy}" method="post" class="form-whishlist">
        <input type="hidden" name="products_id" value="{$product.id}"/>
        <input type="hidden" name="add_to_whishlist" value="1"/>
        <button type="submit">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
      </form>
      {/if}
    </div>
  {/if}
  {if !$settings[0].show_view_button}
    <div class="button-view">
      <a href="{$product.link}" class="view-button">{$smarty.const.VIEW}</a>
    </div>
  {/if}
      {if (!$settings[0].show_wishlist_button) || !$settings[0].show_view_button}
    </div>
  {/if}
  
  
  {if !$settings[0].show_compare}
    <div class="compare-box-item w-compare-box-item{Info::addBlockToWidgetsList('compare-box-item')}">
      <label>
        <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
        <div class="cb_check"><input type="checkbox" name="compare[]" value="{$product.id}" class="checkbox"><span>&nbsp;</span></div>
      </label>
    </div>
  {/if}

  {if (!$settings[0].show_attributes || $product.stock_indicator.add_to_cart == 1) && $settings[0]['show_cart_button']}
    <div class="buy-all-choose"><label class="add-all-choose"><span>
      {Html::checkbox('select_buy_all[]', 1, ['data-id'=>{$product.id}, 'checked'=>false, 'class' => 'buy-all-checkbox '])}
      </span>
      </label>
    </div>
  {/if}


</div></div>
