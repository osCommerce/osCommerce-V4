{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-theme"><h4>{$smarty.const.CATEGORY_RESTRICTION}</h4></div>
    <div class="widget-content">
        <div class="w-line-row w-line-row-1">
            <div class="wl-td zones_td">
                <label>{$smarty.const.BOX_GEO_ZONES}:</label>
                <div class="geo_zones"></div>
                <a class="btn popup_zones" href="#geo_zones">{$smarty.const.BUTTON_ADD_MORE_NEW}</a>
                <div class="geo_zone_popup popup-box-wrap-page popup-box-wrap-page-1 hide_popup" id="geo_zones">
                    <div class="around-pop-up-page"></div>
                    <div class="popup-box-page">
                        <div class="pop-up-close-page"></div>
                        <div class="pop-up-content-page">
                            <div class="popup-heading">{$smarty.const.TEXT_SET_UP} {$smarty.const.BOX_GEO_ZONES}</div>
                            <div class="popup-content geo_zones_block">
                                <div class="multiselected_items">{$smarty.const.TEXT_SELECTED_ITEMS}</div>
                                {Html::dropDownList('zones[]', $selected_zones, $zones, ['class' => 'multiselect form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])|strip}
                                <div class="btn-bar">
                                    <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                                    <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td zones_td">
                <label>{$smarty.const.BOX_TAXES_COUNTRIES}:</label>
                <div class="geo_zones"></div>
                <a class="btn popup_zones" href="#countries">{$smarty.const.BUTTON_ADD_MORE_NEW}</a>
                <div class="geo_zone_popup popup-box-wrap-page popup-box-wrap-page-1 hide_popup" id="countries">
                    <div class="around-pop-up-page"></div>
                    <div class="popup-box-page">
                        <div class="pop-up-close-page"></div>
                        <div class="pop-up-content-page">
                            <div class="popup-heading">{$smarty.const.TEXT_SET_UP} {$smarty.const.BOX_TAXES_COUNTRIES}</div>
                            <div class="popup-content countries_block">
                                {Html::dropDownList('countries[]', $selected_countries, $countries, ['class' => 'multiselect form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])|strip}
                                <div class="btn-bar">
                                    <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                                    <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>