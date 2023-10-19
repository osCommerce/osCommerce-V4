{use class="\yii\helpers\Html"}

{function lc}
    <div class="currency_languages">
        <div class="lc">
            <div class="">
                <label>{$smarty.const.TITLE_CURRENCY}<span class="fieldRequired">*</span></label>
                {Html::dropDownList('currency', $entry->defualt_platform_currency, $entry->platform_currencies, ['class' => 'form-select'])}
            </div>                              
        </div>	
        <div class="lc">
            <div class="">
                <label>{$smarty.const.TEXT_LANGUAGES}<span class="fieldRequired">*</span></label>
                {Html::dropDownList('language_id', $entry->defualt_platform_language, $entry->platform_languages, ['class' => 'form-select'])}
            </div>                              
        </div>
    </div>
{/function}
{if !$cl}
    <div class="widget box box-no-shadow">
        <div class="widget-header widget-header-platform"><h4><i class="icon-frontends"></i>Platform details</h4></div>
        <div class="widget-content">              
            <div class="w-line-row w-line-row-1 wbmbp1">	
                <div>
                    <div class="wbmbp1">
                        <label>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}:<span class="fieldRequired">*</span></label>
                        {Html::dropDownList('platform_id', $entry->default_platform, $entry->platforms, ['class' => 'form-select', 'onchange' => 'reloadPdetails(this.value)'])}
                    </div> 
                </div>
                {call lc entry = $entry}
            </div>
            <i style="color:#ff0000;"><sup>*</sup>Required</i>
        </div>
        <div class = "noti-btn">
            <div class="btn-left">
               {if $currentCurrent || $back }
                    <button class="btn btn-default btn-cancel-settings">{$smarty.const.IMAGE_CANCEL}</a>
                {/if}
            </div>
            <div class="btn-right"><a href="javascript:void(0)"  class="btn btn-primary btn-save-settings">Save changes</a></div>
        </div>
        <script>
            function reloadPdetails(pid) {
                $.post('editor/settings', {
                    'platform_id': pid,
                }, function (data, status) {
                    if (status == 'success') {
                        $('.currency_languages').replaceWith(data);
                    }
                }, 'html');
            }
            $(document).ready(function () {
                $('.pop-up-close:last').hide();
                $('.btn-cancel-settings').click(function () {
                    closePopup();
                    {if $back}
                        window.location.href = "{\Yii::$app->urlManager->createUrl($back)}";
                    {/if}
                });
                $('.btn-save-settings').click(function () {
                    $.post($urlCalculateRow, {
                        'action': 'save_settings',
                        'currentCart': $('input[name=currentCart]').val(),
                        'platform_id': $('select[name=platform_id]').val(),
                        'currency': $('select[name=currency]').val(),
                        'language_id': $('select[name=language_id]').val(),
                    }, function (data, status) {
                        if (status == 'success') {
                            if (data.reload) {
                                window.location.reload();
                            }
                        }
                    }, 'json');
                })
            })
        </script>
    </div>
{else}
    {call lc entry = $entry}
{/if}