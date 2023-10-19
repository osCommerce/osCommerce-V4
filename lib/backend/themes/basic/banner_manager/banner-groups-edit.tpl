
{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}

<form action="" class="banner-group-form">
    <input type="hidden" name="group_id" value="{$group_id}"/>

    <div class="row align-items-center" style="max-width: 500px">
        <label class="col-3">{$smarty.const.GROUP_NAME}:</label>
        <div class="col-6">
            <input type="text" name="banners_group" value="{$groupName}" class="form-control"/>
        </div>
    </div>

<div class="banner-groups-table">

<table class="table table-bordered">
    <thead>
    <tr>
        <th>{$smarty.const.WINDOW_SIZE_FROM}</th>
        <th>{$smarty.const.WINDOW_SIZE_TO}</th>
        <th>{$smarty.const.IMAGE_WIDTH}</th>
        <th>{$smarty.const.IMAGE_HEIGHT}</th>
        <th> </th>
    </tr>
    </thead>
    <tbody>
    {foreach $groupSizes as $size}
    <tr>
        <td>
            <input type="hidden" name="id[]" value="{$size.id}"/>
            <input type="text" name="width_from[]" value="{if $size.width_from}{$size.width_from}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="width_to[]" value="{if $size.width_to}{$size.width_to}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_width[]" value="{if $size.image_width}{$size.image_width}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_height[]" value="{if $size.image_height}{$size.image_height}{/if}" class="form-control"/>
        </td>
        <td>
            <span class="btn-delete"></span>
        </td>
    </tr>
    {/foreach}
    </tbody>
</table>

    <div class="btn-bar">
        <div class="btn-right">
            <span class="btn btn-primary btn-add-size">{$smarty.const.IMAGE_ADD}</span>
        </div>
    </div>

</div>

<div class="btn-bar">
    <div class="btn-left">
        <a href="{Yii::$app->urlManager->createUrl(['banner_manager', 'row_id' => $row_id, 'platform_id' => $platform_id])}" class="btn">{$smarty.const.IMAGE_BACK}</a>
    </div>
    <div class="btn-right">
        <span class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</span>
    </div>
</div>
</form>



<script>

    $(function () {
        const form = $('.banner-group-form');
        const $btnNewGroup = $('.btn-new-group', form);
        const $btnSelectGroup = $('.btn-select-group', form);
        const $fieldNewGroup = $('.field-new-group', form);
        const $fieldSelectGroup = $('.field-select-group', form);

        $btnNewGroup.on('click', function(){
            $btnNewGroup.hide()
            $fieldSelectGroup.hide()
            $fieldSelectGroup.attr('name', 'name_tmp')
            $btnSelectGroup.show()
            $fieldNewGroup.show()
            $fieldNewGroup.attr('name', 'name')
        })
        $btnSelectGroup.on('click', function(){
            $btnNewGroup.show()
            $fieldSelectGroup.show()
            $fieldSelectGroup.attr('name', 'name')
            $btnSelectGroup.hide()
            $fieldNewGroup.hide()
            $fieldNewGroup.attr('name', 'name_tmp')
        })
        $fieldSelectGroup.on('change', function(){
            if ($('input[name="banners_group"]', form).val()) {
                window.location = '{$app->urlManager->createUrl('banner_manager/banner-groups-edit')}?banners_group='
                    + $(this).val()
            }
        })


        $('.banner-group-form .btn-confirm, .top-buttons .save-group').on('click', function(){
            $.post('{$app->urlManager->createUrl('banner_manager/banner-groups-save')}', form.serializeArray(), function(d){
                if (d.error) {
                    alertMessage(d.text, 'alert-message');
                    return;
                }
                const $message = alertMessage(d.text, 'alert-message');

                setTimeout(() => $message.remove(), 1000)
            }, 'json')
        });

        $('.table-bordered tbody', form).on('click', '.btn-delete', function () {
            $(this).parents('tr').remove();
        });

        $('.btn-add-size', form).on('click', function () {
            $('.table-bordered tbody').append(`
    <tr>
        <td>
            <input type="hidden" name="id[]" value="0"/>
            <input type="text" name="width_from[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="width_to[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_width[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_height[]" value="" class="form-control"/>
        </td>
        <td>
            <span class="btn-delete"></span>
        </td>
    </tr>
            `)
        })

    })

</script>