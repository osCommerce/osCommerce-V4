<input type="hidden" name="without_inventory" id="productInventorySwitch" value="{$pInfo->without_inventory}">
<div class="btn-box-inv-price btn-in after">
    <span
            class="full-attr-price{if !$pInfo->without_inventory} active{/if}" data-value="0">{$smarty.const.TEXT_INVENTORY_SWITCH_ON}
    </span><span
            class="add-attr-price{if $pInfo->without_inventory} active{/if}" data-value="1">{$smarty.const.TEXT_INVENTORY_SWITCH_OFF}
    </span>
</div>
<style>
    .inventory_off .inventory-box{ display: none }
</style>
<script>
    $(document).ready(function(){
        (function($root){

            $root
                .on('inventory_on',function(event, init){
                    $root.addClass('inventory_on').removeClass('inventory_off');
                    $('.btn-in-pr .full-attr-price').removeAttr('disabled');
                    $('.btn-in-pr .full-attr-price').removeClass('dis_module');
                    if ( !init && typeof updateInventoryBox === 'function' ) updateInventoryBox();
                })
                .on('inventory_off',function(event, init){
                    $root.addClass('inventory_off').removeClass('inventory_on');

                    $('.btn-in-pr .add-attr-price').each(function(){
                        if ($(this).hasClass('active')) return;
                        $(this).trigger('click');
                    });

                    $('.btn-in-pr .full-attr-price').attr('disabled','disabled');
                    $('.btn-in-pr .full-attr-price').addClass('dis_module');

                    $('#product-inventory-box').html('');
                });

        })($('#attributes'));

        setTimeout(function(){
            if ( $('#productInventorySwitch').val()=='1' ){
                $('#attributes').trigger('inventory_off',[true]);
            }else if ( $('#productInventorySwitch').val()=='0' ){
                $('#attributes').trigger('inventory_on', [true]);
            }
        },200);

        $('#productInventorySwitch').on('change',function(){
            if ( $(this).val()=='1' ){
                $('#attributes').trigger('inventory_off');
            }else if ( $(this).val()=='0' ){
                $('#attributes').trigger('inventory_on');
            }
            {if true}
            var currentlySelected = [];
            $('#attributes .js-option').each( function(){
                var option_id = $(this).data('option_id');
                $(this).find('.js-option-value').each( function(){
                    currentlySelected.push([ option_id, $(this).data('option_value_id') ]);
                } );
            } );
            $('#selected_attributes_box').html('');
            addSelectedAttribute(currentlySelected);
            {else}
            var attrData = $('#attributes').find(':input').serializeArray();
            attrData.push({ name:'products_id', value: '{$pInfo->products_id}' });
            $.post(
                "{Yii::$app->urlManager->createUrl('categories/selected-attributes')}",
                attrData,
                function(data){
                    $('#selected_attributes_box').html(data);
                }
            );
            {/if}


        });
        $('.btn-in > span').on('click',function(e){
            var $inp = $('#productInventorySwitch');
            if ( ($inp.val()||'0')!=$(e.target).data('value') ){
                $inp.val($(e.target).data('value'));
                $inp.trigger('change');
                $('.btn-in > span').toggleClass('active');
            }
        });


    });
</script>