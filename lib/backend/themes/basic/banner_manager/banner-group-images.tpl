<div class="row">
{foreach $sizeImages as $img}
    <div class="col-md-6 m-b-4">

        <span class="image-size">
            <span class="label-image-width">{$smarty.const.IMAGE_VIDEO_WIDTH}:</span>
            <span class="image-width">{$img.image_width}</span>.
            {if $img.image_height}
                <span class="label-image-height">{$smarty.const.RECOMMENDED_IMAGE_HEIGHT}:</span>
                <span class="image-height">{$img.image_height}</span>.
            {/if}
        </span>
        <span class="window-size">
            <span class="parenthesis">(</span>
            Window width
            {if $img.width_from} from <span class="width-from">{$img.width_from}</span> {/if}
            {if $img.width_to} to <span class="width-to">{$img.width_to}</span> {/if}
            <span class="parenthesis">)</span>
        </span>

        <div class="upload-box upload-box-wrap"
             data-name="group_image[{$language_id}][{$img.image_width}]"
             data-value="{$img.image}"
             data-upload="group_image_upload[{$language_id}][{$img.image_width}]"
             data-delete="group_image_delete[{$language_id}][{$img.image_width}]"
             data-type="{$img.type}"
             data-accepted-files="image/*,video/*"
             data-width="{$img.image_width}"
             data-height="{$img.image_height}"
             data-position-name="position[{$language_id}][{$img.image_width}]"
             data-position-value="{$img.position}"
             data-fit-name="fit[{$language_id}][{$img.image_width}]"
             data-fit-value="{$img.fit}"
             data-edit="1"
        >
        </div>
    </div>
{/foreach}
</div>