{use class="yii\helpers\Html"}
<div class="ps_desc_wrapper after prop_value_{$val_id} properties-row">

    <div class="check-field"><input type="checkbox" class="batch-remove"/></div>

    <div class="handle">
        <i class="icon-hand-paper-o"></i>
        {Html::hiddenInput('sort_order['|cat:$lang_id|cat:']['|cat:$val_id|cat:']', $num, ['class'=>'js-sort-order', 'id'=> 'so_'|cat:$val_id|cat:'_'|cat:$lang_id])}
    </div>

    <div class="div-interval name-field">
        <label class="show-interval">{$smarty.const.TEXT_FROM}</label>
        {if {$pInfo->properties_type|default:null == 'text' && $pInfo->multi_line > 0}}
            {if $is_default_lang > 0}
                {Html::textarea('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
            {else}
                {Html::textarea('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
            {/if}
        {else}
            {if $is_default_lang > 0}
                {Html::textInput('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
            {else}
                {Html::textInput('values['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values']])}
            {/if}
        {/if}
        <label class="show-interval label-interval-to">{$smarty.const.TEXT_TO}</label>
        {if $is_default_lang > 0}
            {Html::textInput('values_upto['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_number_upto'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control show-interval', 'placeholder'=>$value['values_number_upto']])}
        {else}
            {Html::textInput('values_upto['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_number_upto'], ['class'=>'form-control show-interval', 'placeholder'=>$value['values_number_upto']])}
        {/if}
        <div class="upload_doc" data-name="upload_docs[{$val_id}][{$lang_id}]" {if {$pInfo->properties_type|default:null == 'file'}}data-value="{$value['values']}"{/if}></div>
    </div>



    <div class="alternative-name-field">
        {if {$pInfo->properties_type|default:null == 'text' && $pInfo->multi_line > 0}}
            {if $is_default_lang > 0}
                {Html::textarea('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
            {else}
                {Html::textarea('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
            {/if}
        {else}
            {if $is_default_lang > 0}
                {Html::textInput('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
            {else}
                {Html::textInput('values_alt['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_alt'], ['class'=>'form-control can-be-textarea', 'placeholder'=>$value['values_alt']])}
            {/if}
        {/if}
    </div>

    <div class="seo-field">
        {if $is_default_lang > 0}
            {Html::textInput('values_seo['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_seo'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang_id|cat:')', 'class'=>'form-control', 'placeholder'=>$value['values_seo']])}
        {else}
            {Html::textInput('values_seo['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_seo'], ['class'=>'form-control', 'placeholder'=>$value['values_seo']])}
        {/if}
    </div>
    <div class="image-map-field">
        {include file='../assets/imageMapBlock.tpl' idSuffix="`$val_id`_"|cat:$lang['id'] nameSuffix="[$val_id]["|cat:$lang.id|cat:"]"}

    </div>

        <div class="prefix-field">
            {Html::textInput('values_prefix['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_prefix'], ['class'=>'form-control', 'placeholder'=>$value['values_prefix']])}
        </div>
        <div class="postfix-field">
            {Html::textInput('values_postfix['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_postfix'], ['class'=>'form-control', 'placeholder'=>$value['values_postfix']])}
        </div>
                    
        
    <div class="color-field">

        <div class="colors-inp">
            <div id="cp3" class="input-group colorpicker-component">
                {Html::textInput('values_color['|cat:$val_id|cat:']['|cat:$lang_id|cat:']', $value['values_color'], ['class'=>'form-control'])}
                <span class="input-group-addon"><i></i></span>
            </div>
        </div>
    </div>

    <div class="icon-field">

        <div class="gallery-filedrop-container-{$val_id}-{$lang_id}">
            <div class="gallery-filedrop">
          <span class="gallery-filedrop-message">
              <a href="#gallery-filedrop" class="gallery-filedrop-fallback-trigger-{$val_id}-{$lang_id} btn" rel="nofollow">{$smarty.const.IMAGE_UPLOAD}</a>
          </span>
                <input size="30" id="gallery-filedrop-fallback-{$val_id}-{$lang['id']}" name="values_image[{$val_id}][{$lang['id']}]" class="elgg-input-file-{$val_id}-{$lang_id} hidden" type="file">
                <input type="hidden" name="values_image_loaded[{$val_id}][{$lang['id']}]" class="elgg-input-hidden">

                <div class="gallery-filedrop gallery-filedrop-queue-{$val_id}-{$lang_id}">
                    <img style="max-height:48px;{if empty($value['values_image'])}display:none;{/if}" src="{if $value['values_image']}{$smarty.const.DIR_WS_CATALOG_IMAGES}{$value['values_image']}{else}{$app->view->theme->baseUrl}/img/no-icon.png{/if}" class="option_image" />
                </div>
            </div>
            <div class="hidden" id="image_wrapper">
                <div class="gallery-template-{$val_id}-{$lang_id}">
                    <div class="gallery-media-summary">
                        <div class="gallery-album-image-placeholder">
                            <img src="">
                            <span class="elgg-state-uploaded"></span>
                            <span class="elgg-state-failed"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="remove-field">
        <a href="javascript:delPropValue('{$val_id}')" class="ps_del"><i class="icon-trash"></i></a>

    </div>
</div>
<script type="text/javascript">
    var createColorpicker_{$val_id}_{$lang_id} = function (){
        setTimeout(function(){
            var cp = $('.colorpicker-component');
            cp.colorpicker({ sliders: {
                saturation: { maxLeft: 200, maxTop: 200 },
                hue: { maxTop: 200 },
                alpha: { maxTop: 200 }
            }});

            var removeColorpicker_{$val_id}_{$lang_id} = function() {
                cp.colorpicker('destroy');
                cp.closest('.popup-box-wrap').off('remove', removeColorpicker_{$val_id}_{$lang_id})
                $('.style-tabs-content').off('st_remove', removeColorpicker_{$val_id}_{$lang_id})
            };

            cp.closest('.popup-box-wrap').on('remove', removeColorpicker_{$val_id}_{$lang_id});
            $('.style-tabs-content').on('st_remove', removeColorpicker_{$val_id}_{$lang_id});
        }, 200)
    };

    createColorpicker_{$val_id}_{$lang_id}();

    $('.gallery-filedrop-container-{$val_id}-{$lang_id}').each(function() {

        var $filedrop = $(this);

        function createImage_{$val_id}_{$lang_id} (file, $container) {
            var $preview = $('.gallery-template-{$val_id}-{$lang_id}', $filedrop);
            $image = $('img', $preview);
            var reader = new FileReader();
            $image.height(48);
            reader.onload = function(e) {
                $image.attr('src',e.target.result);
            };
            reader.readAsDataURL(file);
            $preview.appendTo($('.gallery-filedrop-queue-{$val_id}-{$lang_id}', $container));
            $.data(file, $preview);
        }

//  $(function () {

        $('.gallery-filedrop-fallback-trigger-{$val_id}-{$lang_id}', $filedrop)
            .on('click', function(e) {
                e.preventDefault();
                $('#' + $('.elgg-input-file-{$val_id}-{$lang_id}', $filedrop).attr('id')).trigger('click');
            })

        $filedrop.filedrop({
            fallback_id : $('.elgg-input-file-{$val_id}-{$lang_id}', $filedrop).attr('id'),
            url: "{Yii::$app->urlManager->createUrl('upload/index')}",
            paramname: 'file',
            maxFiles: 1,
            maxfilesize : 20,
            allowedfiletypes: ['image/jpeg','image/png','image/gif'],
            allowedfileextensions: ['.jpg','.jpeg','.png','.gif'],
            error: function(err, file) {
                console.log(err);
            },
            uploadStarted: function(i, file, len) {
                $('.option_image', $filedrop).hide();
                createImage_{$val_id}_{$lang_id}(file, $filedrop);
            },
            progressUpdated: function(i, file, progress) {
                $.data(file).find('.gallery-filedrop-progress').width(progress);
            },
            uploadFinished: function (i, file, response, time) {
                $('.elgg-input-hidden', $filedrop).val(file.name);
            }
        });
//  });

    });

</script>
