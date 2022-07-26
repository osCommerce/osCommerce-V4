
<table width="100%" class="banners-table">
{foreach $sizeImages as $img}
    <tr>
        <td class="label_name">SVG: <span class="label-image-width">{$img.image_width}</span></td>
        <td class="label_value">
            <div class="" style="width: {$img.image_width}px">{$img['svg']}</div>
            <div class="">
                <span data-href="{$img['svg_url']}" class="btn btn-edit-svg">{$smarty.const.IMAGE_EDIT} SVG</span>
                <span class="btn btn-remove-svg">Remove</span>
            </div>
            <input type="hidden" class="group-svg-remove" name="group_svg_remove[{$language_id}][{$img['image_width']}]"/>
        </td>
    </tr>
{/foreach}
</table>