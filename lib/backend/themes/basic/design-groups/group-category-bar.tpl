<div class="or_box_head">{$title}</div>

{if $categoryId}
<div class="row">
    <div class="col-12 align-center"></div>
</div>

<div class="btn-toolbar-col main-buttons">

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
            $.post('design-groups/delete-category', { categoryId: '{$categoryId}' }, function (response) {
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
{/if}