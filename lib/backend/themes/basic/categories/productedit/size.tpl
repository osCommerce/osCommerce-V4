{use class="\common\helpers\Html"}
<div class="is-virtual-btn">{$smarty.const.TEXT_IS_VIRTUAL} <input type="checkbox" value="1" name="is_virtual" id="is-virt-switcher" class="is_virt_on_off"{if $pInfo->is_virtual == 1} checked{/if} /></div>
<div class="is-virtual-upload"{if $pInfo->is_virtual == 0} style="display: none;"{/if}>
  <label>{$smarty.const.TEXT_PRODUCTS_FILE}</label>
  {*tep_draw_file_field('products_file')*}
  <div class="attachment-upload-container">
    <div class="upload-file-wrap">
      <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>
      <div class="upload-file"></div>
      <div class="upload-hidden"><input type="hidden" name="products_file"/></div>
    </div>
  </div>
  <span id="upload-file-name"></span>
  {if isset($app->controller->view->productFile)}{$app->controller->view->productFile}{/if}
</div>
<div class="is-virtual"{if $pInfo->is_virtual == 1} style="display: none;"{/if}>

    <div class="row m-b-2">
        <div class="col-md-9 ">
            <div class="is-bundle bundle-volume-calc">
                <span>{$smarty.const.CALCULATE_VOLUME}</span>
                <label class="label-in">{Html::radio("bundle_volume_calc", $pInfo->bundle_volume_calc==0, ['value' => 0])} <span class="title">{$smarty.const.VOLUME_BY_CHILDREN}</span></label>
                <label class="label-in">{Html::radio("bundle_volume_calc", $pInfo->bundle_volume_calc==1, ['value' => 1])} <span class="title">{$smarty.const.OWN_VOLUME}</span></label>
                <label class="label-in">{Html::radio("bundle_volume_calc", $pInfo->bundle_volume_calc==2, ['value' => 2])} <span class="title">{$smarty.const.OWN_VOLUME_AND_CHILDREN}</span></label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric_system">
                {$metric = $smarty.const.WEIGHT_UNIT_DEFAULT|default:'KG' == 'KG'}
                <span {if $metric}class="selected"{/if} data-class="dimmens_cm">{$smarty.const.TEXT_METRIC_SYSTEM}</span>
                <span {if !$metric}class="selected"{/if} data-class="dimmens_in">{$smarty.const.TEXT_ENGLISH_SYSTEM}</span>
            </div>
        </div>
    </div>


  <div class="cartoon_wrapper">
    {if $ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')}
      {$ext::productBlock($pInfo, $app->controller->view->pack_unit_price_tabs_data|default:null, $app->controller->view->packaging_unit_price_tabs_data|default:null)}
    {else}
        <div class="widget box box-no-shadow">
            <div class="widget-header"><h4>{$smarty.const.TEXT_PRODUCT_SIZE}</h4></div>
            <div class="widget-content after">
                <div class="size-pack-col">
                    <div class="dimmens_cm dimmens">
                        <div class="edp-line">
                            <label class="addcolonm">{$smarty.const.TEXT_WIGHT_KG}</label>
                            <input type="text" name="weight_cm" value="{$pInfo->weight_cm}" class="form-control form-control-small js_convert" data-target="weight_in" data-unit="kg">
                        </div>
                    </div>
                </div>
                <div class="size-pack-col dimmens_in dimmens">
                    <div class="edp-line">
                        <label class="addcolonm">{$smarty.const.TEXT_WEIGHT_LBS}</label>
                        <input type="text" name="weight_in" value="{$pInfo->weight_in}" class="form-control form-control-small js_convert" data-target="weight_cm" data-unit="lbs">
                    </div>
                </div>
            </div>
        </div>
            
    {/if}
  </div>
</div>
{foreach \common\helpers\Hooks::getList('categories/productedit', 'size/bottom') as $filename}
    {include file=$filename}
{/foreach}


<div class="popup-box-wrap-page" style="display: none" id="packaging-price-management">
  <div class="around-pop-up-page"></div>
  <div class="popup-box-page">
    <div class="pop-up-close-page"></div>
    <div class="pop-up-content-page">
      <div class="popup-heading">
        {$smarty.const.SET_UP_DETAILS}
      </div>
      <div class="popup-content">
        <div class="tabbable tabbable-custom">
          <div class="tab-content">
            <div class="tab-prices">
              {if is_array($app->controller->view->groups) && $app->controller->view->groups|@count > 0}
              <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs">
                  <li class="active" data-bs-toggle="tab" data-bs-target="#tab_packaging-0"><a><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                  {foreach $app->controller->view->groups as $groups_id => $group}
                    <li data-bs-toggle="tab" data-bs-target="#tab_packaging-{$groups_id}">
                      <a>
                        <span>{$group['groups_name']}</span>
                      </a>
                    </li>
                  {/foreach}
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_packaging-0">
                    {/if}
                    <div style="padding-right: 65px">
                      <div class="t-row js_packaging_group_fullprice" data-group_discount="0">
                        <div class="t-col-3">
                          <label><input type="radio" name="ifpopt_packaging_0" value="-2">Use base product price</label>
                          <label><input type="radio" name="ifpopt_packaging_0" value="1">Set price</label>
                        </div>
                        <div class="t-col-3">
                          <label for="">{$smarty.const.TEXT_NET_PRICE}</label>
                          <div class=""><input type="text" name="packaging_full_prices[0]" value="{$pInfo->products_price_packaging}" class="form-control form-control-small js_inventory_price_input" onkeyup="updateGross()"></div>
                        </div>
                        <div class="t-col-3">
                          <label for="">{$smarty.const.TEXT_GROSS_PRICE}</label>
                          <div class=""><input type="text" name="packaging_full_gross_prices[0]" value="" class="form-control form-control-small" onkeyup="updateNet()"></div>
                        </div>
                      </div>
                      <div class="t-row">
                        <div class="t-col-3">
                          <label for="">{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
                        </div>
                        <div class="t-col-3">
                          <input type="checkbox" value="1" name="full_qty_discount_status_packaging" class="check_qty_discount_packaging" {if isset($app->controller->view->qty_discounts_packaging) && $app->controller->view->qty_discounts_packaging|@count > 0} checked="checked" {/if} />
                        </div>
                        <div class="t-col-3"></div>
                      </div>
                    </div>

                    <div class="qty-discount-wrap">

                      <div class="wrap-quant-discount-full-price-packaging">
                        {if isset($app->controller->view->qty_discounts_packaging[0]) && $app->controller->view->qty_discounts_packaging[0]|@count > 0}
                          {foreach $app->controller->view->qty_discounts_packaging[0] as $qty => $price}
                            <div class="quant-discount-line after div_qty_discount_packaging" {if not {$app->controller->view->qty_discounts_packaging|@count} > 0} style="display:none;" {/if}>
                              <div>
                                <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_full_qty_packaging[]" value="{$qty}">
                              </div>
                              <div>
                                <label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_full_price_packaging[]" value="{$price}" onkeyup="updateGross()">
                              </div>
                              <div>
                                <label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_full_gross_price_packaging[]" value="" onkeyup="updateNet()">
                              </div>
                              <span class="rem-quan-line"></span>
                            </div>
                          {/foreach}
                        {/if}
                      </div>
                      <div class="quant-discount-btn div_qty_discount_packaging" {if isset($app->controller->view->qty_discounts_packaging) && $app->controller->view->qty_discounts_packaging|@count == 0} style="display:none;" {/if}>
                        <span class="btn btn-add-more-inv-qty-full-price" data-uprid="packaging" data-aprid="packaging">{$smarty.const.TEXT_ADD_MORE}</span>
                      </div>
                    </div>

                    {if is_array($app->controller->view->groups) && $app->controller->view->groups|@count > 0}
                  </div>

                  {foreach $app->controller->view->groups as $groups_id => $group}

                    <div class="tab-pane" id="tab_packaging-{$groups_id}">

                      <div class="t-row js_packaging_group_fullprice" data-uprid="packaging" data-group_id="{$groups_id}" data-group_discount="{$group['groups_discount']}">
                        <div class="t-col-3">
                          <label><input type="radio" name="ifpopt_packaging_{$groups_id}" value="-2">{$smarty.const.TEXT_PRICE_SWITCH_MAIN_PRICE}</label>
                          <label><input type="radio" name="ifpopt_packaging_{$groups_id}" value="1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_OWN_PRICE, $group['groups_name'])}</label>
                          <label><input type="radio" name="ifpopt_packaging_{$groups_id}" value="-1">{sprintf($smarty.const.TEXT_PRICE_SWITCH_DISABLE, $group['groups_name'])}</label>
                        </div>
                        <div class="js_inventory_price_block">
                          <div class="t-col-3">
                            <label for="">{$smarty.const.TEXT_NET_PRICE}</label>
                            <div class=""><input type="text" name="packaging_full_prices[{$groups_id}]" value="{if isset($app->controller->view->products_group_price_packaging[$groups_id])}{$app->controller->view->products_group_price_packaging[$groups_id]}{/if}" class="form-control form-control-small js_inventory_price_input" onkeyup="updateGross()"></div>
                          </div>
                          <div class="t-col-3">
                            <label for="">{$smarty.const.TEXT_GROSS_PRICE}</label>
                            <div class=""><input type="text" name="packaging_full_gross_prices[{$groups_id}]" value="" class="form-control form-control-small" onkeyup="updateNet()"></div>
                          </div>
                        </div>
                      </div>

                      <div class="qty-discount-wrap">

                        <div class="wrap-quant-discount-full-price-packaging-{$groups_id}">
                          {if isset($app->controller->view->qty_discounts_packaging[$groups_id]) && $app->controller->view->qty_discounts_packaging[$groups_id]|@count > 0}
                            {foreach $app->controller->view->qty_discounts_packaging[$groups_id] as $qty => $price}
                              <div class="quant-discount-line after div_qty_discount_packaging" {if not {$app->controller->view->qty_discounts_packaging|@count} > 0} style="display:none;" {/if}>
                                <div>
                                  <label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_full_qty_packaging_{$groups_id}[]" value="{$qty}">
                                </div>
                                <div>
                                  <label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_full_price_packaging_{$groups_id}[]" value="{$price}" onkeyup="updateGross()">
                                </div>
                                <div>
                                  <label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_full_gross_price_packaging_{$groups_id}[]" value="" onkeyup="updateNet()">
                                </div>
                                <span class="rem-quan-line"></span>
                              </div>
                            {/foreach}
                          {/if}
                        </div>
                        <div class="quant-discount-btn div_qty_discount_packaging" {if isset($app->controller->view->qty_discounts_packaging) && $app->controller->view->qty_discounts_packaging|@count == 0} style="display:none;" {/if}>
                          <span class="btn btn-add-more-inv-qty-price" data-groups_id="{$groups_id}" data-uprid="packaging" data-aprid="packaging">{$smarty.const.TEXT_ADD_MORE}</span>
                        </div>
                      </div>

                    </div>
                  {/foreach}

                </div>
              </div>
              {/if}

            </div>

          </div>
        </div>

      </div>
      <div class="popup-buttons">
        <span class="btn btn-primary btn-save2"> OK </span>

        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    var volume_in_edit = $('#volume_in_edit');
    var volume_cm_edit = $('#volume_cm_edit');
    var volume_cm = $('#volume_cm');
    var volume_in = $('#volume_in');
    var volume_cm_calc = $('#volume_cm_calc');
    var volume_in_calc = $('#volume_in_calc');

    var volume_init_cm = {$pInfo->volume_weight_cm};
    var volume_init_in = {$pInfo->volume_weight_in};

    volume_in_edit.on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).blur();
        volume_in.prop('readonly',true);
        volume_in_calc.hide();
        if ($(this).hasClass('active')) {
            volume_in.prop('readonly',false);
            volume_in_calc.show();
        }
    });

    volume_cm_edit.on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).blur();
        volume_cm.prop('readonly',true);
        volume_cm_calc.hide();
        if ($(this).hasClass('active')) {
            volume_cm.prop('readonly',false);
            volume_cm_calc.show();
        }
    });

    volume_in_calc.on('click', function (e) {
        e.preventDefault();
        updateCalculatedFields(false, true);
    });
    volume_cm_calc.on('click', function (e) {
        e.preventDefault();
        updateCalculatedFields(true, false);
    });

function updateCalculatedFields(changeCm, changeIn) {

    changeCm = changeCm || false;
    changeIn = changeIn || false;

    var lenght = parseInt($('input[name="length_cm"]').val());
    var width = parseInt($('input[name="width_cm"]').val());
    var height = parseInt($('input[name="height_cm"]').val());
    var weight = parseInt($('input[name="weight_cm"]').val());

    var volume = parseFloat(volume_cm.val());
    var newValue = parseFloat((lenght*width*height)/{$smarty.const.VOLUME_WEIGHT_COEFFICIENT});

    volume_cm.css('border-color', 'rgb(204, 204, 204)');
    if (volume_init_cm <= 0.00 || changeCm) {
        volume_cm.val(newValue.toFixed(2));
    } else {
        if (volume.toFixed(2) !== newValue.toFixed(2)) {
            volume_cm.css('border-color', 'rgb(255,0,0)');
        }
    }
    if (volume > weight) {
        $('#chargeable_cm').val('Volume');
        $('#chargeable_in').val('Volume');
    } else {
        $('#chargeable_cm').val('Weight');
        $('#chargeable_in').val('Weight');
    }
    
    lenght = parseInt($('input[name="length_in"]').val());
    width = parseInt($('input[name="width_in"]').val());
    height = parseInt($('input[name="height_in"]').val());
    volume = parseFloat(volume_in.val());
    newValue = parseFloat((lenght*width*height)/{$smarty.const.VOLUME_WEIGHT_COEFFICIENT});
    volume_in.css('border-color', 'rgb(204, 204, 204)');
    if (volume_init_in <= 0.00 || changeIn) {
        volume_in.val(newValue.toFixed(2));
    } else {
        if (volume.toFixed(2) !== newValue.toFixed(2)) {
            volume_in.css('border-color', 'rgb(255,0,0)');
            //console.log(newValue.toFixed(2));
        }
    }

}
$(document).ready(function(){
/*
    $('.js_pack_unit_group_fullprice').on('change_state', function(event, state) {
      var $block = $(this);
      var $all_input = $block.find('[name^="pack_unit_full_prices"],[name^="pack_unit_full_gross_prices"]');
      var base_ref = 'input[name="pack_unit_full_prices[0]"]';
      var $main_input = $block.find('.js_inventory_price_input');
      //
      var base_val = parseFloat($(base_ref).val()) || 0;
      var new_val = ((100-parseFloat($block.attr('data-group_discount')))/100*base_val);

      //
      $(base_ref).on('change',function() {
        $block.find('[name^="ifpopt_"][name!=ifpopt_pack_unit_0]').filter('[value="-2"]').trigger('click');
      });

      var $dep_block = $block.closest('.tab-pane').find('.qty-discount-wrap');
      if ( parseFloat(state) == -1 ) {
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        $main_input.val('-1');
        $block.find('.js_inventory_price_block').hide();
        $dep_block.hide();
      } else if(parseFloat(state) == -2) {
        if ( $dep_block.is(':hidden') ) $dep_block.show();
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        $main_input.val('');
        $main_input.trigger('keyup');
        $all_input.attr({ readonly:'readonly',disabled:'disabled' });
        $block.find('.js_inventory_price_block').show();
      } else {
        if ( $dep_block.is(':hidden') ) $dep_block.show();
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        if ( parseFloat($main_input.val()) <= 0 ) {
          $main_input.val('');
          $main_input.trigger('keyup');
        }
        $block.find('.js_inventory_price_block').show();
      }
    });

    $('.js_packaging_group_fullprice').on('change_state', function(event, state) {
      var $block = $(this);
      var $all_input = $block.find('[name^="packaging_full_prices"],[name^="packaging_full_gross_prices"]');
      var base_ref = 'input[name="packaging_full_prices[0]"]';
      var $main_input = $block.find('.js_inventory_price_input');
      //
      var base_val = parseFloat($(base_ref).val()) || 0;
      var new_val = ((100-parseFloat($block.attr('data-group_discount')))/100*base_val);

      //
      $(base_ref).on('change',function() {
        $block.find('[name^="ifpopt_"][name!=ifpopt_packaging_0]').filter('[value="-2"]').trigger('click');
      });

      var $dep_block = $block.closest('.tab-pane').find('.qty-discount-wrap');
      if ( parseFloat(state) == -1 ) {
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        $main_input.val('-1');
        $block.find('.js_inventory_price_block').hide();
        $dep_block.hide();
      } else if(parseFloat(state) == -2) {
        if ( $dep_block.is(':hidden') ) $dep_block.show();
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        $main_input.val('');
        $main_input.trigger('keyup');
        $all_input.attr({ readonly:'readonly',disabled:'disabled' });
        $block.find('.js_inventory_price_block').show();
      } else {
        if ( $dep_block.is(':hidden') ) $dep_block.show();
        $all_input.removeAttr('readonly');
        $all_input.removeAttr('disabled');
        if ( parseFloat($main_input.val()) <= 0 ) {
          $main_input.val('');
          $main_input.trigger('keyup');
        }
        $block.find('.js_inventory_price_block').show();
      }
    });

    $('.check_qty_discount_pack_unit').bootstrapSwitch({
      onSwitchChange: function (element, argument) {
        if (argument) {
          $(this).closest('.tab-prices').find('.div_qty_discount_pack_unit').show();
        } else {
          $(this).closest('.tab-prices').find('.div_qty_discount_pack_unit').hide();
        }
        return true;
      },
      onText: "{$smarty.const.SW_ON}",
      offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px'
    });

    $('.check_qty_discount_packaging').bootstrapSwitch({
      onSwitchChange: function (element, argument) {
        if (argument) {
          $(this).closest('.tab-prices').find('.div_qty_discount_packaging').show();
        } else {
          $(this).closest('.tab-prices').find('.div_qty_discount_packaging').hide();
        }
        return true;
      },
      onText: "{$smarty.const.SW_ON}",
      offText: "{$smarty.const.SW_OFF}",
      handleWidth: '20px',
      labelWidth: '24px'
    });

    $('.btn-add-more-inv-qty-price').click(function() {
      if ($(this).attr('data-groups_id') > 0) {
        $('.wrap-quant-discount-full-price-' + $(this).attr('data-aprid') + '-' + $(this).attr('data-groups_id')).append('<div class="quant-discount-line after div_qty_discount_pack_unit"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_qty_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value=""></div><div><label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_price_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value="" onkeyup="updateGross()"></div><div><label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_gross_price_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value="" onkeyup="updateNet()"></div><span class="rem-quan-line"></span></div>');
      } else {
        $('.wrap-quant-discount-full-price-' + $(this).attr('data-aprid')).append('<div class="quant-discount-line after div_qty_discount_pack_unit"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_qty_' + $(this).attr('data-uprid') + '[]" value=""></div><div><label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_price_' + $(this).attr('data-uprid') + '[]" value="" onkeyup="updateGross()"></div><div><label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_gross_price_' + $(this).attr('data-uprid') + '[]" value="" onkeyup="updateNet()"></div><span class="rem-quan-line"></span></div>');
      }
      $('.rem-quan-line').unbind('click').click(function() {
        $(this).parent().remove();
      });
    });

    $('.btn-add-more-inv-qty-full-price').click(function() {
      if ($(this).attr('data-groups_id') > 0) {
        $('.wrap-quant-discount-full-price-' + $(this).attr('data-aprid') + '-' + $(this).attr('data-groups_id')).append('<div class="quant-discount-line after div_qty_discount_pack_unit"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_full_qty_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value=""></div><div><label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_full_price_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value="" onkeyup="updateGross()"></div><div><label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_full_gross_price_' + $(this).attr('data-uprid') + '_' + $(this).attr('data-groups_id') + '[]" value="" onkeyup="updateNet()"></div><span class="rem-quan-line"></span></div>');
      } else {
        $('.wrap-quant-discount-full-price-' + $(this).attr('data-aprid')).append('<div class="quant-discount-line after div_qty_discount_pack_unit"><div><label>{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</label><input type="text" class="form-control" name="inventory_discount_full_qty_' + $(this).attr('data-uprid') + '[]" value=""></div><div><label>{$smarty.const.TEXT_NET}</label><input type="text" class="form-control" name="inventory_discount_full_price_' + $(this).attr('data-uprid') + '[]" value="" onkeyup="updateGross()"></div><div><label>{$smarty.const.TEXT_GROSS}</label><input type="text" class="form-control" name="inventory_discount_full_gross_price_' + $(this).attr('data-uprid') + '[]" value="" onkeyup="updateNet()"></div><span class="rem-quan-line"></span></div>');
      }
      $('.rem-quan-line').unbind('click').click(function() {
        $(this).parent().remove();
      });
    });

    $('.rem-quan-line').click(function() {
      $(this).parent().remove();
    });

    $('.js_pack_unit_group_fullprice [name^="ifpopt_"]').on('click',function() {
      $(this).parents('.js_pack_unit_group_fullprice').trigger('change_state',[$(this).val()]);
    });

    $('.js_packaging_group_fullprice [name^="ifpopt_"]').on('click',function() {
      $(this).parents('.js_packaging_group_fullprice').trigger('change_state',[$(this).val()]);
    });

    $('.js_pack_unit_group_fullprice').each(function() {
      var $main_input = $(this).find('.js_inventory_price_input');
      var switch_name_locate = 'ifpopt_';
      var empty_val = $main_input.val();
      var price = parseFloat($main_input.val());
      if (empty_val == '') {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
      } else if (price == -1) {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-1"]').trigger('click');
      } else if (price == -2) {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
      } else {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="1"]').trigger('click');
      }
      //$(this).trigger('change_state',[]);
    });

    $('.js_packaging_group_fullprice').each(function() {
      var $main_input = $(this).find('.js_inventory_price_input');
      var switch_name_locate = 'ifpopt_';
      var empty_val = $main_input.val();
      var price = parseFloat($main_input.val());
      if (empty_val == '') {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
      } else if (price == -1) {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-1"]').trigger('click');
      } else if (price == -2) {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="-2"]').trigger('click');
      } else {
        $(this).find('[name^="'+switch_name_locate+'"]').filter('[value="1"]').trigger('click');
      }
      //$(this).trigger('change_state',[]);
    });
*/
    function doRound(x, places) {
      return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
    }

    var $convertable = $('.js_convert');
    var convert_map = {
      'in': { 'cm': 2.539959 },
      'cm': { 'in': 0.393707143 },
      'kg': { 'lbs': 2.20462262 },
      'lbs': { 'kg': 0.45359237 }
    };
    $convertable.each( function() { $(this).attr('data-compare',$(this).val()); } );
    $convertable.on('keyup',function() {
      var $source = $(this);
      if ($source.attr('data-compare')===$source.val()) return;
      var $target = $convertable.filter('[name="'+$source.attr('data-target')+'"]');
      var source_unit = $source.attr('data-unit'),
              source_val = parseFloat($source.val()) || 0;
      var target_unit = $target.attr('data-unit');
      if ( convert_map[source_unit] && convert_map[source_unit][target_unit] ) {
        $target.val( doRound(source_val*convert_map[source_unit][target_unit],2) );
        $target.attr('data-compare', $target.val());
      }
      $source.attr('data-compare', $(this).val());
      updateCalculatedFields();
    });
    updateCalculatedFields();
  });
</script>

<script type="text/javascript">
  $('.attachment-upload-container').each(function() {

    var _attach = $(this);

    $('.upload-file', _attach).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload/index')}",
      maxFiles: 1,
      uploadMultiple: false,
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _attach).val(e.name);
        $('.upload-remove', _attach).on('click', function(){
          $('.dz-details', _attach).remove()
        })
      },
      dataType: 'json',
      previewTemplate: '<div class="dz-details" style="display: none;"></div>',
      drop: function(){
        $('.upload-file', _attach).html('')
      },
      success: function(e, data) {
        $('#upload-file-name').text(e.name);
        //console.log( e.name );
        //$('.upload-file', _this).html('');

      },
    });

  });

</script>
