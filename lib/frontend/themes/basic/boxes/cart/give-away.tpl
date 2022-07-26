{use class="frontend\design\Info"}
<h2>{$smarty.const.TEXT_GA_PRODUCTS}</h2>
<div class="cart-listing type-2 w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
<div class="ga_wrapper">
  <div class="ga_row after">
  {foreach $products as $product name=foo}
    {*if $smarty.foreach.foo.iteration % 2 == 1 && $smarty.foreach.foo.iteration != 1}
    </div><div class="ga_row after">
    {/if*}
    <div class="ga_column after">
      <div class="ga_img">
        {if $product.products_status}
          <a href="{$product.products_link}"><img src="{$product.image}" alt="{$product.image_alt|escape}"></a>
        {else}
          <img src="{$product.image}" alt="{$product.image_alt|escape}">
        {/if}
      </div>
      <div class="ga_ovr">
        {if $product.active}
        <div data-action="{$product.ga_form_action}" data-method="post" class="frmGaProduct">
        {/if}
        <div class="ga_name">
          {if $product.products_status}
            <a href="{$product.products_link}">{$product.products_name}</a>
          {else}
            {$product.products_name}
          {/if}
          {if count($product.attributes)>0}
          <div class="ga_attributes">
            {foreach $product.attributes as $item}
              <div>
                <select class="js_ga_select" name="giveaways[{$product.ga_idx}][id][{$item.id}]" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}" data-empty-option="{$smarty.const.SELECT|escape:'html'} {$item.title|escape:'html'}">
                  {foreach $item.options as $option}
                    <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                  {/foreach}
                </select>
              </div>
            {/foreach}
           </div>
           {/if}
        </div>
        <div class="ga_qty">{$product.price_b}</div>
        <div class="ga_price after">
          {if $product.active}
            <input type="hidden" name="giveaways[{$product.ga_idx}][products_id]" value="{$product.products_id}">
            <span class="checkBoxWrap"><input type="checkbox" name="giveaway_switch[{$product.ga_idx}]" value="1" {if $product.single_checkbox}checked{/if} onClick="this.form.submit();"></span>
          {/if}
          {$product.giveaway_note}
        </div>
        {if $product.active}
        </div>
        {/if}
      </div>
    </div>
  {/foreach}
  </div>
</div>
</div>
<script type="text/javascript">
  tl([
    '{Info::themeFile('/js/main.js')}',
    '{Info::themeFile('/js/bootstrap-switch.js')}'
  ], function(){
    $(window).on('load', function(){
      $('.ga_row').inRow(['.ga_column'], 2)
    });

    $('.js_ga_select').each(function(){
      var $select = $(this);
      if ( !$select.attr('data-empty-option') ) return;
      $select.prepend('<option value="0">'+$select.attr('data-empty-option')+'</option>')
    });

    $('.frmGaProduct').on('submit',function() {
      var need_select_option = false,
          chck = $(this).find('input[name^="giveaway_switch"]');
      if ( chck.length>0 && chck[0].checked==false ) {
        return true;
      }
      $('.js_ga_select').each(function(){ if ($(this).val()=='0') need_select_option=true; });
      if ( need_select_option ) {
        if ( typeof alertMessage == 'function' ) {
          alertMessage('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}');
        }else{
          alert('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}');
        }

        if ( chck.length>0 ) {
          $(chck[0]).bootstrapSwitch('state',false/*state*/,true/*skipevent*/);
          chck[0].checked = false;
        }
        return false;
      }
      return true;
    });

    {\frontend\design\Info::addBoxToCss('switch')}
    $("input[name^='giveaway_switch']").bootstrapSwitch({
      onSwitchChange: function (element, arguments) {
        $(this).val(10); // generally should be radio (max 1 GAW in php code restriction) saved for future multi gaw features.
        var $divna = $(this).parents('div[data-action]');

        var event = jQuery.Event( "submit" );
        $divna.trigger(event);
        if ( event.result ) {
          $(this.form).attr('action',$divna.attr('data-action'));
          $(this.form).attr('method',$divna.attr('data-method'));
          this.form.submit();
        }
        return true;
      },
      offText: '{TEXT_NO}',
      onText: '{TEXT_YES}'
    });
  })

</script>