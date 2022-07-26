{use class="common\helpers\Acl"}
<div class="widget box">
    <div class="widget-header">
        <h4>{$smarty.const.TEXT_PRODUCTS}</h4>
    </div>
    <div class="widget-content dashboard-scroll">


        <div class="summary-box summary-box-info">
            <div class="sb-line dash-orders dash-list">
                <div class="dash-orders-title">{$smarty.const.TEXT_DASHBOARD_LISTING}</div>
                <div class="dash-orders-sub-title after">
                    <div><a href="{$app->urlManager->createUrl(['categories', 'type_listing'=>'1','stock'=>'y'])}"><span>{$stats.pData.listing.active}</span>{$smarty.const.TEXT_DASHBOARD_ACTIVE}</a></div>
                    <div><a href="{$app->urlManager->createUrl(['categories', 'type_listing'=>'1'])}"><span>{$stats.pData.listing.active + $stats.pData.listing.inactive}</span>{$smarty.const.TEXT_ALL}</a></div>
                </div>
            </div>
            <div class="sb-line dash-orders">
                <div class="dash-orders-title">{$smarty.const.TEXT_DASHBOARD_MASTER}</div>
                <div class="dash-orders-sub-title after">
                    <div><a href="{$app->urlManager->createUrl(['categories', 'type_not_listing'=>'1','stock'=>'y'])}"><span>{$stats.pData.master.active}</span>{$smarty.const.TEXT_DASHBOARD_ACTIVE}</a></div>
                    <div><a href="{$app->urlManager->createUrl(['categories', 'type_not_listing'=>'1'])}"><span>{$stats.pData.master.active + $stats.pData.master.inactive}</span>{$smarty.const.TEXT_ALL}</a></div>
                </div>
            </div>
            <div class="sb-line dash-orders dash-child">
                <div class="dash-orders-title">{$smarty.const.TEXT_DASHBOARD_CHILD}</div>
                <div class="dash-orders-sub-title after">
                    <div><a href="{$app->urlManager->createUrl(['categories', 'sub_children'=>'1','stock'=>'y'])}"><span>{$stats.pData.child.active}</span>{$smarty.const.TEXT_DASHBOARD_ACTIVE}</a></div>
                    <div><a href="{$app->urlManager->createUrl(['categories', 'sub_children'=>'1'])}"><span>{$stats.pData.child.active + $stats.pData.child.inactive}</span>{$smarty.const.TEXT_ALL}</a></div>
                </div>
            </div>
            <div class="sb-line dash-orders">
                <div class="dash-orders-title">{$smarty.const.TEXT_DASHBOARD_BUNDLES}</div>
                <div class="dash-orders-sub-title after">
                    <div><a href="{$app->urlManager->createUrl(['categories', 'all_bundles'=>'1','stock'=>'y'])}"><span>{$stats.pData.bundle.active}</span>{$smarty.const.TEXT_DASHBOARD_ACTIVE}</a></div>
                    <div><a href="{$app->urlManager->createUrl(['categories', 'all_bundles'=>'1'])}"><span>{$stats.pData.bundle.active + $stats.pData.bundle.inactive}</span>{$smarty.const.TEXT_ALL}</a></div>
                </div>
            </div>
            {if $TrustpilotClass = Acl::checkExtensionAllowed('Trustpilot', 'allowed')}
                {if $TrustpilotClass::enabledDashboard()}
                    {$TrustpilotClass::viewDashboard()}
                {else}
                    <div class="sb-line dash-orders dash-review">
                        <div class="dash-orders-title">Reviews</div>
                        <div class="dash-orders-sub-title after">
                            <div><a href="{$app->urlManager->createUrl('reviews')}"><span>{$stats.reviews_to_confirm}</span>{$smarty.const.TEXT_DASH_TO_APPROVE}</a></div>
                            <div><a href="{$app->urlManager->createUrl('reviews')}"><span>{$stats.reviews_confirmed}</span>{$smarty.const.TEXT_ALL}</a></div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>


    </div>
</div>