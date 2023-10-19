
{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}

<form action="" class="image-settings-form">
    <input type="hidden" name="parent_id" value="{$typeId}"/>
    <input type="hidden" name="image_types_name" value="{$image_types_name}"/>

<div class="image-settings-table">

<table class="table table-bordered">
    <thead>
    <tr>
        <th>{$smarty.const.WINDOW_SIZE_FROM}</th>
        <th>{$smarty.const.WINDOW_SIZE_TO}</th>
        <th>{$smarty.const.IMAGE_WIDTH}</th>
        <th>{$smarty.const.MAX_IMAGE_HEIGHT}</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    {foreach $typeSizes as $size}
    <tr>
        <td>
            <input type="hidden" name="image_types_id[]" value="{$size.image_types_id}"/>
            <input type="text" name="width_from[]" value="{if $size.width_from}{$size.width_from}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="width_to[]" value="{if $size.width_to}{$size.width_to}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_types_x[]" value="{if $size.image_types_x}{$size.image_types_x}{/if}" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_types_y[]" value="{if $size.image_types_y}{$size.image_types_y}{/if}" class="form-control"/>
        </td>
        <td>
            {if $size.parent_id != 0}
                <span class="btn-delete"></span>
            {/if}
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
        <a href="{Yii::$app->urlManager->createUrl('image-settings')}" class="btn">{$smarty.const.IMAGE_BACK}</a>
    </div>
    <div class="btn-right">
        <span class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</span>
    </div>
</div>
</form>



<script type="text/javascript">

    $(function () {
        let form = $('.image-settings-form');

        $('.btn-confirm, .save-group').on('click', function(){
            $.post('{$app->urlManager->createUrl('image-settings/save')}', form.serializeArray(), function(d){
                alertMessage(`<div class="alert-message">${ d}</div>`);
                if (!$('input[name="parent_id"]').val()){
                    window.location = '{$app->urlManager->createUrl('image-settings/edit')}?image_types_id=' + $('input[name="parent_id"]').val()
                }
                setTimeout(function () {
                    $('.popup-box-wrap').remove()
                }, 1000);

                $.get(window.location.href, function (response) {
                    $('.content-container').html(response);
                })
            })
        });

        $('.table-bordered tbody', form).on('click', '.btn-delete', function () {
            $(this).parents('tr').remove();
        });

        $('.btn-add-size', form).on('click', function () {
            $('.table-bordered tbody').append(`
    <tr>
        <td>
            <input type="hidden" name="image_types_id[]" value="-1"/>
            <input type="text" name="width_from[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="width_to[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_types_x[]" value="" class="form-control"/>
        </td>
        <td>
            <input type="text" name="image_types_y[]" value="" class="form-control"/>
        </td>
        <td>
            <span class="btn-delete"></span>
        </td>
    </tr>
            `)
        })

    })

</script>