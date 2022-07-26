{use class="Yii"}
{use class="frontend\design\Info"}
<div class="attributes product-attributes">
  {foreach $attributes as $item}
    {if $item['type'] == 'radio'}
      {include file="`$smarty.current_dir`/attributes/radio.tpl" item=$item options_prefix=$options_prefix}
    {else}
      {include file="`$smarty.current_dir`/attributes/select.tpl" item=$item  options_prefix=$options_prefix}
    {/if}
  {/foreach}
{if !Yii::$app->request->get('list_b2b')}
 {if not $isAjax }
{*&& $smarty.const.PRODUCTS_ATTRIBUTES_SHOW_SELECT!='True'*}
    <script type="text/javascript">
      tl(function(){
        $('.item-holder[data-item="{$products_id}"]').each(function() {
          update_attributes_list(this);
        });
{*
       update_attributes_list(
    $(this).closest('.item-holder[data-item="{$products_id}"]')
    /*$('form[data-form="{$products_id}"]')* /
    );*}
      });
    </script>
 {/if}  
{/if}
</div>

