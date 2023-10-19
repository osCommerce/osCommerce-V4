{use class="yii\helpers\Html"}
{use class="\common\classes\Images"}
{if defined('ADMIN_TOO_MANY_IMAGES') && is_array($app->controller->view->images|default:null) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
  {$uniform="uniform-dis"}
{else}
  {$uniform="uniform"}
{/if}
<div id="image-box-{$Key}" class="image-box inactive">
<div class="box-gallery-left">
    <div class="edp-our-price-box">
        <div class="widget widget-full box box-no-shadow" style="margin-bottom: 0">
            <div class="widget-content after">
                <div class="status-left st-origin">
                    <span>{$smarty.const.TEXT_STATUS}</span>
                    <input type="checkbox" value="1" name="image_status[{$Key}]" class="check_bot_switch_on_off" checked="checked" />
                </div>
                <div class="status-left">
                    <span>{$smarty.const.TEXT_DEFAULT_IMG}:</span>
                    <input type="radio" value="{$Key}" name="default_image" class="default-images check_bot_switch_on_off" />
                </div>
            </div>
            {if $ext = \common\helpers\Acl::checkExtensionAllowed('ProductImagesByPlatform', 'allowed')}
                {$ext::imageBlock($Key, $Item)}
            {/if}
        </div>
        <div class="widget box widget-not-full box-no-shadow" style="margin-bottom: 0; border-top: 0;">
            <div class="widget-content">
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs nav-tabs-vertical nav-tabs-vertical-lang">
                        <li class="active" data-bs-toggle="tab" data-bs-target="#tab_4_{$Key}_0"><a><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                        {foreach $description as $DKey => $DItem}
                        <li data-bs-toggle="tab" data-bs-target="#tab_4_{$Key}_{$DItem['key']}"><a class="flag-span">{$DItem['logo']}<span>{$DItem['name']}</span></a></li>
                        {/foreach}
                    </ul>
                    <div class="tab-content tab-content-vertical">
                        <div class="tab-pane active one-img-gal{if $Item['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_0">
                            <div class="one-img-gal-left">
                                <div class="drag-prod-img-2">
                                    <div class="upload new_upload_{$Key}" data-linked="preview-box-{$Key}" data-name="orig_file_name[{$Key}][0]" data-show="ofn_{$Key}_0" data-preload="{$Item['orig_file_name']|escape:'html'}" data-value="{$Item['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                                </div>
                            </div>
                            <div class="one-img-gal-right">
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_ORING_NAME}</label>
                                    <span id="ofn_{$Key}_0">{$Item['orig_file_name']}&nbsp;</span>
                                    <label><input type="checkbox" name="use_origin_image_name[{$Key}][0]" value="1" {if $Item['use_origin_image_name'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_USE_ORIGIN_IMAGE_NAME}</label>
                                </div>
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                                    <input type="text" name="image_title[{$Key}][0]" value="{$Item['image_title']|escape:'html'}" class="form-control" />
                                </div>        
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                                    <input type="text" name="image_alt[{$Key}][0]" value="{$Item['image_alt']|escape:'html'}" class="form-control" />
                                </div>
                                <div class="our-pr-line js-image-toggle">
                                    <label><input type="checkbox" name="link_video_flag[{$Key}][0]" value="1" {if $Item.link_video_id > 0} checked{/if} class="uniform js-image-toggle-source" /> {$smarty.const.TEXT_LINK_TO_VIDEO}</label>
                                    {if $Item.link_video_id == 0}
                                        {$styleForLink = 'display: none;'}
                                    {else}
                                        {$styleForLink = ''}
                                    {/if}
                                    {Html::dropDownList('link_video_id['|cat:$Key|cat:'][0]', $Item['link_video_id'], $video, ['class' => 'form-control js-image-toggle-target', 'style' => $styleForLink])}
                                </div>
                                <div class="our-pr-line">
                                    <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_0')" name="use_external_images[{$Key}][0]" value="1" {if $Item['use_external_images']} checked{/if} class="{$uniform}" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
                                </div>
                                <div class="our-pr-line external_images-hide js-image-toggle">
                                    <label><input type="checkbox" name="alt_file_name_flag[{$Key}][0]" value="1" {if $Item['alt_file_name'] != ""} checked{/if} class="{$uniform} js-image-toggle-source" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                                    <input type="text" name="alt_file_name[{$Key}][0]" value="{$Item['alt_file_name']|escape:'html'}" class="form-control js-image-toggle-target" {if $Item['alt_file_name'] == ""} style="display: none;"{/if} />
                                </div>
                                <div class="our-pr-line external_images-hide">
                                    <label><input type="checkbox" name="no_watermark[{$Key}][0]" value="1" {if $Item['no_watermark'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                                </div>
                                <div class="external_images-show">
                                    <div class="our-pr-line">
                                        <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,'Original')}</label>
                                        <input type="text" name="external_image_original[{$Key}][0]" value="{$Item['external_image_original']|escape:'html'}" class="form-control" />
                                    </div>
                                    {foreach from=$Item['external_images'] item=external_image name=imageTypeList}
                                        <div class="our-pr-line">
                                            <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,$external_image['image_types_name'])} ({$external_image['image_size']})</label>
                                            <input type="text" name="external_image[{$Key}][0][{$external_image['image_types_id']}]" value="{$external_image['image_url']|escape:'html'}" class="form-control{if $smarty.foreach.imageTypeList.last} biggestImage{/if}" />
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        {foreach $description as $DKey => $DItem}
                        <div class="tab-pane one-img-gal{if $DItem['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_{$DItem['key']}">
                            <div class="one-img-gal-left">
                                <div class="drag-prod-img-2">
                                    <div class="upload new_upload_{$Key}" data-name="orig_file_name[{$Key}][{$DItem['id']}]" data-show="ofn_{$Key}_{$DItem['id']}" data-value="{$DItem['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                                </div>
                            </div>
                            <div class="one-img-gal-right">
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_ORING_NAME}</label>
                                    <span id="ofn_{$Key}_{$DItem['id']}">{$DItem['orig_file_name']}&nbsp;</span>
                                    <label><input type="checkbox" name="use_origin_image_name[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_origin_image_name'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_USE_ORIGIN_IMAGE_NAME}</label>
                                </div>
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                                    <input type="text" name="image_title[{$Key}][{$DItem['id']}]" value="{$DItem['image_title']|escape:'html'}" class="form-control" />
                                </div>        
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                                    <input type="text" name="image_alt[{$Key}][{$DItem['id']}]" value="{$DItem['image_alt']|escape:'html'}" class="form-control" />
                                </div>
                                <div class="our-pr-line js-image-toggle">
                                    <label><input type="checkbox" name="link_video_flag[{$Key}][{$DItem['id']}]" value="1" {if $DItem.link_video_id > 0} checked{/if} class="uniform js-image-toggle-source" /> {$smarty.const.TEXT_LINK_TO_VIDEO}</label>
                                    <input type="text" name="link_video_id[{$Key}][{$DItem['id']}]" value="{$DItem.link_video_id}" class="form-control js-image-toggle-target" {if $DItem.link_video_id == 0} style="display: none;"{/if} />
                                    {if $Item.link_video_id == 0}
                                        {$styleForLink = 'display: none;'}
                                    {else}
                                        {$styleForLink = ''}
                                    {/if}
                                    {Html::dropDownList('link_video_id['|cat:$Key|cat:']['|cat:$DItem['id']|cat:']', $DItem['link_video_id'], $video, ['class' => 'form-control js-image-toggle-target', 'style' => $styleForLink])}
                                </div>
                                <div class="our-pr-line">
                                    <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_{$DItem['id']}')" name="use_external_images[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_external_images'] == 1} checked{/if} class="uniform" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
                                </div>
                                <div class="our-pr-line external_images-hide js-image-toggle">
                                    <label><input type="checkbox" name="alt_file_name_flag[{$Key}][{$DItem['id']}]" value="1" {if $DItem['alt_file_name'] != ""} checked{/if} class="uniform js-image-toggle-source" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                                    <input type="text" name="alt_file_name[{$Key}][{$DItem['id']}]" value="{$DItem['alt_file_name']|escape:'html'}" class="form-control js-image-toggle-target" {if $DItem['alt_file_name'] == ""} style="display: none;"{/if} />
                                </div>
                                <div class="our-pr-line external_images-hide">
                                    <label><input type="checkbox" name="no_watermark[{$Key}][{$DItem['id']}]" value="1" {if $DItem['no_watermark'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                                </div>
                                <div class="external_images-show">
                                    <div class="our-pr-line">
                                        <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,'Original')}</label>
                                        <input type="text" name="external_image_original[{$Key}][{$DItem['id']}]" value="{$DItem['external_image_original']|escape:'html'}" class="form-control" />
                                    </div>
                                    {foreach from=$Item['external_images'] item=external_image name=imageTypeList}
                                        <div class="our-pr-line">
                                            <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,$external_image['image_types_name'])} ({$external_image['image_size']})</label>
                                            <input type="text" name="external_image[{$Key}][{$DItem['id']}][{$external_image['image_types_id']}]" value="{$external_image['image_url']|escape:'html'}" class="form-control{if $smarty.foreach.imageTypeList.last} biggestImage{/if}" />
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{$AttributesImages = \common\helpers\Extensions::isAllowed('AttributesImages')}
{$InventoryImages = \common\helpers\Extensions::isAllowed('InventoryImages')}
{if $AttributesImages || $InventoryImages}
<div class="box-gallery-right">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
                {$rightTabActive = 'class="active"'}
                {if $AttributesImages}
                    <li {$rightTabActive} data-bs-toggle="tab" data-bs-target="#tab_{$Key}_5_1"><a><span>{$smarty.const.TEXT_ASSIGN_TO_ATTR}</span></a></li>
                    {$rightTabActive = ''}
                {/if}
                {if $InventoryImages}
                    <li {$rightTabActive} data-bs-toggle="tab" data-bs-target="#tab_{$Key}_5_2"><a><span>{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                    {$rightTabActive = ''}
                {/if}
        </ul>
        <div class="tab-content">
                {if $AttributesImages}
                    {$AttributesImages::productBlock2($Key, $Item)}
                {/if}
                {if $InventoryImages}
{*                    {$InventoryImages::productBlock2($Key)}*}
                    {$InventoryImages::productBlock2($Key, $Item)}
                {/if}
        </div>
    </div> 
</div>
{/if}
<input type="hidden" name="products_images_id[{$Key}]" value="0" />
<input type="hidden" id="deleted-image-{$Key}" name="products_images_deleted[{$Key}]" value="0" />
</div>
<script type="text/javascript">
$('li.clickable-box-{$Key}').click( function() { 
    $('.jcarousel li').removeClass('active');
    $(this).addClass('active');
    $(".image-box.active").removeClass('active').addClass('inactive');
    var prefix = $(this).attr('prefix');
    $("#"+prefix).removeClass('inactive').addClass('active');
});

$(".check_bot_switch_on_off").bootstrapSwitch(
    {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    }
);


$('.new_upload_{$Key}').uploads();
$('.jcarousel').jcarousel();
$('#image-box-{$Key}').find(':radio.uniform, :checkbox.uniform').uniform();
</script>