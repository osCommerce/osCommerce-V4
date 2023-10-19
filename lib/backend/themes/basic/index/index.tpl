{use class="common\helpers\Acl"}
<style type="text/css">
    .rss-feed-heading .subscribe:before {
        content: '\f003';
        font-family: FontAwesome;
    }
    .rss-feed-heading .github:before {
        content: '\f09b';
        font-family: FontAwesome;
    }
    @media (max-width: 1200px) {
        .dashboard-bottom .col-sm-3 {
            width: 50%;
        }
    }
</style>

<div class="row dashboard-top">
    <div class="col-sm-8">
        {if \common\helpers\Acl::rule(['TEXT_DASHBOARD', 'WIDGET_SALESSUMMARY'])}
            {\backend\widgets\SalesSummary::widget()}
        {/if}
        {*if \common\helpers\Acl::rule(['TEXT_DASHBOARD', 'WIDGET_NEWORDERS'])}
        {\backend\widgets\NewOrders::widget()}
        {/if*}
    </div>
    <div class="col-sm-4">
        {if \common\helpers\Acl::rule(['TEXT_DASHBOARD', 'WIDGET_SALESGRAPH'])}
            {\backend\widgets\SalesGraph::widget()}
        {/if}
    </div>
</div>


<div class="row m-t-4 dashboard-bottom">
    <div class="col-sm-3">

        {\backend\widgets\Products::widget()}

    </div>
    {if defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True}
    <div class="col-sm-6">
        {\backend\widgets\NewOrders::widget()}
    </div>
    {/if}
    <div class="col-sm-3">

        <div class="widget box">
            <div class="widget-header rss-feed-heading">
                <h4>
                    <a href="{$smarty.const.RSS_FEED_NEWS_LINK}" target="_blank">{$smarty.const.TEXT_NEWS}</a>
                    <div class="social-links">
                        {if $smarty.const.RSS_FEED_FACEBOOK}
                            <a href="{$smarty.const.RSS_FEED_FACEBOOK}" class="facebook" rel="nofollow" target="_blank" title="Facebook"></a>
                        {/if}
                        {if $smarty.const.RSS_FEED_TWITTER}
                            <a href="{$smarty.const.RSS_FEED_TWITTER}" class="twitter" rel="nofollow" target="_blank" title="Twitter"></a>
                        {/if}
                        {if $smarty.const.RSS_FEED_LINKEDIN}
                            <a href="{$smarty.const.RSS_FEED_LINKEDIN}" class="linkedin" rel="nofollow" target="_blank" title="LinkedIn"></a>
                        {/if}
                        {if $smarty.const.RSS_FEED_SUBSCRIBE}
                            <a href="{$smarty.const.RSS_FEED_SUBSCRIBE}" class="subscribe" rel="nofollow" target="_blank" title="Subscribe"></a>
                        {/if}
                        {if $smarty.const.RSS_FEED_GITHUB}
                            <a href="{$smarty.const.RSS_FEED_GITHUB}" class="github" rel="nofollow" target="_blank" title="Github"></a>
                        {/if}
                    </div>
                </h4>
            </div>
            <div class="widget-content dashboard-scroll">

                {\backend\widgets\News::widget(['url' => $smarty.const.RSS_FEED_NEWS])}

            </div>
        </div>

    </div>
    {if !defined('SUPERADMIN_ENABLED') || SUPERADMIN_ENABLED != True}
    <div class="col-sm-3">

        <div class="widget box">
            <div class="widget-header rss-feed-heading">
                <h4><a href="{$smarty.const.RSS_FEED_NEW_APPLICATIONS_LINK}" target="_blank">{$smarty.const.TEXT_NEW_APPLICATIONS}</a></h4>
            </div>
            <div class="widget-content dashboard-scroll">

                {\backend\widgets\News::widget(['url' => $smarty.const.RSS_FEED_NEW_APPLICATIONS])}

            </div>
        </div>

    </div>
    <div class="col-sm-3">

        <div class="widget box">
            <div class="widget-header rss-feed-heading">
                <h4><a href="{$smarty.const.RSS_FEED_FEATURED_APPLICATIONS_LINK}" target="_blank">{$smarty.const.TEXT_FEATURED_APPLICATIONS}</a></h4>
            </div>
            <div class="widget-content dashboard-scroll">

                {\backend\widgets\News::widget(['url' => $smarty.const.RSS_FEED_FEATURED_APPLICATIONS])}

            </div>
        </div>

    </div>
    {/if}
</div>

{if \common\helpers\Acl::rule(['TEXT_DASHBOARD', 'WIDGET_GOOGLEMAPS'])}
    {\backend\widgets\GoogleMaps::widget()}
{/if}
<script type="text/javascript">
    $(function(){
        const $box1 = $('.summary-box-wrapper');
        const $box2 = $('.dashboard-top .widget-content');
        $(window).on('resize', alignBlock)
        alignBlock();
        setTimeout(alignBlock, 1000)
        function alignBlock() {
            $box1.css('min-height', '');
            $box2.css('min-height', '');
            if ($box1.height() < $box2.height()) {
                $box1.css('min-height', $box2.height())
            } else {
                $box2.css('min-height', $box1.height() - 1)
            }
        }
    })
</script>