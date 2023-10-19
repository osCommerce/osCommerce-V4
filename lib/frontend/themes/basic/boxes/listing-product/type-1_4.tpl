{use class="frontend\design\Info"}
{use class="Yii"}
{if ($product.is_virtual || $product.stock_indicator.flags.can_add_to_cart || $settings[0].list_demo) && !GROUPS_DISABLE_CART}
    {$can_buy = true}
{else}
    {$can_buy = false}
{/if}
{if ((!Yii::$app->user->isGuest) || $settings[0].list_demo) && !GROUPS_DISABLE_CART}
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
    <div class="item-wrap">
        <div class="image-name">

            {if !$settings[0].show_image}
                <div class="image">

                    <a href="{$product.link}">
                        <img
                                {if $settings[0].lazy_load}data-{/if}src="{$product.image}"
                                alt="{str_replace('"', '″', strip_tags($product.image_alt))}"
                                title="{str_replace('"', '″', strip_tags($product.image_title))}"
                                {if $product.srcset}{if $settings[0].lazy_load}data-{/if}srcset="{$product.srcset}"{/if}
                                {if $product.sizes}{if $settings[0].lazy_load}data-{/if}sizes="{$product.sizes}"{/if}
                                {if $settings[0].lazy_load}class="lazy" {/if}
                        ></a>
                </div>
            {/if}
            <div class="name">
                {if !$settings[0].show_name}
                    <div class="title"><a href="{$product.link}">
                            {if $product.products_name_teg}
                                {$product.products_name_teg}
                            {else}
                                {$product.products_name}
                            {/if}
                        </a></div>
                {/if}
                {if !$settings[0].show_stock}
                    <div class="stock">
                        <span class="{$product.stock_indicator.text_stock_code}"><span
                                    class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
                    </div>
                {/if}
            </div>
        </div>
        <div class="item-doc">
            {$aDocuments = \common\extensions\ProductDocuments\ProductDocuments::getDocuments($product.id)}
            {if is_array($aDocuments) && count($aDocuments) > 0}
                {foreach $aDocuments as $aDocument}
                    {if is_array($aDocument.docs) && count($aDocument.docs) > 0}
                        {foreach $aDocument.docs as $document}
                            <a href="{DIR_WS_CATALOG}documents/{$document.filename}" target="_blank">
                                {if $document.title.17 != ''}
                                    {$document.title.17}
                                {else}
                                    {$smarty.const.TEXT_CERTIFICATE}
                                {/if}
                            </a>
                        {/foreach}
                    {/if}
                {/foreach}
            {/if}

        </div>
        {if !$settings[0].show_price}
            <div class="price">
            {if Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')}
                <span class="current">{sprintf($smarty.const.TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL'))}</span>
            {else}
                    {if isset($product.price)}
                    <span class="current">{$product.price}</span>
                {else}
                    <span class="specials">{$product.price_special}</span>
                    <span class="old">{$product.price_old}</span>
                {/if}
            {/if}
            </div>
        {/if}

        <div class="qty_list">
            <div class="qty-input">
                {if $can_buy}
                    <label>{output_label const="QTY"}</label>
                    <input autocomplete="off" type="text" name="qty[]" value="0" data-price="{$product.calculated_price}" data-zero-init="1" class="qty-inp"
                        {if $product.stock_indicator.quantity_max>0} data-max="{$product.stock_indicator.quantity_max}"{/if}
                        {if $moq = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$moq::setLimit($product.order_quantity_data)}{/if}
                        {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($product.order_quantity_data)}{/if}
                    />
                    <input type="hidden" name="products_id[]" value="{$product.uprid}"/>
                {/if}	
            </div>

        </div>

        <div class="item-itog">
		</div>
        <div class="item-remove">
		{if !$can_buy}
				<div>
                    {$smarty.const.TEXT_PRODUCT_DISABLED}
				</div>
          {elseif $product.product_has_attributes}
                <a class="view_link_cart pc-send-shop" data-id="{$product.uprid}" data-cart="cart" href="javascript:void(0)">{$smarty.const.TEXT_BUY}</a>
          {else}
                {*<a class="btn-1 btn-buy add-to-cart" data-id="{$product.products_id}" data-cart="quote" title="{$smarty.const.ADD_TO_CART}" href="javascript:void(0)">{$smarty.const.TEXT_BUY}</a>s*}
                <a class="view_link_cart pc-send-shop" data-id="{$product.products_id}" data-cart="cart" href="javascript:void(0)">{$smarty.const.TEXT_BUY}</a>
          {/if}
            <a class="pc-delete-item" data-type="{$product.add_flag}" data-id="{$product.uprid}" href="#">{$smarty.const.IMAGE_BUTTON_DELETE}</a>
        </div>
    </div>
</div>
