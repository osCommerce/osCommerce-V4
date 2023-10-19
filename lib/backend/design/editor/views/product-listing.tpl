{use class="yii\helpers\Html"}

<div class="widget box box-no-shadow">
    <div class="widget-header">
        <h4>{$smarty.const.TABLE_HEADING_PRODUCTS}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content widget-content-prod_">
        <table class="table table-selectable table-responsive table-bordered tabl-res datatable table-no-search" rowClass="orderEdit" border="0" width="100%" cellspacing="0" cellpadding="2" callback="productsGridInit" data_ajax="{$app->urlManager->createUrl(['editor/order-edit-products', Yii::$app->request->getQueryParams()])}">
            <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent left" rowspan="2">{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                <th class="dataTableHeadingContent priceTh" align="center" rowspan="2" colspan="1">{$smarty.const.TABLE_HEADING_UNIT_PRICE}</th>
                <th class="dataTableHeadingContent priceTh" align="center" rowspan="2" colspan="1">{$smarty.const.TABLE_HEADING_QUANTITY}</th>
                <th class="dataTableHeadingContent" align="center" colspan="2"><center>{$smarty.const.TABLE_HEADING_TOTAL_PRICE}</center></th>
                <th class="dataTableHeadingContent" rowspan="2" style="width: 60px"></th>
            </tr>
            <tr>
                <th class="dataTableHeadingContent text-center" style="width: 100px">{$smarty.const.TEXT_EXC_VAT}</th>
                <th class="dataTableHeadingContent text-center" style="width: 100px">{$smarty.const.TEXT_INC_VAT}</th>
            </tr>
            </thead>
            <tbody>
            {*$manager->render('ProductsListingTable', ['manager' => $manager])*}
            </tbody>
        </table>


        <div class="btn-bar pt-0 pb-0">
            <div class="btn-left">
                <span id="reset-cart" class="btn btn-cancel">{$smarty.const.RESET_CART}</span>
                <span id="save-changes"  class="btn btn-primary btn-save-cart">{$smarty.const.SAVE_CART}</span>
            </div>
            <div class="btn-right">
                {Html::a(TEXT_ADD_A_NEW_PRODUCT, Yii::$app->urlManager->createUrl($queryParams), ['class'=> "btn popup add-product-btn", 'data-class'=>"add-product"] )}
            </div>
        </div>


        {if $giveaway_count && in_array($manager->getInstanceType(), ['order'])}
            {Html::a(TEXT_ADD_GIVEAWAY, Yii::$app->urlManager->createUrl(array_merge($queryParams, ['action'=>'show_giveaways'])), ['class'=> "btn popup add-product-btn", 'data-class'=>"add-product"] )}
        {/if}




        <script>
            function onClickEvent(obj, table) {
                return false;
            }
            function onUnclickEvent(obj, table) {
                return false;
            }
            function productsGridInit() {
                $('.product_info').find('input.result-price').setMaskMoney();
                setPlugin();
                $('input.result-price').on('change',function(e){
                    order.updateProductInRow(e.target, 'change_price');
                });
                //$('.qty', '.product_info').trigger('check_quantity');
                $('.product_info .qty').each(function(){
                    $(this).trigger('check_quantity', [$(this).val()])
                })
                $('.product_info .qty').on('change', function(){
                    $(this).trigger('check_quantity', [$(this).val()])
                })
            }
            $(document).ready(function() {
                $('.dataTables_header.clearfix').hide().parent('.row').hide();

            });
        </script>
    </div>
</div>




