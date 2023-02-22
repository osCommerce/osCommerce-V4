{\frontend\design\Info::addBoxToCss('compare')}

<div class="compare-clear-list">
    <span class="clear-all">{*$smarty.const.TEXT_CLEAR_COMPARE_LIST*}</span>
</div>
<div class="compare-list">
    {if {strlen($error_text) > 0}}
        {$error_text}
    {else}

        {if is_array($products_data_array) && count($products_data_array) > 0}
            <div class="compare-fix-bar">
                <div class="product-listing list-{\common\classes\design::pageName($settings[0].listing_type)}">
                    <div class="cross-box" style="width: {100 / (count($products_data_array) + 1)}%">
                        {if $lastCategoryUrl}<div class="return"><a href="{$lastCategoryUrl}">Return back to category</a></div>{/if}
                        <div class="different-all">
                            <div class="all-property active">All property</div>
                            <div class="different-property">Only differences</div>
                        </div>
                    </div>
                    {foreach $products_data_array as $products_id => $product}
                        {$productListing[$products_id]}
                    {/foreach}
                </div>
            </div>
        <div class="compare-list-wrap">
        <div class="compare-list-holder">
            <table>
                {if {$properties_tree_array|@count} > 0}
                    {foreach $properties_tree_array as $properties_id => $property}
                        <tr id="property-{$property['properties_id']}" class="{if {$property['vary']}}vary{else}same{/if}">
                            <td class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" valign="top">
                                {$property['properties_name']}
                            </td>
                            {foreach $products_data_array as $products_id => $products_data}
                                <td class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" style="width: {100 / (count($products_data_array) + 1)}%" valign="top">
                                    {if is_array($products_data['properties_tree'][$properties_id]['values']) && count($products_data['properties_tree'][$properties_id]['values']) > 0}
                                        {foreach $products_data['properties_tree'][$properties_id]['values'] as $value_id => $value}
                                            <span id="value-{$value_id}">{$value}</span>
                                        {/foreach}
                                    {/if}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                {/if}
            </table>
        </div>
        </div>
        {/if}

        <div class="landscape-scroll"><div class="landscape-scroll-holder" style="font-size: 1px">&nbsp;</div></div>
    {/if}
</div>


{*
<script type="text/javascript">
    tl(function(){
       {if \common\helpers\Acl::checkExtensionAllowed('BonusActions')}
       {Yii::$app->controller->promoActionsObs->renderValidPromoAction('comparing_products')}
       {/if}
    });
</script>*}
