<div class="xl-pr-box" id="box-xl-pr">
    <div class="after">
        <div class="attr-box attr-box-1">
            <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIND_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" id="txtSetSearchProducts" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                    <select id="selSetSearchProducts" size="25" style="width: 100%; height: 100%; border: none;" ondblclick="addSelectedProduct()" multiple="multiple">
                    </select>
                </div>
            </div>
        </div>
        <div class="attr-box attr-box-2">
            <span class="btn btn-primary" onclick="addSelectedProduct()"></span>
        </div>
        <div class="attr-box attr-box-3">
            <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" id="txtSearchAssigned" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                    <table class="table assig-attr-sub-table set-assigned-products">
                        <thead>
                        <tr role="row">
                            <th></th>
                            <th>{$smarty.const.TEXT_IMG}</th>
                            <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $set_products as $eKey => $product}
                            {include file="product-set-item.tpl" product=$product}
                        {/foreach}
                        </tbody>
                    </table>
                    <input type="hidden" value="" name="products_set_sort_order" id="assigned_sort_order"/>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function addSelectedProduct() {
        $( 'select#selSetSearchProducts option:selected' ).each(function() {
            var products_id = $(this).val();
            if ( $('input[name="set_products_id[]"][value="' + products_id + '"]').length ) {
                //already exist
            } else {
                $.post("{Yii::$app->urlManager->createUrl('seo-delivery-location/set-new-product')}", {
                    'products_id': products_id
                }, function(data, status) {
                    if (status == "success") {
                        $( ".set-assigned-products tbody" ).append(data);
                    } else {
                        alert("Request error.");
                    }
                },"html");
            }
        });

        return false;
    }

    function deleteSelectedProduct(obj) {
        $(obj).parent().remove();
        return false;
    }

    var color = '#ff0000';
    var phighlight = function(obj, reg){
        if (reg.length == 0) return;
        $(obj).html($(obj).text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
        return;
    }

    var searchHighlightExisting = function(e){
        var $rows = $(e.data.rows_selector);
        var search_term = $(this).val();
        $rows.each(function(){
            var $row = $(this);
            var $value_text = $row.find(e.data.text_selector);
            var search_match = true;

            if ( !$row.data('raw-value') ) $row.data('raw-value', $value_text.html());
            var prop_value = $row.data('raw-value');
            if ( search_term.length>0 ) {
                var searchRe = new RegExp(".*" + (search_term + "").replace(/([.?*+\^\$\[\]\\(){}|-])/g, "\\$1") + ".*", 'i');
                if (searchRe.test(prop_value)) {
                    phighlight($value_text, search_term);
                } else {
                    $value_text.html(prop_value);
                    search_match = false;
                }
            }else{
                $value_text.html(prop_value);
            }

            if ( search_match ) {
                $row.show();
            }else{
                $row.hide();
            }
        });
    };
    $(document).ready(function() {
        $('#txtSearchAssigned').on('focus keyup', { rows_selector: '.set-assigned-products tbody tr', text_selector: '.ast-name-element'}, searchHighlightExisting);

        $('#txtSetSearchProducts').on('focus keyup', function(e) {
            var str = $(this).val();
            $.post( "{Yii::$app->urlManager->createUrl('seo-delivery-location/set-product-search')}?platform_id={$platform_id}&q="+encodeURIComponent(str), function( data ) {
                $( "select#selSetSearchProducts" ).html( data );
                psearch = new RegExp(str, 'i');
                $.each($('select#selSetSearchProducts').find('option'), function(i, e){
                    if (psearch.test($(e).text())){
                        phighlight(e, str);
                    }
                });
            });
        }).keyup();

        $( ".set-assigned-products tbody" ).sortable({
            handle: ".sort-pointer",
            axis: 'y',
            update: function( event, ui ) {
                var data = $(this).sortable('serialize', { attribute: "prefix" });
                $("#assigned_sort_order").val(data);
            },
        }).disableSelection();

    });
</script>