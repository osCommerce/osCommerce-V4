
<table width="100%" class="banners-table">
{foreach $sizeImages as $img}
    <tr>
        <td class="label_name">
            {$smarty.const.IMAGE_WIDTH}: <span class="label-image-width">{$img.image_width}</span><br>
            {$smarty.const.RECOMMENDED_IMAGE_HEIGHT}: <span class="label-image-height">{$img.image_height}</span>
        </td>
        <td class="label_value">
            <div class="upload-box">
            {\backend\design\Image::widget([
            'name' => 'group_image['|cat:$language_id|cat:']['|cat:$img.image_width|cat:']',
            'value' => $img.image,
            'upload' => 'group_image_upload['|cat:$language_id|cat:']['|cat:$img.image_width|cat:']',
            'delete' => 'group_image_delete['|cat:$language_id|cat:']['|cat:$img.image_width|cat:']',
                'path' => 'images/banners'
            ])}
            </div>
        </td>
    </tr>
{/foreach}
</table>