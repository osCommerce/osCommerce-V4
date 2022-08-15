
{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{if (((!$product.product_has_attributes || !$settings[0].show_attributes_b2b) && ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart)) || $settings[0].list_demo) && !GROUPS_DISABLE_CART}
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

<div class="item" id="item-{$product.id}">
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

  {if !$settings[0].show_image_b2b}
    <div class="image">

      <a href="{$product.link}">
          <img
                  {if $settings[0].lazy_load}data-{/if}src="{$product.image}"
                  alt="{str_replace('"', '″', strip_tags($product.image_alt))}"
                  title="{str_replace('"', '″', strip_tags($product.image_title))}"
                  {if $product.srcset}{if $settings[0].lazy_load}data-{/if}srcset="{$product.srcset}"{/if}
                  {if $product.sizes}{if $settings[0].lazy_load}data-{/if}sizes="{$product.sizes}"{/if}
                  {if $settings[0].lazy_load}class="lazy" {/if}
          >
      </a>
    </div>
  {/if}

  <div class="right-area">
	<div class="name">
    {if !$settings[0].show_name_b2b}
      <div class="title"><table class="wrapper"><tr><td>
                      <a href="{$product.link}">
                          {if $product.products_name_teg}
                              {$product.products_name_teg}
                          {else}
                              {$product.products_name}
                          {/if}
                      </a></td></tr></table></div>
    {/if}
    {if !$settings[0].show_description_b2b}
      <div class="description"><table class="wrapper"><tr><td>{if $product.products_description_short}{strip_tags($product.products_description_short)|truncate:150:"...":false}{else}{strip_tags($product.products_description)|truncate:150:"...":true}{/if}</td></tr></table></div>
    {/if}
    {if !$settings[0].show_model_b2b && $product.products_model}
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
    {if !$settings[0].show_properties_b2b}
      {if {$product.properties|@count} > 0}
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
                  <strong>{$property['properties_name']}</strong>
                  {foreach $property['values'] as $value_id => $value}{if $value@index > 0}, {/if}<span>{$value}</span>{/foreach}
                </div>
              {/if}
            {/if}
          {/foreach}
        </div>
      {/if}
    {/if}

    {if !$settings[0].show_attributes_b2b && $can_buy && $product.product_has_attributes}
    <div class="attributes"></div>
    <script type="text/javascript">
      tl(function(){
        update_attributes_list($("#item-{$product.id|replace:'{':'\\\{'|replace:'}':'\\\}'}"));
      })
    </script>
    {/if}
    {if !$settings[0].show_attributes_b2b && $can_buy && $product.is_bundle}
    <div class="bundle"></div>
    <script type="text/javascript">
      tl(function(){
        update_bundle_attributes_list($('#item-{$product.id}'));
      })
    </script>
    {/if}
  </div>
  
  <div class="right-area-2">
  {if !$settings[0].show_stock_b2b}
    <div class="stock js-stock">
      <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
    </div>
  {/if}
    {if !$settings[0].show_rating_counts_b2b}
      <div class="rating-count">
        ({Info::getProductsRating($product.id, 'count')})
      </div>
    {/if}
    {if !$settings[0].show_rating_b2b}
      <div class="rating">
        <span class="rating-{Info::getProductsRating($product.id)}"></span>
      </div>
    {/if}
    {if !$settings[0].show_price_b2b}
      <div class="price">
        {if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
            <span class="current">{sprintf($smarty.const.TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL'))}</span>
        {else}
            {if $product.price_special}
                <span class="old">{$product.price_old}</span>
                <span class="specials">{$product.price_special}</span>
            {else}
                <span class="current">{$product.price}</span>
            {/if}
        {/if}
      </div>
    {/if}
    {if !$settings[0].show_qty_input_b2b && $can_buy && Info::pageBlock() != 'product'  && $product.stock_indicator.flags.add_to_cart==1}
      <div class="qty-input"{if $product.product_in_cart} style="display: none"{/if}>
        {*<label>{output_label const="QTY"}</label>*}
        <input type="text" name="qty[]" value="{if isset($product.add_qty)}{if $product.stock_indicator.quantity_max < $product.add_qty}{$product.stock_indicator.quantity_max}{else}{$product.add_qty}{/if}{else}0{/if}" data-zero-init="1" class="qty-inp"{if $product.stock_indicator.quantity_max>0} data-max="{$product.stock_indicator.quantity_max}"{/if} {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_minimal>0} data-min="{$product.order_quantity_data.order_quantity_minimal}" {else}data-min="0" {/if} {if $product.order_quantity_data && $product.order_quantity_data.order_quantity_step>1} data-step="{$product.order_quantity_data.order_quantity_step}"{/if}/>
        <input type="hidden" name="products_id[]" value="{$product.id}"/>
      </div>
    {/if}
      {assign var=paramsData value=['products_id'=>$product.id]}
      {assign var=params value=['params'=>$paramsData]}
      {\frontend\design\boxes\ButtonListing::widget($params)}
    {if (!$settings[0].show_wishlist_button_b2b && $can_save) || !$settings[0].show_view_button_b2b}
    <div class="buttons">
      {/if}

      {*if !$settings[0].show_wishlist_button_b2b && $can_save && Info::pageBlock() != 'product'}
        <div class="button-wishlist">
          {Html::beginForm($product.action_buy, 'post', ['class' => 'form-whishlist'])}
            <input type="hidden" name="products_id" value="{$product.id}"/>
            <input type="hidden" name="add_to_whishlist" value="1"/>
            <button type="submit">{$smarty.const.TEXT_WISHLIST_SAVE}</button>
          {Html::endForm()}
        </div>
      {/if*}
      {if !$settings[0].show_view_button_b2b}
        <div class="button-view">
          <a href="{$product.link}" class="view-button">{$smarty.const.VIEW}</a>
        </div>
      {/if}
      {if (!$settings[0].show_wishlist_button_b2b && $can_save) || !$settings[0].show_view_button_b2b}
    </div>
    {/if}
    {if !$settings[0].show_compare_b2b}
      <div class="compare-box-item w-compare-box-item{Info::addBlockToWidgetsList('compare-box-item')}">
        <label>
          <span class="cb_title">{$smarty.const.TEXT_SELECT_TO_COMPARE}</span>
          <div class="cb_check"><input type="checkbox" name="compare[]" value="{$product.id}" class="checkbox"><span>&nbsp;</span></div>
        </label>
      </div>
    {/if}
	</div>
  </div>
</div>
