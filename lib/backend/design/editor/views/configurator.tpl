{use class = "yii\helpers\Html"}
<div class="pc_wrapper">
    <div class="pc_table">
        {foreach $elements as $element}
        <div class="pc_name heading-4">
            {if $element['elements_image'] != ''}
                <img src="{$smarty.const.DIR_WS_IMAGES}{$element['elements_image']}" alt="{$element['elements_name']}" width="30">
            {/if}
            {$element['elements_name']}
            {if $element['is_mandatory']} 
                <span class="inputRequirement">*</span>
                {Html::hiddenInput('product_info[][mandatory]['|cat:$element['elements_id']|cat:']', $element['elements_id'])}
            {/if}
        </div>
        <div class="pc_row">
            <div class="pc_item">
                {Html::dropDownList('product_info[][elements]['|cat:$element['elements_id']|cat:']', $element['selected_id'], $element['products_array'], ['class' => 'form-control', 'onchange'=>"getDetails(this, true)"])}
            </div>

            {if $element['selected_id'] > 0}
                <div class="item-content" data-id="{$element['selected_id']}">
                    <div class="pc_details">
                          <span class="pc_details_img"><img src="{$element['selected_image']}" title="{$element['selected_name']}" /></span>
                    </div>
                    <div class="pc_stock">                      
                        <span class="{$element.selected_stock_indicator.text_stock_code}">
                            <span class="{$element.selected_stock_indicator.stock_code}-icon">&nbsp;</span>
                            {$element.selected_stock_indicator.stock_indicator_text}
                        </span>
                    </div>
                    <div class="pc_qty">
                      <div class="qty-box plus_td">
                        <span class="pr_minus"></span>
                        {Html::textInput('product_info[][elements_qty]['|cat:$element['elements_id']|cat:']', $element['elements_qty'], ['class' => 'qty form-control new-product', 'data-min' => $element['selected_min'], 'data-step' => 1, 'onchange' => "getDetails(this);" ])}
                        <span class='pr_plus'></span>
                      </div>
                    </div>
                    <span class="tl-ed-or-two-pr">
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tbody><tr>
                                <td><span class="element_total_summ" data-price=""></span></td>
                            </tr>
                            <tr>
                                <td><span class="element_total_summ_tax"></span></td>
                            </tr>
                            </tbody>
                        </table>
                    </span>
                    {*<div class="pc_price price">
                        {if strlen($element['selected_price']) > 0}
                            <span class="current">{$element['selected_price']}</span>
                        {else}
                            <span class="old">{$element['selected_price_old']}</span>
                            <span class="specials">{$element['selected_price_special']}</span>
                        {/if}
                    </div>*}
                    
              </div>
              <div class="attributes-parent w-line-row">
                {$manager->render('Attributes', ['attributes' => $element.attributes_array, 'settings' => ['onchange' => 'getDetails(this);' ], 'complex' => true])}
                <div class="attributes-item tax">
                    {$manager->render('Tax', ['manager' => $manager, 'product' => $element, 'uprid'=> $element['selected_id'] , 'tax_address' => $tax_address, 'tax_class_array' => $tax_class_array, 'onchange' => 'changeConfTax(this)' ])}
                </div>
              </div>
          {/if}
        </div>
        {/foreach}
    </div>    
    <div class="pc-total-price">{$smarty.const.TEXT_TOTAL_PRICE} <span class="product-price-configurator"></span></div>
</div>
