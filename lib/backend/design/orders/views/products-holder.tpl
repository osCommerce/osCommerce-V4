
            <table border="0" class="table table-process" width="100%" cellspacing="0" cellpadding="2">
                <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent" colspan="3"><i class="btn-order-products-sort icon-sort js-sort-order-products hide" aria-hidden="true" data-selector=".table-process tbody" data-server-action="{Yii::$app->urlManager->createUrl(['orders/sort-products', 'order_id' => $order->order_id])}"></i>{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                        <th class="dataTableHeadingContent">{$smarty.const.TABLE_HEADING_STATUS}</th>
                        <th class="dataTableHeadingContent">{$smarty.const.TEXT_STOCK}</th>
                        <th class="dataTableHeadingContent location">{$smarty.const.BOX_HEADING_LOCATION}</th>                        
                        <th class="dataTableHeadingContent tax">{$smarty.const.TABLE_HEADING_TAX}</th>
                        <th class="dataTableHeadingContent price" align="center"><span>{$smarty.const.TEXT_PRICE}</span><span>{$smarty.const.TEXT_TOTAL}</span></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $order->getOrderedProducts('admin_order_detail') as $i => $product}
                        {$manager->render('Product', [
                                'manager' => $manager,
                                'product'=> $product,
                                'iter' => $i,
                                'order' => $order,
                                'opsArray' => $opsArray,
                                'handlers_array' => $handlers_array,
                                'warehouses_allocated_array' => $warehouses_allocated_array,
                                'suppliers_allocated_array' => $suppliers_allocated_array,
                                'warehouseList' => $warehouseList,
                                'locationBlockList' => $locationBlockList
                            ])}
                    {/foreach}
                </tbody>
            </table>
            <script type="text/javascript">
                $(document).ready(function () {
                    $("a.js_gv_state_popup").popUp({
                        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading pup-head'><?php echo POPUP_TITLE_GV_STATE_SWITCH; ?></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                    });
                    $('.popup').popUp();
                    $('.right-link').popUp({ 'box_class':'popupCredithistory' });
                });
            </script>