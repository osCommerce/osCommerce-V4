<div class="or_box_head">{$name}</div>

<div class="row">
    <div class="col-12 align-center">{$smarty.const.NUMBER_OF_BANNERS}: <b>{$count}</b></div>
</div>

<div class="btn-toolbar-col">
    <a href="{Yii::$app->urlManager->createUrl(['banner_manager/banner-groups-edit', 'platform_id' => $platform_id, 'group_id' => $group_id, 'row_id' => $row_id])}" class="btn btn-edit btn-primary">{$smarty.const.IMAGE_EDIT}</a>

    <span class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</span>
</div>

<script>
    $(function () {

        const $btnEdit = $('.btn-toolbar-col .btn-edit');
        const row_id = $('input[name="row_id"]').val();
        $btnEdit.attr('href', $btnEdit.attr('href') + `&row_id=${ row_id }`);


        $('.btn-toolbar-col .btn-delete').on('click', function () {
            $.get('banner_manager/banner-groups-delete-confirm', { group_id: '{$group_id}'}, function (data, status) {
                if (status == "success") {
                    $('#banners_management_data .scroll_col').html(data);
                    $("#banners_management").show();
                    heightColumn();
                } else {
                    alert("Request error.");
                }
            });
        })
    })
</script>