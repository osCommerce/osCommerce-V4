{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
{use class="Yii"}
<div id="form_management_data">
    {Html::beginForm(Url::to(['orders-comment-template/edit', 'id' => $model->comment_template_id]), 'post', ['name' => 'comment_template', 'enctype' => 'multipart/form-data'])}
    <div class="box-wrap">


        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TABLE_HEADING_STATUS}</label>
                {Html::checkbox('template[status]', !!$model->status, ['class' => 'form-control check_on_off' ])}
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</label>
            </div>
                <div style="padding: 0 0 0 145px;margin-bottom: 10px;">
                    {Html::checkboxList('template[visibility]', $model->getVisibilityArray(), \common\helpers\CommentTemplate::getVisibilityVariants(),['separator'=>'<br/>'])}
                </div>
            </div>
        </div>

        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_HIDE_FOR_PLATFORMS}</label>
                <div class="row">
                    <div class="col-md-12">
                {Html::dropDownList('template[hide_for_platforms]', $model->getHideForPlatformsArray(), $platformsVariants, ['class' => 'form-control multisel-check', 'multiple'=>'multiple' ])}
                    </div>
                </div>
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_ONLY_FOR_ADMIN_GROUP}</label>
                <div class="row">
                    <div class="col-md-12">
                        {Html::dropDownList('template[show_for_admin_group]', $model->getShowForAdminGroupsArray(), $accessLevelsVariants, ['class' => 'form-control multisel-check', 'multiple'=>'multiple' ])}
                    </div>
                </div>
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_HIDE_FROM_ADMIN}</label>
                <div class="row">
                    <div class="col-md-12">
                {Html::dropDownList('template[hide_from_admin]', $model->getHideFromAdminArray(), $adminMemberVariants, ['class' => 'form-control multisel-check', 'multiple'=>'multiple' ])}
                    </div>
                </div>
            </div>
        </div>

        <div class="widget-content tabbable tabbable-custom js-description-tabs">
            {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                        <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
            {/if}
            <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $descriptions  as $lang_id => $description}
                    <div class="tab-pane{if $description@first} active{/if}" id="tab_{\common\classes\language::get_code($lang_id)}">

                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_TEMPLATE_NAME}</label>
                                {Html::textInput('description['|cat:$lang_id|cat:'][name]', $description->name, ['class' => 'form-control' ])}
                            </div>
                        </div>
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_TEMPLATE_COMMENT_TEXT}</label>
                                {Html::textarea('description['|cat:$lang_id|cat:'][comment_template]', $description->comment_template, ['class' => 'form-control', 'style'=>'min-height:180px' ])}
                                <div class="btn-bar">
                                    <button type="button" class="btn js-subst" data-str="##CUSTOMER_NAME##" data-to="description[{$lang_id}][comment_template]">{$smarty.const.TEXT_SUB_CUSTOMER_NAME}</button>
                                    <button type="button" class="btn js-subst" data-str="##STORE_NAME##" data-to="description[{$lang_id}][comment_template]">{$smarty.const.TEXT_SUB_STORE_NAME}</button>
                                </div>
                            </div>
                        </div>


                    </div>
                {/foreach}
            </div>
        </div>

        <div class="btn-bar">
            <div class="btn-left">{Html::a(IMAGE_CANCEL, Url::to(['orders-comment-template/index']), ['class' => 'btn btn-cancel btn-no-margin' ])}</div>
            <div class="btn-right">
                {if $model->isNewRecord}
                    {Html::submitInput(IMAGE_NEW, ['class' => 'btn btn-confirm' ])}
                {else}
                    {Html::submitInput(IMAGE_UPDATE, ['class' => 'btn btn-confirm' ])}
                {/if}
            </div>
        </div>
    </div>
    {Html::endForm()}
</div>
{\backend\assets\MultiSelectAsset::register($this)|void}
<script type="text/javascript">
    $(document).ready(function(){
        $(".check_on_off").bootstrapSwitch({
            onText: "{$smarty.const.SW_ON|escape:'javascript'}",
            offText: "{$smarty.const.SW_OFF|escape:'javascript'}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
        $('.multisel-check').multipleSelect({
            filter: true,
            place:'{$smarty.const.TEXT_SEARCH_ITEMS}',
            minimumCountSelected: 10
        });
        $('.js-description-tabs').on('click', '.js-subst', function(event){
            console.log(event);
            var $btn = $(event.target);
            var $targetArea = $('[name="'+$btn.data('to')+'"]');
            var insertText = $btn.data('str');
            if ( insertText && $targetArea.length>0 ) {
                var cursorPos = $targetArea.prop('selectionStart');
                var cursorPosEnd = $targetArea.prop('selectionEnd');

                var v = $targetArea.val();
                var textBefore = v.substring(0,  cursorPos);
                if ( textBefore.length>0 && textBefore.substring(-1)!==' ') insertText = ' '+insertText;
                var textAfter  = v.substring(cursorPos+(cursorPosEnd-cursorPos), v.length);
                if ( textAfter.length>0 && textAfter.substring(0,1)!==' ') insertText = insertText+' ';
                $targetArea.val(textBefore + insertText + textAfter);
                $targetArea.focus();

                $targetArea.prop('selectionEnd', (textBefore + insertText).length);
            }
            return false;
        });
    });
</script>