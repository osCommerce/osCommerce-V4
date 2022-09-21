{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{if $pInfo->products_id>0}
    {if !is_array($fieldsDataDef)}
        {$fieldsDataDef=array()}
    {/if}
    {if !empty($sInfo->specials_id)}
<a href="{\Yii::$app->urlManager->createUrl(array_merge(['specials/product-price-edit', 'id' => $sInfo->specials_id,  'products_id' => $pInfo->products_id], $fieldsDataDef))}" class="edit-price-popup popUp">
    {else}
<a href="{\Yii::$app->urlManager->createUrl(array_merge(['categories/product-price-edit', 'products_id' => $pInfo->products_id], $fieldsDataDef))}" class="edit-price-popup popUp">
    {/if}
    {if $all_hidden}
        {$smarty.const.TEXT_EDIT_GROUP_PRICE}
    {else}
        {$smarty.const.TEXT_EDIT_HIDDEN_GROUP_PRICE}
    {/if}
    </a>

<script type="text/javascript">
    $('.edit-price-popup').popUp({ 'box_class': 'popupCredithistory'});
</script>
{/if}