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
                <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
            </div>
        </div>
    </div>

    <div class="icon-field">


        <div class="upload-box-{$val_id}-{$lang['id']} upload-box upload-box-wrap upload-box-inline-small"
             data-name="values_image[{$val_id}][{$lang['id']}]"
             data-value="{$value['values_image']}"
             data-upload="values_image_loaded[{$val_id}][{$lang['id']}]"
             data-delete="values_image_delete[{$val_id}][{$lang['id']}]"
             data-type="image"
             data-acceptedFiles="image/*">
        </div>


    </div>
    <div class="remove-field">
        <a href="javascript:delPropValue('{$val_id}')" class="ps_del"><i class="icon-trash"></i></a>

    </div>
</div>
