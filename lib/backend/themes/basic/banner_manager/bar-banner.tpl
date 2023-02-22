<div class="or_box_head">{$bInfo['banners_title']}</div>

<div class="row_or_wrapp">
<div class="row_or"><div>{$smarty.const.TEXT_BANNERS_DATE_ADDED}</div>
    <div>{\common\helpers\Date::date_format($bInfo['date_added'], DATE_FORMAT_SHORT)}</div></div>
</div>

<div class="graph b_imgcenter">{$image}</div>

<div class="pad_bottom b_right">
    <strong>{$smarty.const.TEXT_GROUP}</strong>
    <span>{$bInfo['banners_group']}</span>
</div>

<div class="b_right">
    <strong>{$smarty.const.BOX_PLATFORMS}:</strong>
    <span>{$b_platform}</span>
</div>

{if $bInfo['date_scheduled']}
    <div class="pad_bottom">
        {sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, \common\helpers\Date::datetime_short($bInfo['date_scheduled'], DATE_FORMAT_SHORT))}
    </div>
{/if}

{if $bInfo['expires_date']}
    <div class="pad_bottom">
        {sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, \common\helpers\Date::datetime_short($bInfo['expires_date'], DATE_FORMAT_SHORT))}
    </div>
{elseif $bInfo['expires_impressions']}
    <div class="pad_bottom">
        {sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo['expires_impressions'])}
    </div>
{/if}

{if $bInfo['date_status_change']}
    <div class="pad_bottom">
        {sprintf(TEXT_BANNERS_STATUS_CHANGE, \common\helpers\Date::date_format($bInfo['date_status_change'], DATE_FORMAT_SHORT))}
    </div>
{/if}

<div class="btn-toolbar-col">
    <a class="btn btn-edit btn-primary" href="{Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $bInfo['banners_id']])}">{$smarty.const.IMAGE_EDIT}</a>

    <span class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</span>

    <span class="btn btn-no-margin btn-copy">{$smarty.const.IMAGE_COPY}</span>
</div>

<script>
    $(function () {
        $('.btn-toolbar-col .btn-delete').on('click', function(){

            $.get('banner_manager/delete-confirm', { bID: ['{$bInfo['banners_id']}'] }, function(data, status){
                if (status == "success") {
                    $('#banners_management_data .scroll_col').html(data);
                    $("#banners_management").show();
                    heightColumn();
                } else {
                    alert("Request error.");
                }
            });
        });

        $('.btn-toolbar-col .btn-copy').on('click', function(){
            const platform_id = $('#filterForm input[name="platform_id"]').val();
            $.post('banner_manager/banner-duplicate', { banners_id:'{$bInfo['banners_id']}', platform_id }, function(data, status){
                if (status != 'success') {
                    alertMessage('Request error.', 'alert-message');
                    return null;
                }
                if (data.error) {
                    alertMessage(data.error, 'alert-message');
                    return null;
                }
                if (data.success) {
                    alertMessage(data.success, 'alert-message');
                    resetStatement()
                    return null;
                }
            }, 'json');
        })

        const $btnEdit = $('.btn-toolbar-col .btn-edit');
        const group_id = $('input[name="group_id"]').val();
        const platform_id = $('input[name="platform_id"]').val();
        const row_id = $('input[name="row_id"]').val();
        $btnEdit.attr('href', $btnEdit.attr('href') + `&group_id=${ group_id }&platform_id=${ platform_id }&row_id=${ row_id }`)
    })
</script>