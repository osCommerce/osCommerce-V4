{use class="yii\helpers\Html"}
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<div style="font-style: italic; font-size: 14px;margin-bottom:10px">{$editBreadCrumbInfo}</div>
{if not {$app->controller->view->usePopupMode|default:null}}
    <div class="">
        <a back-href="{$href_back}" onclick="return backStatement()" href="javascript:void(0);" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
    </div>
{/if}
<div class="location-edit-content">
    <form action="{Yii::$app->urlManager->createUrl(['seo-delivery-location/location-save','platform_id'=>$platform_id])}" method="post" enctype="multipart/form-data" id="frmDeliveryLocation" onsubmit="return saveDeliveryLocation()">
        {Html::hiddenInput('item_id', $location_data['id'])}
        {Html::hiddenInput('parent_id', $parent_id)}
        <div class="prop_wrapper">
            <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_main" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
                    <li><a href="#tab_details" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                    <li><a href="#tab_seo" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
                    <li><a href="#tab_products" data-toggle="tab"><span>{$smarty.const.TEXT_PRODUCTS}</span></a></li>
                    <li><a href="#tab_categories" data-toggle="tab"><span>{$smarty.const.DATE_PRODUCT_CATEGORIES}</span></a></li>
                    <li><a href="#tab_crpsspages" data-toggle="tab"><span>{$smarty.const.TEXT_PAGES}</span></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane topTabPane tabbable-custom active" id="tab_main">
                        {*<div class="md_row after">
                            <label for="status">{$smarty.const.TEXT_CATEGORIES_STATUS}</label>
                            <div class="md_value"><input type="checkbox" value="1" name="status" class="check_on_off"{if $location_data['status'] == 1} checked="checked"{/if}></div>
                            <input type="hidden" name="status_present" value="1">
                        </div>*}
                        {include file="edit-description.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_details">
                        {include file="edit-details.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_seo">
                        {include file="edit-seo.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_products">
                        {include file="edit-products.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_categories">
                        {include file="edit-categories.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_crpsspages">
                        {include file="edit-crpsspages.tpl"}
                    </div>
                </div>
            </div>

        </div>
        <div class="btn-bar edit-btn-bar">
            <div class="btn-left">
            {if not {$app->controller->view->usePopupMode|default:null}}
                <a back-href="{$href_back}" onclick="return backStatement()" href="javascript:void(0);" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
            {/if}
            </div>
            <div class="btn-right">
                <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
            </div>
        </div>
    </form>
</div>
<br>
<script type="text/javascript">
    <!--
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchChange(element, arguments);
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '38px',
            labelWidth: '24px'
        }
    );

    function backStatement() {
        window.history.back();
        return false;
    }

    function saveDeliveryLocation() {
        var form = $('#frmDeliveryLocation');
        if (typeof(CKEDITOR) == 'object'){
            for ( var instance in CKEDITOR.instances ) {
                if ( CKEDITOR.instances.hasOwnProperty(instance) ) {
                    CKEDITOR.instances[instance].updateElement();
                }
            }
        }
        $.post( form.attr('action'), form.serializeArray(), function(data){
            //alertMessage(data);
            $('.location-edit-content').append(data);
            if ($('[name="image_listing_loaded"]',form).val()) {
                $('[name="image_listing"]', form).val($('[name="image_listing_loaded"]', form).val());
            }
            if ($('[name="image_headline_loaded"]',form).val()) {
                $('[name="image_headline"]', form).val($('[name="image_headline_loaded"]', form).val());
            }
            $('[name="image_listing_loaded"]',form).val('');
            $('[name="image_headline_loaded"]',form).val('');
        });
        return false;
    }

    $(document).ready(function(){
        $('.js-template-edit').on('click',function(){
            var params = [
                {
                    name:'parent_id',
                    value: '{$parent_id}'
                }
            ];
            $('<a href="{Yii::$app->urlManager->createUrl(['seo-delivery-location/template-edit','platform_id'=>$platform_id])}'+($(this).attr('data-params')||'')+'"></a>').popUp({
                data: params,
                box_class: 'box-location-template'
            }).trigger('click');
            return false;
        });
        $('.js-template-preview').on('click',function(){
            var params = $('#frmDeliveryLocation').serializeArray();
            params.push({
                name:'parent_id',
                value: '{$parent_id}'
            });
            var formData = [];
            for( var i=0; i<params.length; i++ ) {
                formData.push('<input type="hidden" name="'+params[i].name+'" value="'+params[i].value.replace(/"/g,'&quot;')+'" />');
            }

            $('<form method="post" action="{Yii::$app->urlManager->createUrl(['seo-delivery-location/template-preview','platform_id'=>$platform_id])}'+($(this).attr('data-params')||'')+'">'+formData.join('')+'</form>').popUp({
                data: params,
                box_class: 'box-location-template'
            }).trigger('submit');
            return false;
        });
    });

    //-->
</script>
<style type="text/css">
    .box-location-template{ width: 70%};
</style>