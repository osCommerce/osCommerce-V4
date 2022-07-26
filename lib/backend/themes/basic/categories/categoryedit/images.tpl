{use class="\common\classes\Images"}


<div class="widget box box-no-shadow">
    <div class="widget-header">
        <h4>Additional images</h4>
    </div>
    <div class="widget-content">
        <div class="wrap-prod-gallery" data-platform_id="{$data.platform_id}">
            <div class="drag-prod-img">
                <div class="upload-container upload-container-with-button">
                    <div class="upload-file-wrap">
                        <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>
                        <div class="upload-file"></div>
                        <div class="upload-hidden"><input type="hidden" name="image_buffer"/></div>
                    </div>
                </div>
            </div>
            <div class="jcarousel-wrapper">
                <div class="jcarousel" id="jcarousel-images-listing">
                    <ul class="images-listing">
                        {if isset($images[$data.platform_id])}
                        {foreach $images[$data.platform_id] as $image}
                            <li>
                                <input type="hidden"
                                       name="additional_categories[image][{$data.platform_id}][]"
                                       value="{$image['image']}">
                                <input type="hidden"
                                       name="additional_categories[image_id][{$data.platform_id}][]"
                                       value="{$image['categories_images_id']}">
                                <span class="handle"><i class="icon-hand-paper-o"></i></span>
                                <span>
                                    <img src="{$image['image_url']}" />
                                </span>
                                <div class="upload-remove"></div>
                            </li>
                        {/foreach}
                        {/if}
                    </ul>
                </div>
                <a href="#" class="jcarousel-control-prev"></a>
                <a href="#" class="jcarousel-control-next"></a>
            </div>
            <input type="hidden" value="" name="images_sort_order" class="images_sort_order"/>
        </div>
    </div>
</div>

