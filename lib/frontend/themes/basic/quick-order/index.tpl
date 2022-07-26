{use class="Yii"}
{use class="\common\helpers\Html"}
{use class="frontend\design\Info"}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css"/>
<div class="block inform">
    <div class="box-block type-1">
        <div class="block" style="text-align: left;">
            <h1>{$smarty.const.TEXT_WHOLESALE_ORDER_FORM}</h1>
            {Html::beginForm(Yii::$app->urlManager->createUrl(['quick-order', 'action' => 'quick_buy']), 'post', ['class' => 'form-buy'])}

            <div class="all-prod-filter">
                <div class="desc_fil">{$smarty.const.TEXT_DESCRIPTION_FILTERS}</div>
                {frontend\design\boxes\FiltersAll::widget()}
            </div>

            <div class="all-prod-list">
                <table id="all-prod-list-table" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                          {foreach $columns as $column}
                            <th class="{$column.class}">{$column.title}</th>
                          {/foreach}
                            {*<th class="al_model">{$smarty.const.TEXT_MODEL}</th>
                            <th class="al_pr_name">{$smarty.const.TEXT_PRODUCT_NAME}</th>
                            <th class="al_price">{$smarty.const.TEXT_ATTRIBUTES}</th>
                            <th class="al_category">{$smarty.const.TEXT_MAIN_CATEGORY}</th>
                            <th class="al_category">{$smarty.const.TEXT_SUB_CATEGORY}</th>
                            <th class="al_price">{$smarty.const.TEXT_PRICE}</th>
                            <th class="al_stock">{$smarty.const.TEXT_STOCK}</th>
                            <th class="al_qty">{$smarty.const.TEXT_QTY}</th>
                            <th class="al_incart">{$smarty.const.TEXT_IN_YOUR_CART}</th>*}
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="all-scroll-div">
                <div class="main-width">

                    <div class="quick-order-total">
                        <div class="already-in-cart">
                            <span class="title">Already in cart:</span>
                            <span class="value" id="total_current">{$total}</span>
                        </div>
                        <div class="total-selected">
                            <span class="title">Selected:</span>
                            <span class="value" id="total_selected">0.00</span>
                        </div>
                        <div class="total-total">
                            <span class="title">Total Sum:</span>
                            <span class="value" id="total_total">{$total}</span>
                        </div>
                        <div class="total-total">
                            <button type="submit" class="btn btn-primary btn-buy add-to-cart" value="{$smarty.const.ADD_TO_CART|escape:'html'}">{$smarty.const.ADD_TO_CART}</button>
                        </div>
                    </div>

                </div>
            </div>
            <div id="selected_products"></div>
            {Html::endForm()}
        </div>
    </div>
</div>
<script>
    var set;
    var total = 0;
    var table;

    tl('https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.js', function () {
        var enabled = false;

        table = $('#all-prod-list-table').DataTable(
                {
                    "dom": '<"top"ip>rt<"bottom"ip><"clear">',
                    "processing": true,
                    "serverSide": true,
                    //"stateSave": true,
                    "lengthMenu": [100, 100],
                    "ajax": {
                        "url": "{$url}",
                        "type": "POST",
                        "data" : function ( d ) {
                            d._csrf = $('input[name^="_csrf"]').val();
                            d.filter = $('#selected_products').find('input[name^="qty"]').serialize();
                        },
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": [0, 1, 2, 3, 4, 5] }
                    ],
                    "columns": [
                      {$dtColumns}
                      /*
                        { "data": "model", "orderable": true, 'class': 'al-model' },
                        { "data": "name", "orderable": true, 'class': 'al-name'  },
                        { "data": "attributes", "orderable": true, 'class': 'al-attributes'  },
                        { "data": "top_category", "orderable": true, 'class': 'al-top-category'  },
                        { "data": "sub_category", "orderable": true, 'class': 'al-sub-category'  },
                        { "data": "price", "orderable": true, 'class': 'al-price'  },
                        { "data": "stock", "orderable": false, 'class': 'al-stock'  },
                        { "data": "qty_box", "orderable": false, 'class': 'al-qty'  },
                        { "data": "incart", "orderable": false, 'class': 'al-incart'  }*/
                    ],
                    "order": [[1, 'asc']],
                    "drawCallback": function (settings) {
                        //$('.total_value').html(settings.json.currency.left_symbol + "0.00" + settings.json.currency.right_symbol);
                        {Info::addBoxToCss('quantity')}
                        $('input.qty-inp').quantity();
                        $('input.qty-inp').off('change').on('change', function (e) { 
                            var qty = $(this).val();
                            var id = $(this).data('id');
                            var type = $(this).data('type');
                            if (type == 'unit') {
                                $('#selected_products').find('input[name="qty['+id+'][0]"]').remove();
                                if (qty > 0) {
                                    let hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'qty['+id+'][0]';
                                    hidden.value = qty;
                                    $('#selected_products').append(hidden);
                                }
                            } else if (type == 'pack_unit') {
                                $('#selected_products').find('input[name="qty['+id+'][1]"]').remove();
                                if (qty > 0) {
                                    let hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'qty['+id+'][1]';
                                    hidden.value = qty;
                                    $('#selected_products').append(hidden);
                                }
                            } else if (type == 'packaging') {
                                $('#selected_products').find('input[name="qty['+id+'][2]"]').remove();
                                if (qty > 0) {
                                    let hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'qty['+id+'][2]';
                                    hidden.value = qty;
                                    $('#selected_products').append(hidden);
                                }
                            } else {
                                $('#selected_products').find('input[name="qty['+id+']"]').remove();
                                if (qty > 0) {
                                    let hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = 'qty['+id+']';
                                    hidden.value = qty;
                                    $('#selected_products').append(hidden);
                                }
                            }
                            
                            $.get('{Yii::$app->urlManager->createUrl('quick-order/recalc')}', $('#selected_products').find('input[name^="qty"]').serializeArray(), function(d){
                                $('#total_current').html(d.current);
                                $('#total_selected').html(d.selected);
                                $('#total_total').html(d.total);
                            })
                            
                        });
                    }
                });

       // $('.clear').trigger('click');
    });

    tl('{Info::themeFile('/js/main.js')}' , function(){
        {assign var=after_add value=Info::themeSetting('after_add')}
        {if $after_add == 'popup'}
        $('.btn-buy, .form-buy').popUp({
          box_class: 'cart-popup',
          opened: function(obj){
            obj.closest('.item').find('.add-to-cart').hide();
            obj.closest('.item').find('.in-cart').show();
            obj.closest('.item').find('.qty-input').hide()
            $('#selected_products').html('');
            var table = $('#all-prod-list-table').DataTable();
            table.draw(false);
          }
        });
        {elseif $after_add == 'animate'}

        {/if}
    });
</script>
