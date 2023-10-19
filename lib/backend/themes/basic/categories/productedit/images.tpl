{use class="yii\helpers\Html"}
{use class="\common\classes\Images"}
{if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && count($app->controller->view->images) >= intval(ADMIN_TOO_MANY_IMAGES)}
  {$uniform="uniform-dis"}
{else}
  {$uniform="uniform"}
{/if}
<style>
div.image-size { display:none; font-size: 10px; color: #000000; z-index: 1; width: 180px; height: 30px; top: -15px; position: absolute; left: -20px; }
.upload-remove:hover div.image-size { display:block; }
</style>
<div class="widget box box-no-shadow" style="margin-bottom: 0; border-bottom: 0;">
  <div class="widget-header">
    <h4>{$smarty.const.TEXT_PRODUCT_IMAGES}</h4>
  </div>
  <div class="widget-content{if isset($app->controller->view->images) && count($app->controller->view->images) > 10} no-carusel{/if}" style="padding-bottom: 0;">
    <div class="wrap-prod-gallery">
      <div class="drag-prod-img">
        <div class="upload-container upload-container-with-button">
          <div class="upload-file-wrap">
            <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>
            <div class="upload-file"></div>
            <div class="upload-hidden"><input type="hidden" name="image_buffer"/></div>
          </div>
          <div class="upload-file-additional-button">
            <a href=" javascript:void(0);" class="btn" onclick="return addExternalImage();">Add external image</a>
          </div>
        </div>
      </div>
      <div class="jcarousel-wrapper">
        <div class="jcarousel" id="jcarousel-images-listing">
          <ul id="images-listing">
            {if isset($app->controller->view->images)}
            {foreach $app->controller->view->images as $Key => $Item}
              <li{if $Key == 0} class="active"{/if} prefix="image-box-{$Key}"><span class="handle"><i class="icon-hand-paper-o"></i></span><span><img id="preview-box-{$Key}" {if defined('ADMIN_TOO_MANY_IMAGES') && $Key>intval(ADMIN_TOO_MANY_IMAGES)}class="invisible" style="width:92%" src="data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DABAAECAEDnPpQlwAAAABJRU5ErkJggg== " data-{/if}src="{$Item['image_url']}" data-idx="{$Key}" /></span><div onclick="removeImage(this, {$Key});" class="upload-remove"></div></li>
            {/foreach}
            {/if}
          </ul>
        </div>
        <a href="#" class="jcarousel-control-prev"></a>
        <a href="#" class="jcarousel-control-next"></a>
      </div>
      <input type="hidden" value="" name="images_sort_order" id="images_sort_order"/>
    </div>
  </div>
</div>
<div class="box-gallery after">
  {if isset($app->controller->view->images)}
  {foreach $app->controller->view->images as $Key => $Item}
    <div id="image-box-{$Key}" class="image-box {if $Key == 0}active inited{else}inactive{/if}">
      <div class="box-gallery-left">
        <div class="edp-our-price-box">
          <div class="widget widget-full box box-no-shadow" style="margin-bottom: 0">
            <div class="widget-content after">
              <div class="status-left st-origin">
                <span>{$smarty.const.TEXT_STATUS}</span>
                <input type="checkbox" value="1" name="image_status[{$Key}]" class="check_bot_switch_on_off{if $Key != 0}_ni{/if}"{if $Item['image_status'] == 1} checked="checked"{/if} />
              </div>
              <div class="status-left">
                <span>{$smarty.const.TEXT_DEFAULT_IMG}:</span>
                <input type="radio" value="{$Key}" name="default_image" class="default-images check_bot_switch_on_off{if $Key != 0}_ni{/if}"{if $Item['default_image'] == 1} checked="checked"{/if} />
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
                  {foreach $Item['description'] as $DKey => $DItem}
                    <li data-bs-toggle="tab" data-bs-target="#tab_4_{$Key}_{$DItem['key']}"><a class="flag-span">{$DItem['logo']}<span>{$DItem['name']}</span></a></li>
                  {/foreach}
                </ul>
                <div class="tab-content tab-content-vertical">
                  <div class="tab-pane active one-img-gal{if $Item['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_0">
                    <div class="one-img-gal-left">
                      <div class="drag-prod-img-2">
                        <div class="upload{if defined('ADMIN_TOO_MANY_IMAGES') && $Key>intval(ADMIN_TOO_MANY_IMAGES)}-ni{/if} upload-{$Key}-0" data-linked="preview-box-{$Key}" {if $Item['use_external_images']}data-external-image="{$Item['image_url']}"{/if} data-name="orig_file_name[{$Key}][0]"{if $Item['preload']} data-preload="{$Item['preload']|escape:'html'}"{/if} data-show="ofn_{$Key}_0" data-value="{$Item['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
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
                        <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_0');" name="use_external_images[{$Key}][0]" value="1" {if $Item['use_external_images']} checked{/if} class="{$uniform}" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
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
                      {if is_array($Item['imageSize'])}<div>{$smarty.const.TEXT_ORIGINAL_IMAGE_SIZE} {$Item['imageSize'][0]}x{$Item['imageSize'][1]}px</div>{/if}
                    
                      {* Implements Product Design constructor *}
                      {if \common\helpers\Acl::checkExtensionAllowed('ProductDesigner', 'allowed')}
                         {\common\extensions\ProductDesigner\ProductDesigner::imageBlock($Item)}
                      {/if}
                    </div>
                  </div>
                  {foreach $Item['description'] as $DKey => $DItem}
                    <div class="tab-pane one-img-gal{if $DItem['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_{$DItem['key']}">
                      <div class="one-img-gal-left">
                        <div class="drag-prod-img-2">
                          <div class="upload" data-name="orig_file_name[{$Key}][{$DItem['id']}]" {if $DItem['use_external_images']}data-external-image="{$DItem['image_url']}"{/if}{if $Item['preload']} data-preload="{$DItem['preload']|escape:'html'}"{/if} data-show="ofn_{$Key}_{$DItem['id']}" data-value="{$DItem['image_name']}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                        </div>
                      </div>
                      <div class="one-img-gal-right">
                        <div class="our-pr-line">
                          <label>{$smarty.const.TEXT_ORING_NAME}</label>
                          <span id="ofn_{$Key}_{$DItem['id']}">{$DItem['orig_file_name']}&nbsp;</span>
                          <label><input type="checkbox" name="use_origin_image_name[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_origin_image_name'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_USE_ORIGIN_IMAGE_NAME}</label>
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
                          <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_{$DItem['key']}')" name="use_external_images[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_external_images']} checked{/if} class="{$uniform}" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
                        </div>
                        <div class="our-pr-line external_images-hide js-image-toggle">
                          <label><input type="checkbox" name="alt_file_name_flag[{$Key}][{$DItem['id']}]" value="1" {if $DItem['alt_file_name'] != ""} checked{/if} class="{$uniform} js-image-toggle-source" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                          <input type="text" name="alt_file_name[{$Key}][{$DItem['id']}]" value="{$DItem['alt_file_name']|escape:'html'}" class="form-control js-image-toggle-target" {if $DItem['alt_file_name'] == ""} style="display: none;"{/if} />
                        </div>
                        <div class="our-pr-line external_images-hide">
                          <label><input type="checkbox" name="no_watermark[{$Key}][{$DItem['id']}]" value="1" {if $DItem['no_watermark'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                        </div>
                        <div class="external_images-show">
                            <div class="our-pr-line">
                              <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,'Original')}</label>
                              <input type="text" name="external_image_original[{$Key}][{$DItem['id']}]" value="{$DItem['external_image_original']|escape:'html'}" class="form-control" />
                            </div>
                            {foreach from=$DItem['external_images'] item=external_image name=imageTypeList}
                              <div class="our-pr-line">
                                <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,$external_image['image_types_name'])} ({$external_image['image_size']})</label>
                                <input type="text" name="external_image[{$Key}][{$DItem['id']}][{$external_image['image_types_id']}]" value="{$external_image['image_url']|escape:'html'}" class="form-control{if $smarty.foreach.imageTypeList.last} biggestImage{/if}" />
                              </div>
                            {/foreach}
                        </div>
                        {if is_array($DItem['imageSize'])}<div>{$smarty.const.TEXT_ORIGINAL_IMAGE_SIZE} {$DItem['imageSize'][0]}x{$DItem['imageSize'][1]}px</div>{/if}
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
            {if $app->controller->view->showInventory == true}
                {if $InventoryImages}
                    <li {$rightTabActive} data-bs-toggle="tab" data-bs-target="#tab_{$Key}_5_2"><a><span>{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                    {$rightTabActive = ''}
                {/if}
            {/if}
          </ul>
          <div class="tab-content">
                {if $AttributesImages}
                    {$AttributesImages::productBlock2($Key, $Item)}
                {/if}
            {if $app->controller->view->showInventory == true}
                {if $InventoryImages}
                    {$InventoryImages::productBlock2($Key, $Item)}
                {/if}
            {/if}
          </div>
        </div>
      </div>
      {/if}
      <input type="hidden" name="products_images_id[{$Key}]" value="{$Item['products_images_id']}" />
      <input type="hidden" id="deleted-image-{$Key}" name="products_images_deleted[{$Key}]" value="0" />
    </div>
  {/foreach}
  {/if}

  <script type="text/javascript">
    var imagesQty = {$app->controller->view->imagesQty};
    var imagepath = "{$app->controller->view->upload_path}";

    function removeImage(obj, key) {
      $("#image-box-"+key).removeClass('active').addClass('inactive');
      $("#deleted-image-"+key).val('1');
      $(obj).parent().remove();
      $('#save_product_form').trigger('image_removed',[key]);
    }

    function uploadRemove(obj, show, linked) {
      $(obj).parent().remove();
      if (show != undefined) {
        $('#'+show).text(' ');
      }
      if (linked != undefined) {
        $('#'+linked).hide();
      }
    }

    function imageSwitchExtInt(id)
    {
        var $tab = $('#'+id);
        $tab.toggleClass('product-image-external-active');
        var $upload = $tab.find('.upload');
        if ( $upload.length>0 ) {
            var $previewImg = $('#'+$upload.data('linked'));
            if ($tab.hasClass('product-image-external-active')){
                $previewImg.attr('src',$upload.data('external-image'));
            }else{
                $previewImg.attr('src',$upload.data('value'));
            }
        }

        return true;
    }

    function uploadSuccess(linked, name) {
      $('#'+linked).attr('src', imagepath+name);
      $('#'+linked).show();
    }

    $('.upload-container').each(function() {

      var _this = $(this);

      $('.upload-file', _this).dropzone({
        url: "{Yii::$app->urlManager->createUrl('upload/index')}",
        sending:  function(e, data) {
          $('.upload-hidden input[type="hidden"]', _this).val(e.name);
          $('.upload-remove', _this).on('click', function(){
            $('.dz-details', _this).remove()
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details" style="display: none;"><img data-dz-thumbnail /></div>',
        drop: function(){
          $('.upload-file', _this).html('')
        },
        success: function(e, data) {


          setTimeout(function () {

            $("#images-listing").append('<li class="clickable-box-'+imagesQty+'" prefix="image-box-'+imagesQty+'"><span class="handle"><i class="icon-hand-paper-o"></i></span><span><img id="preview-box-'+imagesQty+'" src="'+imagepath+e.name+'" /></span><div onclick="removeImage(this, '+imagesQty+');" class="upload-remove"></div></li>');
            var isFirstImage = $("#images-listing li").length==1;
            //$("#images-listing").append('<li class="clickable-box-'+imagesQty+'" prefix="image-box-'+imagesQty+'"><span><img src="'+$('img', _this).attr('src')+'" /></span><div onclick="removeImage(this, '+imagesQty+');" class="upload-remove"></div></li>');

            $.get("{Yii::$app->urlManager->createUrl('categories/product-new-image')}", { id: imagesQty, name: e.name }, function(data, status){
              if (status == "success") {
                var $data = $(data);
                if ( isFirstImage ) {
                    $data.find("input[name='default_image']").attr('checked','checked');
                }
                $(".box-gallery.after").append($data);
                $('.widget-content:not(.no-carusel) .jcarousel').jcarousel('scroll', -1); //TODO: better on first uploaded (if batch upload)
                  $('.no-carusel .jcarousel').scrollLeft(100000)
                $('#save_product_form').trigger('new_image_uploaded');
              } else {
                alert("Request error.");
              }
            },"html");



            //$('.upload-file', _this).html('');
            imagesQty++;
          }, 200);

        },
          error: function(){
              alertMessage('<div class="alert-message">Request error.</div>')
          }
      });

    });

    function addExternalImage()
    {
        var imageUrl = ''; //imagepath+e.name
        $("#images-listing").append('<li class="clickable-box-'+imagesQty+'" prefix="image-box-'+imagesQty+'"><span class="handle"><i class="icon-hand-paper-o"></i></span><span><img id="preview-box-'+imagesQty+'" '+(imageUrl?'src="'+imageUrl+'"':'')+' /></span><div onclick="removeImage(this, '+imagesQty+');" class="upload-remove"></div></li>');
        var isFirstImage = $("#images-listing li").length==1;
        $.get("{Yii::$app->urlManager->createUrl('categories/product-new-image')}", { id: imagesQty, external: '1', pid: '{$pInfo->products_id}' }, function(data, status){
            if (status == "success") {
                var $data = $(data);
                if ( isFirstImage ) {
                    $data.find("input[name='default_image']").attr('checked','checked');
                }
                $(".box-gallery.after").append($data);
                $('.widget-content:not(.no-carusel) .jcarousel').jcarousel('scroll', -1); //TODO: better on first uploaded (if batch upload)
                $('.no-carusel .jcarousel').scrollLeft(100000)
                $('#save_product_form').trigger('new_image_uploaded');
            } else {
                alert("Request error.");
            }
        },"html");
        imagesQty++;
        return false;
    }


    $(document).ready(function(){
      var $main_form = $('#save_product_form');

      var sync_image_attributes = function(){
        var selected_attr = [];
        var selected_opt = { };

        var $attributes = $main_form.find('select[name="attributes"]');
        var get_id_re = /^products_attributes_id\[(\d+)\]\[(\d+)\]/;
        var check_opt_pass = { };
        $main_form.find('input[name^="products_attributes_id\["]').each(function(){
          var rr = this.name.match(get_id_re);
          if ( rr ){
            var ov_pair = rr[1]+'_'+rr[2];
            selected_attr.push(ov_pair);
            selected_opt[''+rr[1]] = rr[2];
            if ( !check_opt_pass[rr[1]] && $('.js-option-group-images[data-ov_id="'+rr[1]+'"]').length==0 ) {
              $('.js-option-images').each(function(){
                $(this).append('<label class="js-option-group-images" data-ov_id="'+rr[1]+'">'+$attributes.find('optgroup[id="'+rr[1]+'"]').attr('label')+'</label><ul class="js-option-group-images" data-ov_id="'+rr[1]+'"></ul>');
              });
              check_opt_pass[rr[1]] = rr[1];
            }
            if ($('.js-option-value-images[data-ov_pair="'+ov_pair+'"]').length == 0) {
              $('ul.js-option-group-images[data-ov_id="'+rr[1]+'"]').each(function(){
                var key = $(this).parents('.image-box').attr('id').replace('image-box-','');
                {if \common\helpers\Acl::checkExtensionAllowed('AttributesImages', 'allowed')}
                $(this).append('<li class="js-option-value-images" data-ov_pair="'+ov_pair+'"><label><input type="checkbox" name="image_attr['+key+']['+rr[1]+']['+rr[2]+']" value="1" class="{$uniform}" /> '+$attributes.find('option[value="'+rr[2]+'"]').html()+'</label></li>');
                {else}
                $(this).append('<li class="js-option-value-images" data-ov_pair="'+ov_pair+'"><label><input type="checkbox" disabled name="image_attr['+key+']['+rr[1]+']['+rr[2]+']" value="1" class="{$uniform}" /> '+$attributes.find('option[value="'+rr[2]+'"]').html()+'</label></li>');
                {/if}
              });
            }
          }
        });
        $('.js-option-images').each(function(){
          var $set = $(this);
          var selected_attr_str = '|'+selected_attr.join('|')+'|';
          $('.js-option-group-images',$set).each(function(){
            var $ws = $(this);
            var opt_id = $ws.attr('data-ov_id');
            if ( typeof selected_opt[opt_id] !== 'undefined' ){
              if ($ws.hasClass('hide-default')) $ws.removeClass('hide-default');
              $('.js-option-value-images[data-ov_pair]',$ws).each(function(){
                var $wso = $(this);
                if (selected_attr_str.indexOf($wso.attr('data-ov_pair'))!==-1){
                  // need
                  if ($wso.hasClass('hide-default')) $wso.removeClass('hide-default');
                }else{
                  if (!$wso.hasClass('hide-default')) $wso.addClass('hide-default');
                }
              });
            }else{
              if (!$ws.hasClass('hide-default')) $ws.addClass('hide-default');
            }
          });
        });
      };
      $main_form.on('attributes_changed',sync_image_attributes);
      $main_form.on('new_image_uploaded',sync_image_attributes);
      //sync_image_attributes();
      $main_form.trigger('attributes_changed');

      var rebuild_images_inventory = function(){

        var $tplContainer = $('#new_image_inventory');
        if ( $tplContainer.length==0 ) return;
        var tpl = $tplContainer.html();
        // disable if too many images or variations
        var img_inventory_allowed = true;
{if !defined('ADMIN_TOO_MANY_IMAGES') || (isset($app->controller->view->images) && $app->controller->view->images|@count < intval(ADMIN_TOO_MANY_IMAGES))}
        img_inventory_allowed = ($('#new_image_inventory input.image_inventory').length < 100);
{else}
            img_inventory_allowed = false;
{/if}
        // disable if too many images or variations eof
        
        $('.js_image_inventory').each(function(){
          var $cont = $(this);
          
          if (!img_inventory_allowed) {
            $cont.html('{$smarty.const.TOO_MANY_IMAGES_SETUP_ATTRIBUTES|escape:javascript}');
            return;
          }

          var image_idx = $cont.attr('data-image_idx');
          var $new_content = $(tpl.replace(/%%img_idx%%/g, image_idx));
          $cont.find('input:checked').each(function(){
            var new_checkbox = $new_content.find('input[value="'+$(this).val()+'"]');
            if ( new_checkbox.length>0 ) {
              new_checkbox[0].checked = true; new_checkbox.attr( 'checked', 'checked' );
            }
          });

{if !defined('ADMIN_TOO_MANY_IMAGES') || (isset($app->controller->view->images) && $app->controller->view->images|@count < intval(ADMIN_TOO_MANY_IMAGES))}
          if ($new_content.find(':radio.uniform, :checkbox.uniform').length<200) {
            $new_content.find(':radio.uniform, :checkbox.uniform').uniform();
          }
{/if}
          $cont.html($new_content);
        });
      };
      $main_form.on('inventory_arrived',rebuild_images_inventory);
      $main_form.on('new_image_uploaded',rebuild_images_inventory);

/* sync selected images per attributes and inventory at images tab*/
      var sync_images_check_state = function(){
      {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
          return;
      {/if}
        var checked_ov = { },
            unchecked_ov = { },
            unchecked_inv = { },
            checked_inv = { };
        $('#save_product_form').find('[name^="image_attr"]:checked').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var pair = ids[2]+'_'+ids[3]; //img
            if ( typeof checked_ov[pair] === 'undefined' ) checked_ov[pair] = [];
            checked_ov[pair].push( parseInt(img_idx,10) );
          }
        });
        $('#save_product_form').find('[name^="image_attr"]').each(function(){
          var ids = this.name.match(/\[(\d+)\]\[(\d+)\]\[(\d+)\]/);
          if ( ids ) {
            var img_idx = ids[1]; //img
            var pair = ids[2]+'_'+ids[3]; //img
            if ( typeof checked_ov[pair] === 'undefined' ) {
                if ( typeof unchecked_ov[pair] === 'undefined' ) unchecked_ov[pair] = [];
                unchecked_ov[pair].push( parseInt(img_idx,10) );
            }
          }
        });
        if  (false) { ///could be tooo many
          $('#save_product_form').find('[name^="image_inventory"]:checked').each(function(){
            var ids = this.name.match(/\[(\d+)\]\[(\d+)\]/);
            if ( ids ) {
              var img_idx = ids[1]; //img
              var uprid = this.value; //img
              if ( typeof checked_inv[uprid] === 'undefined' ) checked_inv[uprid] = [];
              checked_inv[uprid].push( parseInt(img_idx,10) );
            }
          });
          $('#save_product_form').find('[name^="image_inventory"]').each(function(){
            var ids = this.name.match(/\[(\d+)\]\[(\d+)\]/);
            if ( ids ) {
              var img_idx = ids[1]; //img
              var uprid = this.value; //img

              if ( typeof checked_inv[uprid] === 'undefined' ) {
                  if ( typeof unchecked_inv[uprid] === 'undefined' ) unchecked_inv[uprid] = [];
                  unchecked_inv[uprid].push( parseInt(img_idx,10) );
              }
            }
          });
        }
        var sel = $('select[name="divselktr"]');
        for( var pair in checked_ov ) {
          if ( ! checked_ov.hasOwnProperty(pair) ) continue;
          var mc_ov = sel.filter('[data-ov_pair="'+pair+'"]');
          if ( mc_ov.length>0 ) {
            mc_ov.val(checked_ov[pair]); mc_ov.trigger('change');
            mc_ov.multiselect('refresh');
          }
        }
        for( var pair in unchecked_ov ) {
            if ( ! unchecked_ov.hasOwnProperty(pair) ) continue;
          var mc_ov = sel.filter('[data-ov_pair="'+pair+'"]');
          if ( mc_ov.length>0 ) {
            mc_ov.val([]).trigger('change').multiselect('refresh');
          }
        }
        if  (false) { ///could be tooo many
          for( var uprid in checked_inv ) {
            if ( ! checked_inv.hasOwnProperty(uprid) ) continue;
            var mc_inv = sel.filter('[data-uprid="'+uprid+'"]');
            if ( mc_inv.length>0 ) {
              mc_inv.val(checked_inv[uprid]); mc_inv.trigger('change');
              mc_inv.multiselect('refresh');
            }
          }
          for( var uprid in unchecked_inv ) {
            if ( ! unchecked_inv.hasOwnProperty(uprid) ) continue;
            var mc_inv = sel.filter('[data-uprid="'+uprid+'"]');
            if ( mc_inv.length>0 ) {
                mc_inv.val([]).trigger('change').multiselect('refresh')
            }
          }
        }
      };
      var sync_images_check_state_back = function(e){
      {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
          return;
      {/if}
        var $select = $(e.target);
        var selected_now = $select.val(), selected_str;
        if ( $select.attr('data-ov_pair') ) {
          var check_attr = $('[name^="image_attr\["]');
          selected_str = '_'+($.isArray(selected_now)?selected_now.join('_'):selected_now)+'_';
          var pair_name_ending = ']['+($select.attr('data-ov_pair').replace('_',']['))+']';
          check_attr.filter(function(idx, checkbox) {
            if ( checkbox.name.indexOf(pair_name_ending)===-1 ) return false;
            var img_id = checkbox.name.match(/image_attr\[(\d+)\]/);
            if ( img_id ) {
              return (selected_str.indexOf('_'+img_id[1]+'_')===-1?checkbox.checked:!checkbox.checked);
            }
            return false;
          }).trigger('click');
        }
        if ( $select.attr('data-uprid') ) {
          var check_inv = $('[name^="image_inventory\["]');
          selected_str = '_'+($.isArray(selected_now)?selected_now.join('_'):selected_now)+'_';
          check_inv.filter('[value="'+$select.attr('data-uprid')+'"]').filter(function(idx, checkbox) {
            var img_id = checkbox.name.match(/image_inventory\[(\d+)\]/);
            if ( img_id ) {
              return (selected_str.indexOf('_'+img_id[1]+'_')===-1?checkbox.checked:!checkbox.checked);
            }
            return false;
          }).trigger('click');
        }
      };
      $main_form.on('inventory_arrived',function(){
        $('select[name="divselktr"]').on('change', sync_images_check_state_back);
        //default values are set on server, init MS on click sync_images_check_state();
      });
{if !defined('ADMIN_TOO_MANY_IMAGES') || (isset($app->controller->view->images) && $app->controller->view->images|@count < intval(ADMIN_TOO_MANY_IMAGES))}
      $(document).on('click',function(e){
        if ( e.target && e.target.name ){
          var target_name = e.target.name;
          if ( target_name.indexOf('image_inventory[')===0 ) {
            //2check VL sync_images_check_state(e.target.value);
            var idxs = target_name.match(/\[(\d+)\]\[(\d+)\]/);
            var idx= idxs[1];
            var ms = document.getElementById('divselktr' + e.target.value);
            ms.options[idx].selected = e.target.checked;
            multiselectInit(ms, true);
          }
          if ( target_name.indexOf('image_attr[')===0 ) {
            sync_images_check_state();
          }
        }
      });
{/if}
      var rebuild_images = function() {
      {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
          return;
      {/if}
        var options = '';
        $('#images-listing li[prefix]').each(function () {
          var Key = $(this).attr('prefix').replace('image-box-', ''),
                  src = '';
          var $img = $(this).find('img[id="preview-box-' + Key + '"]');
          if ($img.length > 0) src = $img.attr('src');
          options += '<option value="'+Key+'" image="'+src+'" class="multSelktrImg"> </option>';
        });
        $('select[name="divselktr"]').html(options);
        //$('select[name="divselktr"]').multiselect('refresh');
        sync_images_check_state();
      };
      $('#save_product_form').on('new_image_uploaded image_removed', rebuild_images);

      $(document).on('change keyup paste','.product-image-external-active',function(event){
          if ( !event.target.name || event.target.name.indexOf('external_image[')!==0 ) return;
          var checkControl = event.target.name.match(/^external_image\[(\d+)\]\[(\d+)\]/);
          if ( checkControl ) {
              var imageIdx = checkControl[1];
              var $holder = $(event.currentTarget);
              var $input = $(event.target);
              if ( $input.hasClass('biggestImage') ) {
                  if (checkControl[2] == '0') {
                      $('#preview-box-' + imageIdx).attr('src', $input.val());
                      $('#save_product_form').trigger('new_image_uploaded');
                  }
                  var $imageHolder = $('.upload-file', $holder);
                  var $sideImage = $imageHolder.find('.dz-details.external_images-show img');
                  if ( $sideImage.length==0 ) {
                      $imageHolder.append('<div class="dz-details external_images-show"><img /></div>');
                      $sideImage = $imageHolder.find('.dz-details.external_images-show img');
                  }
                  $sideImage.attr('src',$input.val());
                  $holder.find('.upload').attr('data-external-image',$input.val());
              }
          }
      })
    });

function invMultiselectInit (el) {
      {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
          return;
      {/if}
  if ( $(el).attr('data-uprid') ) {
    $(el).remove();
    multiselectInit(document.getElementById('divselktr' + $(el).attr('data-uprid')));
    $(document.getElementById('divselktr' + $(el).attr('data-uprid'))).siblings('.ui-multiselect').trigger('click');
  }
}

function multiselectInit (el, refresh=false) {
      {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
          return;
      {/if}
    $(el).multiselect({
        multiple: true,
        height: '205px',
        header: 'See the images in the rows below:',
        noneSelectedText: 'Select',
        selectedText: function(numChecked, numTotal, checkedItems){
          return numChecked + ' of ' + numTotal;
        },
        selectedList: false,
        show: ['blind', 200],
        hide: ['fade', 200],
        position: {
            my: 'left top',
            at: 'left bottom'
        }
    });
    try {
      btnId = el.id.replace("divselktr", "msInvBtn");
      $(document.getElementById(btnId)).remove();
      if (refresh && typeof $(el).multiselect !== 'undefined') {
        $(el).multiselect('refresh');
      }
    }catch(err) { }
}

  </script>
</div>

<div class="row" style="margin-top: 20px">

  <div class="col-md-6">
    <div class="widget box">
      <div class="widget-header">
        <h4>{$smarty.const.IMAGE_MAP}</h4>
      </div>
      <div class="widget-content">
        <div class="category-image-map form-container">
          <div class="row">

            <div class="col-md-3" style="padding: 20px 0">
              <label for="">{$smarty.const.IMAGE_MAP_NAME}</label>
            </div>
            <div class="col-md-6" style="padding: 20px 0">
              <input type="text" class="form-control map-name" name="map_name" value="{if isset($app->controller->view->mapsTitle)}{$app->controller->view->mapsTitle}{/if}" autocomplete="off"/>
              <input type="hidden" name="maps_id" value="{if isset($app->controller->view->mapsId)}{$app->controller->view->mapsId}{/if}"/>
              <div class="search-map"></div>
            </div>
            <div class="col-md-3">
              <div class="map-image-holder">
              <img {if isset($app->controller->view->mapsImage) && $app->controller->view->mapsImage!=''}src="../images/maps/{$app->controller->view->mapsImage}"{/if} class="map-image" alt="" {if !(isset($app->controller->view->mapsImage) && $app->controller->view->mapsImage)} style="display: none" {/if}>
                <div class="map-image-remove" {if !(isset($app->controller->view->mapsImage) && $app->controller->view->mapsImage)} style="display: none" {/if}></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="divider"></div>
    </div>
  </div>
</div>

<script>
$(function(){

    let searchProductBox = $('.search-map');
    $('.map-name').keyup(function(e){
        $.get('image-maps/search', {
            key: $(this).val()
        }, function(data){
            $('.suggest').remove();
            searchProductBox.append('<div class="suggest">'+data+'</div>');

            $('a', searchProductBox).on('click', function(e){
                e.preventDefault();

                $('input[name="maps_id"]').val($(this).data('id'));
                $('input[name="map_name"]').val($('.td_name', this).text());
                if ($(this).data('image').length) {
                  $('.map-image').show().attr('src', '../images/maps/' + $(this).data('image'));
                  $('.map-image-remove').show();
                }

                $('.suggest').remove();
                return false
            })
        })
    });

    $('.map-image-remove').on('click', function(){
        $('input[name="maps_id"]').val('');
        $('input[name="map_name"]').val('');
        $('.map-image').show().attr('src', '');
        $(this).hide()
    });

    {if defined('ADMIN_TOO_MANY_IMAGES') && isset($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
        var imgToShow = function (el) {
          $(el).css('width', 'auto');
          $(el).attr('src',$(el).attr('data-src'));
          $(el).off('click');
          //init preview 0 box
          var idx = $(el).attr('data-idx');
          $('.upload-' + idx + '-0').addClass('upload').removeClass('upload-ni');
          $('.upload-' + idx + '-0').uploads();
        }

        $('#images-listing img.invisible').on('click', function () { imgToShow(this); } );

        $('#jcarousel-images-listing').on('jcarousel:scrollend', function(event, carousel) {
          $('#images-listing img.to-show:lt(5)').each(function (){
            imgToShow(this);
            $(this).removeClass('to-show');
          });

        });


        $('#images-listing img.invisible').removeClass('invisible').addClass('to-show');

    {/if}
})
</script>