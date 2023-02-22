<div class="m-t-4 m-b-2">
    <div class="or_box_head">{$banners_group}</div>

    <div class="pad_bottom">{$smarty.const.ARE_YOU_SURE_DELETE_GROUP}:</div>

{foreach $banners as $banner}
    <div class="pad_bottom"><b>{$banner['banners_title']}</b></div>
    {if $banner['banners_image']}
        {$images = true}
    {/if}
{/foreach}
</div>


    <div class="pad_bottom">
        <input type="checkbox" name="delete_image" class="uniform"/>
        <span>{$smarty.const.TEXT_INFO_DELETE_IMAGE}</span>
    </div>

<div class="btn-toolbar-col-2">
    <span class="btn btn-delete btn-no-margin">{$smarty.const.IMAGE_DELETE}</span>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
</div>

<script>
    $(function () {
        $('.btn-toolbar-col-2 .btn-delete').on('click', function () {
            const delete_image = $('input[name="delete_image"]').prop('checked')
            $.post('banner_manager/banner-groups-delete', {
                group_id: {$group_id},
                delete_image
            }, function (data, status) {
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
            }, 'json')
        })
        $('.btn-toolbar-col-2 .btn-cancel').on('click', function () {
            resetStatement()
        })
        $('.btn-toolbar-col-2 .uniform').uniform()
    })
</script>