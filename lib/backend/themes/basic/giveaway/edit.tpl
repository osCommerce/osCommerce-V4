{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}
{use class="common\helpers\Html"}
{include file='../assets/tabs.tpl' scope="global"}

{if $productName}
  <h4>{$productName}</h4>
{/if}
<form action="{Yii::$app->urlManager->createUrl('giveaway/submit')}" method="post" id="save_product_form" class="" name="product_edit" onSubmit="return saveGWA();">
<button type="submit" style="display:none"></button>
{Html::hiddenInput('products_id', $products_id)}
<div class="w-gwa-page after ">
<div class="gaw-pr-box" id="box-gaw-pr">
  {include file='../categories/give-away.tpl'}
</div>

    <div class="btn-bar btn-bar-edp-page after" style="padding: 0;">
        <div class="btn-left">
            <a href="{$back_url}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
</div>
</form>

<script type="text/javascript">

  function saveGWA() {
    var formData = $('#save_product_form').serializeArray();
    if (formData.length && {intval(ini_get('max_input_vars'))}>0 && formData.length>{intval(ini_get('max_input_vars'))}) {
      alert('Too many inputs. All data could NOT be saved. Try to remove some attributes and/or other data.');
      return false;
    }

    $.post($('#save_product_form').attr('action'), formData, function(data, status){
        if (status == "success") {
            $('#save_product_form').append(data);

        } else {
            alert("Request error.");
        }
    },"html");

    return false;

  }
  
$(document).ready(function() {
    $( ".datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true,
                showOtherMonths:true,
                autoSize: false,
                dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
        });
    if ( typeof init_gwa === 'function') init_gwa();
});
</script>