{use class="yii\helpers\Html"}        

    {if is_array($products) && count($products)}
        {foreach $products as $_idx => $product}			
            <tr class="dataTableRow product_info">
                <td class="dataTableContent plus_td box_al_center" valign="top" align="center">
                {if $product.parent == ''}
                    {if !$product['ga'] }
                        {$manager->render('Qty', ['product' => $product,  'manager' => $manager, 'isPack' => $product['is_pack']])}                        
                    {else}
                        <div class="box_al_center">{$product['quantity']}</div>
                    {/if}
                    {tep_draw_hidden_field('products_id',{$product['template_uprid']})}                    
                {else}
                    {$product['quantity']}
                {/if}
                {tep_draw_hidden_field('uprid',{$product['id']})}
                </td>                    
                <td class="dataTableContent left" valign="top">
                    <table class="table no-border">
                        <tr>
                            <td width="15%">
                    {\common\classes\Images::getImage($product['id'])}
                            </td>
                            <td>
                    
                    {if $isEditInGrid}
                        {Html::input('text', "name", {$product['name']},['class' => 'form-control name'])}
                    {else}
                        <label style="display:inline;">{$product['name']}</label>
                    {/if}
                    {$ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')}
                    {if (!$product['ga'] && $ext)}
                            {$ext::queryOrderProcessAdmin($products, $_idx)}
                    {/if}
                    {if is_array($product['attributes']) && $product['attributes']|count > 0}
                        {for $j=0; $j<sizeof($product['attributes']); $j++}
                            <div class="prop-tab-det-inp"><small>&nbsp;
                                <i> - {($product['attributes'][$j]['option'])} : {($product['attributes'][$j]['value'])}</i></small>
                            </div>
                            {*<input type="hidden" name="id[{$product['attributes'][$j]['option_id']}]" data-option="{$product['attributes'][$j]['option_id']}" value="{$product['attributes'][$j]['value_id']}">*}
                         {/for}
                    {/if}
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="dataTableContent left" valign="top">
                    <label>{$product['model']}</label>
                </td>
                {if $giftWrapExist}
                <td class="dataTableContent right" valign="top">
                {if $product.parent == ''}
                    {if $product['gift_wrap_allowed']}
                        <div class="gift-wrap">
                            <label>+{$currencies->display_price($product['gift_wrap_price'], $product['tax'])}<br/>
                        {Html::checkbox('gift_wrap['|cat:$product['id']|cat:']', $product['gift_wrapped'], ['class' => 'check_on_off gift_wrap', 'onchange'=> "order.updateProductInRow(this, 'change_qty')"])}
                            </label>
                        </div>
                    {/if}
                {/if}
                </td>
                {/if}
                <td class="dataTableContent" align="center" valign="top">
                {if !$product['ga'] && $product.final_price}
                    {$manager->render('Tax', ['manager' => $manager, 'product' => $product, 'tax_address' => $tax_address, 'tax_class_array' => $tax_class_array, 'onchange' => "order.updateProductInRow(this, 'change_tax')" ])}                    
                {/if}
                </td>
                <td class="dataTableContent" align="right" valign="top" class="final_price">
                    {$manager->render('Price', ['field' => 'final_price', 'price' => $product['overwritten']['final_price'], 'price_variant' => $product['final_price'], 'tax' => 0, 'qty' => 1, 'currency' => $cart->currency ])}
                    
                    {if !is_null($bonus_points)}
                        {assign var="bonus" value=$bonus_points['bonuses']}
                        {if  $bonus_points.can_use_bonuses && $bonus->products_bonus_list[$product['id']]['redeem'] && $bonus->products_bonus_list[$product['id']]['redeem'] > 0}                
                            {if $bonus->products_bonus_list[$product['id']]['redeem_partly']}
                            <div>{$bonus->products_bonus_list[$product['id']]['redeem_text']}</div>
                            {else}
                            <div>{number_format($bonus->products_bonus_list[$product['id']]['redeem'], 0)} {$smarty.const.TEXT_POINTS_REDEEM}</div>
                            {/if}
                        {/if}
                        {if $product['bonus_points_cost'] && $product['bonus_points_cost'] > 0 && !$bonus->products_bonus_list[$product['id']]['redeem']}
                            <div>{number_format($product['bonus_points_cost'] * $product['quantity'], 0)} {$smarty.const.TEXT_POINTS_EARN}</div>
                        {/if}
                    {/if}
                </td>
                <td class="dataTableContent no-right-border" align="right" valign="top">
                {if !$product['ga'] && $product.parent == ''}
                    {$manager->render('ExtraCharge', ['product' => $product, 'manager' => $manager])}                        
                {/if}
                </td>
                <td class="dataTableContent no-left-border result-price" align="right" valign="top">
                {if !$product['ga'] && $product.parent == ''}
                    {$manager->render('Price', ['field' => 'result_price', 'price' => $product['final_price'], 'tax' => 0, 'qty' => 1, 'currency' => $cart->currency, isEditInGrid => true, 'classname' => 'result-price' ])}
                {/if}
                </td>
                <td class="dataTableContent" align="right" valign="top" class="final_price_total_exc_tax">
                    {$manager->render('Price', ['field' => 'final_price_total_exc_tax', 'price' => $product['final_price'], 'tax' => 0, 'qty' => $product['quantity'], 'currency' => $cart->currency ])}
                </td>
                <td class="dataTableContent" align="right" valign="top" class="final_price_total_inc_tax">
                    {$manager->render('Price', ['field' => 'final_price_total_inc_tax', 'price' => $product['final_price'], 'tax' => $product['tax_rate'], 'qty' => $product['quantity'], 'currency' => $cart->currency ])}
                    
                </td>
                <td class="dataTableContent adjust-bar" align="center" >
                {if $product.parent == ''}
                    {if $product['ga']}
                        <div>
                        {Html::a('<i class="icon-pencil"></i>', Yii::$app->urlManager->createUrl(array_merge($queryParams, ['action' => 'show_giveaways', 'edit' => true])), ['class'=> "popup", 'data-class'=>"add-product"] )}                            
                        </div>
                    {else}
                        <div>
                        {Html::a('<i class="icon-pencil"></i>', Yii::$app->urlManager->createUrl(array_merge($queryParams, ['uprid' => $product['id'], 'action' => 'edit_product'])), ['class'=> "popup", 'data-class'=>"edit-product"] )}
                        </div>                            
                    {/if}						
                    
                    {if $product['ga']}
                        <div class="del-pt" onclick="deleteOrderGiveaway(this);">                        
                    {else}
                        <div class="del-pt" onclick="deleteOrderProduct(this);">
                    {/if}
                    
                    </div>
                {/if}    
                </td>
            </tr>
        {/foreach}	
    {else}
    <tr><td colspan="9"><i>Add some products to create an order</i></td></tr>
    {/if}
		