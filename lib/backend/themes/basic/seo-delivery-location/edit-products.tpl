{if $location_data['product_set_rule']!=-1}
<label><input type="checkbox" id="setParent" name="product_set_rule" value="1" {if $location_data['product_set_rule']==1}checked="checked"{/if}> {$smarty.const.TEXT_DELIVERY_LOCATION_USE_PARENT_PRODUCTS}</label>
{/if}
    <div id="setLayer">
        {include file="product-set.tpl" set_products=$set_products}
    </div>
{if $location_data['product_set_rule']!=-1}
<style type="text/css">
    .set_block_inactive{
        opacity: 0.5;
    }
    .set_block_inactive *{
        cursor: default;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        if(document.getElementById('setParent').checked){
            $('#setLayer').addClass('set_block_inactive');
        }
        $('#setParent').on('click',function () {
            $('#setLayer').toggleClass('set_block_inactive');
        });
        $(document).on('click focus keydown', '#setLayer.set_block_inactive',function(e){
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    });
</script>
{/if}