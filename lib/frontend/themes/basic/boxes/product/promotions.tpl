{use class="yii\helpers\Html"}
{use class="common\classes\Images"}
{\frontend\design\Info::addBoxToCss('info')}
<div class="product-promotions">
    <div class="heading-2"><span>{$smarty.const.SPECIAL_OFFERS_PROMOTIONS}</span></div>
    <div class="product-promotions-content">
        {foreach $promotions as $promo}
            <div class="promotion">
                <div class="info" style="display: none"></div>


                <div class="promotion-data">

                    <div class="heading-3">{$promo.label}</div>

                    {if $promo.image}
                        <div class="image"><img src="{$promo.image}" alt=""></div>
                    {/if}

                    {if !(is_null($promo.date_start) || $promo.date_start eq '0000-00-00 00:00:00') }
                        <div class="date-start">Promotion Date Start: {$promo.start|date_format}</div>
                    {/if}

                    {if !(is_null($promo.date_expired) || $promo.date_expired eq '0000-00-00 00:00:00') }
                        <div class="date-expired">Promotion Date End: {$promo.date_expired|date_format}</div>
                    {/if}

                </div>

                <div class="products">
                    {foreach $promo.products as $prod}
                        <div class="item" id="item-{$prod.products_id}" data-id="{$prod.products_id}">
                            {*<input type="hidden" name="collections[]" value="{$prod.products_id}">
                            <input type="hidden" name="collections_qty[{$prod.products_id}]" value="1">*}

                            <div class="image">
                                <a href="{$prod.link}"><img src="{$prod.image}" alt="{str_replace('"', '″', $prod.products_name)}" title="{str_replace('"', '″', $prod.products_name)}"></a>
                            </div>

                            <div class="stock">
                                <span class="{$prod.stock_indicator.text_stock_code}"><span class="{$prod.stock_indicator.stock_code}-icon">&nbsp;</span>{$prod.stock_indicator.stock_indicator_text}</span>
                            </div>

                            <div class="price">
                                {if $prod.price}
                                    <span class="current">{$prod.price}</span>
                                {else}
                                    <span class="old">{$prod.price_old}</span>
                                    <span class="specials">{$prod.price_special}</span>
                                {/if}
                            </div>

                            <div class="title">
                                <a href="{$prod.link}">{$prod.products_name}</a>
                            </div>


                            <div class="attributes">
                                {foreach $prod.attributes_array as $item}
                                    <div class="select-box">
                                        <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$product.products_name|escape:'html'} - {$item.title}">
                                            <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                                            {foreach $item.options as $option}
                                                <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div class="promotion-data-2">

                    <div class="promotion-full-price-holder">
                        <span class="text">{$smarty.const.TEXT_SEPARATE}:</span>
                        <span class="promotion-full-price">{$promo.full_price}</span>
                    </div>

                    <div class="promotion-full-price-discount-holder">
                        <span class="text">{$smarty.const.TEXT_TOGETHER}:</span>
                        <span class="promotion-full-price-discount">{$promo.full_price_discount}</span>
                    </div>

                    <div class="save">
                        <span class="save-text">{$smarty.const.SALE_TEXT_SAVE}</span>
                        <span class="save-percents">{$promo.save_percents}%</span>
                        <span class="save-price">{$promo.save}</span>
                    </div>
                    {if !isset($params['preview'])}
                    <div class="button">
                        {if $promo.stock_indicator.add_to_cart}
                        <span class="btn-2 add-promotion">{$smarty.const.ADD_OFFER_TO_CART}</span>
                        {/if}
                    </div>
                    {/if}
                </div>


            </div>
        {/foreach}
</div>
</div>
<script>
    tl('{\frontend\design\Info::themeFile('/js/slick.min.js')}', function(){
        var box = $('#box-{$id}');

        var productPromotions = $('.product-promotions', box);
        var addPromotion = $('.add-promotion', box);
        var info = $('.info', box);

        productPromotions.on('change', 'select', function(){
            var sendData = $(this).closest('.products').find('input, select').serializeArray()
            //var sendData = $('input, select', box).serializeArray();
            sendData.push({ name: 'products_id', value: '{$products_id}'});
            sendData.push({ name: 'id', value: '{$id}'});
            sendData.push({ name: 'slide', value: slide});
            $.get(
                "{Yii::$app->urlManager->createUrl('get-widget/one')}",
                sendData,
                function(data){
                    box.html(data)
                })
        });

        addPromotion.on('click', function(){
            var error = false;
            $('select', box).each(function(){
                if ($(this).val() == 0) {
                    info.show().html($('option[value=0]', this).text());
                    error = true
                }
            });

            if (!error) {
                $('form .product-promotions input[name*="collections"]').remove();
                $(this).closest('.promotion').find('.item').each(function(){
                    $(this).prepend('<input type="hidden" name="collections[]" value="' + $(this).data('id') + '">' +
                        '<input type="hidden" name="collections_qty[' + $(this).data('id') + ']" value="1">')
                });

                $(this).parents('form').trigger('submit')
            }
        });


        var carousel = $('.carousel', box);
        var tabs = carousel.parents('.tabs');
        tabs.find('> .block').show();

        var slide = 0;
        {\frontend\design\Info::addBoxToCss('slick')}
        var slider = $('.product-promotions-content', box).slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: false,
        });
        slider.slick('slickGoTo', '{$slide}', true);
        slider.on('afterChange', function(){
            slide = slider.slick('slickCurrentSlide');
        });
        setTimeout(function(){ tabs.trigger('tabHide') }, 100)

    })
</script>