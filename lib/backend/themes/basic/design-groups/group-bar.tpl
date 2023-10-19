<div class="or_box_head">{$group.name}</div>

<div class="row">
    <div class="col-12 align-center"></div>
</div>

<div class="btn-toolbar-col main-buttons">
    <a href="{Yii::$app->urlManager->createUrl(['design-groups/view', 'group_id' => $groupId, 'row_id' => $rowId, 'category' => $category])}" class="btn btn-view btn-primary">{$smarty.const.IMAGE_VIEW}</a>

    <a href="{Yii::$app->urlManager->createUrl(['design-groups/edit', 'group_id' => $groupId, 'row_id' => $rowId, 'category' => $category])}" class="btn btn-edit">{$smarty.const.IMAGE_EDIT}</a>

    <span class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</span>
</div>

<div class="btn-toolbar-col-2 delete-warning" style="display: none">
    <div class="m-b-2">{$smarty.const.ARE_YOU_SURE_DELETE_GROUP}</div>

    <span class="btn btn-no">{$smarty.const.IMAGE_CANCEL}</span>
    <span class="btn btn-delete-yes">{$smarty.const.IMAGE_DELETE}</span>
</div>

<script>
    $(function () {
        $('#col_management .btn-delete').on('click', function () {
            $('#col_management .main-buttons').hide();
            $('#col_management .delete-warning').show();
        });
        $('#col_management .btn-no').on('click', function () {
            $('#col_management .main-buttons').show();
            $('#col_management .delete-warning').hide();
        });
        $('#col_management .btn-delete-yes').on('click', function () {
            $.post('design-groups/delete-group', { groupId: '{$groupId}' }, function (response) {
                if (response.error) {
                    alertMessage(response.error, 'alert-message')
                }
                if (response.text) {
                    $('#col_management .main-buttons').show();
                    $('#col_management .delete-warning').hide();
                    const $message = alertMessage(response.text, 'alert-message');
                    setTimeout(() => $message.remove(), 1000)
                    resetStatement();
                }
            }, 'json')
        })
    })
</script>