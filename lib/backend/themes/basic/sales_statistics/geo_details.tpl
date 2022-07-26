{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{if !$ajax}
<fieldset>
    <div><label>Geo</label></div>
        {Html::dropDownList('geo_type', $selected_geo_type, $geo_type, ['class' => 'form-control wl-td', 'onchange'=> 'return getGeoBox(this.value)'])}
        <div class="order-geo-box">
{/if}
        {if !$selected_geo_type }
            <label>{$smarty.const.TABLE_HEADING_ZONE_NAME}</label>
            {Html::dropDownList('zones[]', $selected_zones, $zones, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
        {else}
            <div class="f_div_country" style="position: relative">
                <label>{$smarty.const.TEXT_INFO_COUNTRY_NAME}</label>
                {Html::dropDownList('country[]', $country, \common\helpers\Country::new_get_countries(), ['class' => 'form-control wl-td', 'id' => 'selectCountry', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
            </div>
            
            <div class="f_div_state">
                <label>{$smarty.const.ENTRY_STATE}</label>
                {Html::input('text', 'state', $state, ['class' => 'form-control wl-td', 'id' => 'selectState'])}
            </div>
            
            <div class="f_div_state">
                <label>{$smarty.const.ENTRY_SUBURB}/{$smarty.const.TEXT_ZIP_CODE}/{$smarty.const.ENTRY_STREET_ADDRESS}</label>
                {Html::input('text', 'sps', $sps, ['class' => 'form-control'])}
            </div>
        {/if}
        </div>
{if !$ajax}
        <script>
            getGeoBox = function (geoType){
                $.get("{Url::to(['sales_statistics/get-geo'])}", { 'geo_type': geoType }, function (data){
                    if (data.hasOwnProperty('selectors')){
                        $('.order-geo-box').html(data.selectors);
                        $("form .order-geo-box select[data-role=multiselect]").multipleSelect({
                                multiple: true,
                                filter: true
                        });
                        loadCn();
                        loadSt();
                    }
                    
                }, 'json');
            }
            
            autoSelect = function(owner, ajaxData, to){
                owner.autocomplete({
                    source: function(request, response) {
                        ajaxData['term'] = request.term;
                        $.ajax({
                            url: "sales_statistics/get-geo",
                            dataType: "json",
                            data: ajaxData,
                            success: function(data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 0,            
                    delay: 0,
                    appendTo: to,            
                    select: function(event, ui) {
                    }
                }).focus(function () {
                  $(this).autocomplete("search");
                });  
            }
            
            loadCn = function(){
                autoSelect($('#selectCountry'), {
                                action:'country',
                            }, '.f_div_country');
            }
            loadCn();
            
            loadSt = function(){
                autoSelect($('#selectState'), {
                                country : $("#selectCountry").val(),
                                action:'state',
                            }, '.f_div_state');
            }
            loadSt();
            
        </script>
</fieldset>
{/if}