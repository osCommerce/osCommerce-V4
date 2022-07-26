{use class="yii\helpers\Html"}
<tr role="row" prefix="bundles-box-{$bundles['bundles_id']}">
    <td class="sort-pointer"></td>
    <td class="img-ast img-ast-img">
        {$bundles['image']}
    </td>
    <td class="name-ast">
        <a target="_blank" href="{Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $bundles['bundles_id']])}">
            {if $smarty.const.BUNDLE_CHILD_NAME_SHOW_SKU == 'True'}[{$bundles['products_model']}] {/if}
            {$bundles['products_name']}
            {if $smarty.const.BUNDLE_CHILD_NAME_SHOW_STOCK == 'True'} ({$bundles['products_quantity']}){/if}
        </a>
        <input type="hidden" name="bundles_id[]" value="{$bundles['bundles_id']}" />
    </td>
    <td class="bu-num">
        <span>{$bundles['products_qty']}</span>
    </td>
    <td class="bu-num">
        <span>{$bundles['products_price']}</span>
    </td>
    <td class="bu-num plus_td">
        <span class="pr_plus pr-plus-{$bundles['bundles_id']}"></span><input type="text" name="sets_num_product[]" value="{$bundles['num_product']}" class="form-control" /><span class='pr_minus pr-minus-{$bundles['bundles_id']}'></span>
    </td>
    <td class="bu-disc">
        <input type="text" name="sets_discount[]" value="{$bundles['discount']}" class="form-control" placeholder="0.00%" />
    </td>
    <td class="bu-price-formula">
        <div class="input-group js-price-formula-group">
            {Html::textInput('price_formula_text['|cat:$bundles['bundles_id']|cat:']', $price_formula_text, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control js-price-formula-text', 'readonly'=>'readonly'])}
            {Html::hiddenInput('price_formula['|cat:$bundles['bundles_id']|cat:']', $bundles['price_formula'], ['class'=>'js-price-formula-data'])}
            <div class="input-group-addon js-price-formula" data-formula-allow-params="PRICE,DISCOUNT"><i class="icon-money"></i></div>
        </div>
    </td>
    <td class="bu-price">
      <input type="checkbox" value="1" name="products_status_bundle" pid="{$bundles['bundles_id']}"
          class="check_bot_switch_on_off"{if $bundles['products_status_bundle'] == 1} checked="checked"{/if} />
      {* <input type="text" name="sets_price[]" value="{$bundles['price']}" class="form-control" /> *}
    </td>
    <td class="remove-ast" onclick="deleteSelectedBundles(this)"></td>
</tr>
<script type="text/javascript">
$('.pr-plus-{$bundles['bundles_id']}').click(function(){ 
    val = $(this).next('input').attr('value');
    if (val < 9){ 
      val++;          
    }
    if (val == 9){ 
        $(this).addClass('disableM');
    }
    var input = $(this).next('input');
    input.attr('value', val);
    if (val > 1) input.siblings('.pr_minus').removeClass('disable');
});
 $('.pr-minus-{$bundles['bundles_id']}').click(function(){ 
    //productButtonCell = $('#qty').parents('.qty-buttons');
    val = $(this).prev('input').attr('value');
    if (val > 1){ 
      val--;
      $(this).prev('input').siblings('.more').removeClass('disableM');
    }
    var input = $(this).prev('input');
    input.attr('value', val);
    if (val < 2) $('.pr_minus').addClass('disable');
});
$( ".check_bot_switch_on_off" ).tlSwitch(
  {
    onText: "|",
    offText: "O",
    handleWidth: '20px',
    labelWidth: '24px'
  }
);
$('input[type="checkbox"][name="products_status_bundle"]').on('switchChange.bootstrapSwitch', function(event, isChecked) {
    let pID = $(this).attr('pid');
    let status = ((isChecked == true) ? 1 : 0);
    if (pID > 0) {
        $.post('{Yii::$app->urlManager->createUrl('categories/product-in-bundle-status')}', { 'pID': pID, 'status': status }, function(response, status) {
            //if use bootstrapSwitch toggleState or setState to revert back previous state on error - it will rise cycling onChange event
        }, 'json');
    }
});
</script>