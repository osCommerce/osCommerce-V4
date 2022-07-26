{use class="yii\helpers\Html"}
    <div class="widget-content widget-content-prod_">
        <table class="table table-selectable table-responsive table-bordered tabl-res datatable table-no-search" rowClass="orderEdit" border="0" width="100%" cellspacing="0" cellpadding="2" callback="productsGridInit" data_ajax="{$app->urlManager->createUrl(['editor/order-edit-products', Yii::$app->request->getQueryParams()])}">
            <thead>
                <tr class="dataTableHeadingRow">
                    {*<th class="dataTableHeadingContent" width="7%" align="center" rowspan="2">{$smarty.const.TABLE_HEADING_QUANTITY}</th>*}
                    <th class="dataTableHeadingContent left" rowspan="2" width="45%">{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                    {*<th class="dataTableHeadingContent" width="10%" rowspan="2">{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>*}
                    {*if $giftWrapExist}
                    <th class="dataTableHeadingContent" width="6%" rowspan="2">{$smarty.const.TEXT_GIFT_WRAP}</th>
                    {/if*}
                    {*<th class="dataTableHeadingContent" width="8%"  align="center" rowspan="2">{$smarty.const.TABLE_HEADING_TAX}</th>*}
                    <th class="dataTableHeadingContent priceTh" width="40%" align="center" rowspan="2" colspan="4">{$smarty.const.TABLE_HEADING_UNIT_PRICE}</th>
{*if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_EDIT_PRODUCT'])}
                    <th class="dataTableHeadingContent no-right-border" width="10%" align="center" rowspan="2">discount/<br>extra charge</th>
                    <th class="dataTableHeadingContent no-left-border" width="6%" align="center" rowspan="2">Price</th>
{/if*}
                    <th class="dataTableHeadingContent" width="15%" align="center" colspan="2"><center>{$smarty.const.TABLE_HEADING_TOTAL_PRICE}</center></th>
                    <th class="dataTableHeadingContent " width="82px" rowspan="2"></th>
                </tr>
                <tr>
                    <th class="dataTableHeadingContent"><center>{$smarty.const.TEXT_EXC_VAT}</center></th>
                    <th class="dataTableHeadingContent"><center>{$smarty.const.TEXT_INC_VAT}</center></th>
                </tr>
            </thead>
            <tbody>
            {*$manager->render('ProductsListingTable', ['manager' => $manager])*}
            </tbody>
        </table>
        {Html::a(TEXT_ADD_A_NEW_PRODUCT, Yii::$app->urlManager->createUrl($queryParams), ['class'=> "btn popup add-product-btn", 'data-class'=>"add-product"] )}

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


