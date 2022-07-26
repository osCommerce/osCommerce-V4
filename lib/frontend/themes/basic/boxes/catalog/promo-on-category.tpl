{use class="yii\helpers\Html"}
{use class="common\classes\Images"}
{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('promotions')}
<div class="promotions w-promotions">
    <div class="promo-content">
        {foreach $promotions as $promo}
            {if $info[$promo->promo_id] && strlen($info[$promo->promo_id]) > 10}
                <div class="{$promo->promo_class} promo-item">
                    {if $promo->promo_icon}
                        <div class="icon">{Html::img(\common\classes\Images::getWSCatalogImagesPath()|cat:'promo_icons/'|cat:$promo->promo_icon)}</div>
                    {/if}

                    {if $promo->promo_label}<div class="heading-3">{$promo->promo_label}</div>{/if}

                    {if !(is_null($promo->promo_date_start) || $promo->promo_date_start eq '0000-00-00 00:00:00') || !(is_null($promo->promo_date_expired) || $promo->promo_date_expired eq '0000-00-00 00:00:00')}
                        <div class="promo-date">
                            {if !(is_null($promo->promo_date_start) || $promo->promo_date_start eq '0000-00-00 00:00:00') }
                                <div class="date-start">
                                    <span class="title">{$smarty.const.PROMOTION_DATE_START}:</span>
                                    <span class="value">{$promo->promo_date_start|date_format}</span>
                                </div>
                            {/if}
                            {if !(is_null($promo->promo_date_expired) || $promo->promo_date_expired eq '0000-00-00 00:00:00') }
                                <div class="date-end">
                                    <span class="title">{$smarty.const.PROMOTION_DATE_END}:</span>
                                    <span class="value">{$promo->promo_date_expired|date_format}</span>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <div class="description">{$info[$promo->promo_id]}</div>
                </div>
            {/if}
        {/foreach}
    </div>
</div>