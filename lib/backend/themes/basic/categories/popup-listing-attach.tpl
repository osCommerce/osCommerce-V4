<div>
    <form action="{Yii::$app->urlManager->createUrl(['categories/listing-attach', 'product_id' => $product_id])}" method="post" onsubmit="return confirmAttach(this)">
        <div class="pop-up-content">
            <div class="popup-heading">{$smarty.const.BUTTON_ATTACH_TO_PARENT_LISTING_PRODUCT}</div>
            <div class="popup-content">
                {sprintf(TEXT_SELECT_PARENT_PRODUCT_S,$product_name)}
                <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;margin-top:8px">
                    <div class="widget-header">
                        <h4>{$smarty.const.FIND_PRODUCTS}</h4>
                        <div class="box-head-serch after">
                            <input type="search" id="find-search-by-products" data-target="find-search-products" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control find-search-by-products">
                            <button onclick="return false"></button>
                        </div>
                    </div>
                    <div class="widget-content">
                        <select id="find-search-products" name="parent_product_id" size="25" style="width: 100%; height: 293px; border: none;" ondblclick="$(this.form).trigger('submit')">
                        </select>
                    </div>
                </div>

                <div>
                    <label><input type="checkbox" name="mark_parent_as_master" value="1" class="uniform"> {$smarty.const.TEXT_SUB_PRODUCT_PARENT_MARK_AS_MASTER}</label>
                </div>
            </div>
        </div>
        <div class="noti-btn">
            <div class="btn-left">
                <a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return closePopup();">{$smarty.const.IMAGE_CANCEL}</a>
            </div>
            <div class="btn-right">
                <button class="btn btn-primary" type="submit">{$smarty.const.IMAGE_CONFIRM}</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    function confirmAttach(form) {
        if (!$(form).find('[name="parent_product_id"]').val()){
            bootbox.alert('{$smarty.const.TEXT_SELECT_PRODUCT|escape:'javascript'}');
            return false;
        }
        $.post($(form).attr('action'), $(form).serializeArray(), function(){
            closePopup();
            if ( typeof resetStatement === 'function') resetStatement();
        });
        return false;
    }
    var color = '#ff0000';
    var phighlight = function(obj, reg){
        if (reg.length == 0) return;
        $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
        return;
    }
    $(document).ready(function(){
        $('.find-search-by-products').on('focus keyup', function(e) {
            var target = $(this).attr('data-target');
            var str = $(this).val();
            $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q="+encodeURIComponent(str)+"&child_skip=1&not={$product_id}", function( data ) {
                $( "select#"+target ).html( data );
                psearch = new RegExp(str, 'i');
                $.each($('select#'+target).find('option'), function(i, e){
                    if (psearch.test($(e).text())){
                        phighlight(e, str);
                    }
                });
            });
        });
        $('.find-search-by-products').trigger('focus');
        {*
        $.post( "{Yii::$app->urlManager->createUrl('categories/product-search')}?q=&not={$pInfo->products_id}", function( data ) {
            $('.xsell-search-by-products').each(function(){
                var target = $(this).attr('data-target');
                $( "select#"+target ).html( data );
            });
        });*}
    });

</script>