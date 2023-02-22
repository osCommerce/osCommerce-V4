{use class="common\helpers\Acl"}
<div class="summary-wrapper">

    <div class="summary-title">
    <span class="summaryTitleLine">{$smarty.const.TEXT_SALES_SUMMARY}</span>
        <form action="{$app->urlManager->createUrl('orders/process-order')}" method="get"
              class="go-to-order"><span class="form-line-title">{$smarty.const.TEXT_GO_TO_ORDER} </span><input type="text" class="form-control"
                                                                          name="orders_id"/>
            <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_GO}</button>
        </form>
        <div class="go-to-order form-inline">
            <span class="form-line-title">{$smarty.const.TEXT_GO_TO_PRODUCT}</span>
            {\common\helpers\Html::beginForm( \Yii::$app->urlManager->createUrl('categories'), 'get', ['class' => 'form-inline form-group'])}
            {\common\helpers\Html::hiddenInput('autoEdit', 1)}
            <div class="box-head-search">
                {\common\helpers\Html::textInput('search')}
                {\common\helpers\Html::button('', ['class' => 'edit-product-quick-search', 'onclick' => 'this.form.submit();'])}
            </div>
            {\common\helpers\Html::endForm()}

        </div>
        <span class="summary_arrow"></span>
    </div>

    <div class="summary-box-wrapper">
        <div class="summary-box summary-box-today">
            <div class="sbv">
                <a class="sb-title"
                   href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}">{$smarty.const.TEXT_TODAY}</a>
                <a class="sb-line sb-line-01"
                   href="{$app->urlManager->createUrl('customers?date=presel&interval=1')}"><span>{$stats.today.customers}</span>{$smarty.const.TEXT_CLIENTS}
                </a>
                <div class="sb-line sb-line-02 dash-orders">
                    <div class="dash-orders-title">{$smarty.const.TEXT_ORDERS}</div>
                    <div class="dash-orders-sub-title after">
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}"><span>{$stats.today.orders_new}</span>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS_DASHBOARD}
                            </a></div>
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}"><span>{$stats.today.orders}</span>{$smarty.const.TEXT_ALL}
                            </a></div>
                    </div>
                </div>
                <div class="sb-line sb-line-04">
                    <i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$stats.today.orders_avg_amount}</span>
                </div>
                <div class="sb-line sb-line-05 global-currency {$prefix}">{$smarty.const.TEXT_AMOUNT}
                    <span>{$stats.today.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}" class="btn-show-orders"
                   title="{$smarty.const.TEXT_HANDLE_ORDERS}">{$smarty.const.TEXT_HANDLE_ORDERS}</a>
            </div>
        </div>
        <div class="summary-box summary-box-week">
            <div class="sbv">
                <a class="sb-title"
                   href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}">{$smarty.const.TEXT_WEEK}</a>
                <a class="sb-line sb-line-01"
                   href="{$app->urlManager->createUrl('customers?date=presel&interval=week')}">{$smarty.const.TEXT_CLIENTS}
                    <span>{$stats.week.customers}</span></a>
                <div class="sb-line sb-line-02 dash-orders">
                    <div class="dash-orders-title">{$smarty.const.TEXT_ORDERS}</div>
                    <div class="dash-orders-sub-title after">
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}"><span>{$stats.week.orders_not_processed}</span>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS_DASHBOARD}
                            </a></div>
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}"><span>{$stats.week.orders}</span>{$smarty.const.TEXT_ALL}
                            </a></div>
                    </div>
                </div>
                <div class="sb-line sb-line-04">
                    <i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$stats.week.orders_avg_amount}</span>
                </div>
                <div class="sb-line sb-line-05 global-currency {$prefix}">{$smarty.const.TEXT_AMOUNT}
                    <span>{$stats.week.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}" class="btn-show-orders"
                   title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
            </div>
        </div>
        <div class="summary-box summary-box-month">
            <div class="sbv">
                <a class="sb-title"
                   href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}">{$smarty.const.TEXT_THIS_MONTH}</a>
                <a class="sb-line sb-line-01"
                   href="{$app->urlManager->createUrl('customers?date=presel&interval=month')}">{$smarty.const.TEXT_CLIENTS}
                    <span>{$stats.month.customers}</span></a>
                <div class="sb-line sb-line-02 dash-orders">
                    <div class="dash-orders-title">{$smarty.const.TEXT_ORDERS}</div>
                    <div class="dash-orders-sub-title after">
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}"><span>{$stats.month.orders_not_processed}</span>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS_DASHBOARD}
                            </a></div>
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}"><span>{$stats.month.orders}</span>{$smarty.const.TEXT_ALL}
                            </a></div>
                    </div>
                </div>
                <div class="sb-line sb-line-04">
                    <i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$stats.month.orders_avg_amount}</span>
                </div>
                <div class="sb-line sb-line-05 global-currency {$prefix}">{$smarty.const.TEXT_AMOUNT}
                    <span>{$stats.month.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}" class="btn-show-orders"
                   title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
            </div>
        </div>
        <div class="summary-box summary-box-year">
            <div class="sbv">
                <a class="sb-title"
                   href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}">{$smarty.const.TEXT_THIS_YEAR}</a>
                <a class="sb-line sb-line-01"
                   href="{$app->urlManager->createUrl('customers?date=presel&interval=year')}">{$smarty.const.TEXT_CLIENTS}
                    <span>{$stats.year.customers}</span></a>
                <div class="sb-line sb-line-02 dash-orders">
                    <div class="dash-orders-title">{$smarty.const.TEXT_ORDERS}</div>
                    <div class="dash-orders-sub-title after">
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}"><span>{$stats.year.orders_not_processed}</span>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS_DASHBOARD}
                            </a></div>
                        <div>
                            <a href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}"><span>{$stats.year.orders}</span>{$smarty.const.TEXT_ALL}
                            </a></div>
                    </div>
                </div>
                <div class="sb-line sb-line-04">
                    <i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$stats.year.orders_avg_amount}</span>
                </div>
                <div class="sb-line sb-line-05 global-currency {$prefix}">{$smarty.const.TEXT_AMOUNT}
                    <span>{$stats.year.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}" class="btn-show-orders"
                   title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
            </div>
        </div>
        <div class="summary-box summary-box-period">
            <div class="sbv">
                <a class="sb-title"
                   href="{$app->urlManager->createUrl('orders')}">{$smarty.const.TEXT_ALL_PERIOD}</a>
                <a class="sb-line sb-line-01"
                   href="{$app->urlManager->createUrl('customers')}">{$smarty.const.TEXT_CLIENTS}
                    <span>{$stats.all.customers}</span></a>
                <div class="sb-line sb-line-02 dash-orders">
                    <div class="dash-orders-title">{$smarty.const.TEXT_ORDERS}</div>
                    <div class="dash-orders-sub-title after">
                        <div><a href="{$app->urlManager->createUrl('orders')}"><span
                                        class="stats_all_orders_not_processed">{$stats.all.orders_not_processed}</span>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS_DASHBOARD}
                            </a></div>
                        <div><a href="{$app->urlManager->createUrl('orders')}"><span
                                        class="stats_all_orders">{$stats.all.orders}</span>{$smarty.const.TEXT_ALL}
                            </a></div>
                    </div>
                </div>
                <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span
                            class="stats_all_orders_avg_amount">{$stats.all.orders_avg_amount}</span></div>
                <div class="sb-line sb-line-05 global-currency {$prefix}">{$smarty.const.TEXT_AMOUNT}<span
                            class="stats_all_orders_amount">{$stats.all.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders')}" class="btn-show-orders"
                   title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        {if $lazyLoadOrderAll}
        $.getJSON("{$app->urlManager->createUrl('index/dashboard-order-stat')}", function (data) {
            if (data || data.all) {
                for (var key in data.all) {
                    if (!data.all.hasOwnProperty(key)) continue;
                    $('.stats_all_' + key).html(data.all[key]);
                }
            }
        });
        {/if}
        $(window).resize(function () {
            $('.summary-box-wrapper').inrow({ item1: '.sb-line i'});
        });
        $(window).resize();
    });
</script>