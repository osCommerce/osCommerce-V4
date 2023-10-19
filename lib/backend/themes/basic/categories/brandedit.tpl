{use class="yii\helpers\Html"}
{if $app->controller->view->usePopupMode }
  <script type="text/javascript" src="{$app->request->baseUrl}/plugins/ckeditor/ckeditor.js"></script>
  <script type="text/javascript" src="{$app->request->baseUrl}/plugins/ckeditor/adapters/jquery.js"></script>
{/if}
{if $app->controller->view->contentAlreadyLoaded == 0}
<div class="brandEditPage popupEditCat">
{/if}
<form id="save_brand_form" name="brand_edit" onSubmit="return saveManufacturer();">
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            <li class="active" data-bs-toggle="tab" data-bs-target="#tab_2"><a>{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_3"><a>{$smarty.const.TEXT_NAME_DESCRIPTION}</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_4"><a>{$smarty.const.TEXT_SEO}</a></li>
            <li data-bs-toggle="tab" data-bs-target="#tab_supplier"><a>{$smarty.const.TEXT_TAB_SUPPLIERS}</a></li>
            {foreach \common\helpers\Hooks::getList('categories/brandedit', 'tab-navs') as $filename}
                {include file=$filename}
            {/foreach}
        </ul>
        <div class="tab-content">
            <div class="tab-pane active topTabPane tabbable-custom" id="tab_2">
                    <div class="tab-pane active">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_NAME}</td>
                                <td class="label_value">{Html::input('text', 'manufacturers_name', $mInfo->manufacturers_name, ['class' => 'form-control'])}</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="stock-reorder stock-limit row align-items-center mb-3">
                                        <label class="col-auto">{$smarty.const.TEXT_STOCK_LIMIT}</label>
                                        <div class="col-auto"><input type="checkbox" {if $mInfo->stock_limit_on|default:null}checked {/if}/></div>
                                        <div class="col-auto">{Html::input('text', 'stock_limit', $mInfo->stock_limit, ['class'=>'form-control form-control-small-qty'])}</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="widget box">
                                                <div class="widget-header">
                                                    <h4>{$smarty.const.TEXT_GALLERY_IMAGE}</h4>
                                                </div>
                                                <div class="widget-content">
                                                    <div class="about-image">
                                                        <div class="about-image-scheme-1">
                                                            <div></div><div></div><div></div><div></div><div></div><div></div>
                                                        </div>
                                                        <div class="about-image-text">
                                                            {$smarty.const.TEXT_GALLERY_IMAGE_INTRO}
                                                            <ul>
                                                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE1}</li>
                                                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE2}</li>
                                                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE3}</li>
                                                            </ul>
                                                        </div>
                                                    </div>

                                                    {\backend\design\Image::widget([
                                                    'name' => "manufacturers_image",
                                                    'value' => {$mInfo->manufacturers_image|escape},
                                                    'upload' => "image_loaded",
                                                    'delete' => "delete_image"
                                                    ])}
                                                </div>
                                                <div class="divider"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="widget box">
                                                <div class="widget-header">
                                                    <h4>{$smarty.const.TEXT_HERO_IMAGE}</h4>
                                                </div>
                                                <div class="widget-content">
                                                    <div class="about-image">
                                                        <div class="about-image-scheme-2">
                                                            <div></div><div></div><div></div><div></div>
                                                        </div>
                                                        <div class="about-image-text">
                                                            {$smarty.const.TEXT_HERO_IMAGE_INTRO}
                                                            <ul>
                                                                <li>{$smarty.const.SHOULD_NOT_BE_TOO_SMALL}</li>
                                                                <li>{$smarty.const.TEXT_FORMATS}:  jpg, png, gif.</li>
                                                                <li>{$smarty.const.TEXT_COLOR_MODE}: RGB</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    {\backend\design\Image::widget([
                                                    'name' => "manufacturers_image_2",
                                                    'value' => {$mInfo->manufacturers_image_2|escape},
                                                    'upload' => "image_loaded_2",
                                                    'delete' => "delete_image_2"
                                                    ])}
                                                </div>
                                                <div class="divider"></div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            <tr>
                                <td class="label_name">Image map</td>
                              <td  class="label_value">
{include file='../assets/imageMapBlock.tpl' idSuffix="brand" nameSuffix="" value=['mapsTitle' => $mInfo->mapsTitle, mapsId => $mInfo->mapsId, mapsImage => $mInfo->mapsImage]}
                              </td>
                            </tr>
                        </table>

            {foreach \common\helpers\Hooks::getList('categories/brandedit', 'main-tab') as $filename}
                {include file=$filename}
            {/foreach}

                    </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_3">
                {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_l_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content descTab {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $mDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_l_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_DESCRIPTION_LINKS}</td>
                                <td class="label_value">
                                    {$mItem['id']}
                                    {\backend\design\LocalLinksButtons::widget(['editor' => 'txt_brand_description_'|cat:$mItem['languageId'], 'platform_id' => 0, 'languages_id' => $mItem['languageId']])}
                                    <div class="info_desc_links">
                                        {$smarty.const.TEXT_INFO_DESC_LINKS}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_DESCRIPTION}</td>
                                <td class="label_value">{$mItem['manufacturers_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_KEYWORDS}</td>
                                <td class="label_value">{$mItem['manufacturers_meta_key']}</td>
                            </tr>
                        </table>

            {foreach \common\helpers\Hooks::getList('categories/brandedit', 'desc-tab') as $filename}
                {include file=$filename}
            {/foreach}

                    </div>
                    {/foreach}
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_4">
                {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content seoTab {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $mDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">
                        <table class="h-teg-table" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_URL}</td>
                                <td class="label_value">{$mItem['manufacturers_url']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_SEO_NAME}</td>
                                <td class="label_value">{$mItem['manufacturers_seo_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_META_DESCRIPTION}</td>
                                <td class="label_value meta_description">{$mItem['manufacturers_meta_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_MANUFACTURERS_META_TITLE}</td>
                                <td class="label_value meta_title">{$mItem['manufacturers_meta_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H1_TAG}</td>
                                <td class="label_value">{$mItem['manufacturers_h1_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H2_TAG}</td>
                                <td class="label_value"><span id="manufacturers_h2_tag-{$mItem['languageId']}">{foreach explode("\n", $mItem['manufacturers_h2_tag']) as $value}<span class="row"><input type="text" name="manufacturers_h2_tag[{$mItem['languageId']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('manufacturers_h2_tag-{$mItem['languageId']}', '{htmlspecialchars('<span class="row"><input type="text" name="manufacturers_h2_tag['|cat:$mItem['languageId']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H3_TAG}</td>
                                <td class="label_value"><span id="manufacturers_h3_tag-{$mItem['languageId']}">{foreach explode("\n", $mItem['manufacturers_h3_tag']) as $value}<span class="row"><input type="text" name="manufacturers_h3_tag[{$mItem['languageId']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('manufacturers_h3_tag-{$mItem['languageId']}', '{htmlspecialchars('<span class="row"><input type="text" name="manufacturers_h3_tag['|cat:$mItem['languageId']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
                        </table>
                         <script>
                      $(document).ready(function(){
                        $('body').on('click', "#tab_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#tab_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });

                        $('input[name=manufacturers_old_seo_page_name]').change(function(){
                            $('input[name=manufacturers_old_seo_page_name]').val($(this).val());
                        })
                      })
                      </script>
                      
            {foreach \common\helpers\Hooks::getList('categories/brandedit', 'seo-tab') as $filename}
                {include file=$filename}
            {/foreach}
                      
                    </div>
                    {/foreach}
                </div>
            </div>

            <div class="tab-pane topTabPane tabbable-custom" id="tab_supplier">
                <div class="widget box box-no-shadow" style="margin-bottom: 0;">
                    {include file="suppliers-price-data.tpl" supplier_data=$mInfo->supplier_data singleSupplier=0 mayEditCost=true}{*supplierCurrenciesVariants=$mInfo->supplierCurrenciesVariants*}
                </div>
            </div>
                
            {foreach \common\helpers\Hooks::getList('categories/brandedit', 'tab-content') as $filename}
                {include file=$filename}
            {/foreach}
                
        </div>
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
</div>
{tep_draw_hidden_field( 'manufacturers_id', $manufacturers_id )}
{tep_draw_hidden_field( 'manufacturers_image_loaded', '' )}
{if $app->controller->view->usePopupMode}
    <input type="hidden" name="popup" value="1" />
{/if}
</form>
{if $app->controller->view->contentAlreadyLoaded == 0}
</div>
{/if}
<script type="text/javascript">

{if $app->controller->view->contentAlreadyLoaded == 0}

function backStatement() {
    {if $app->controller->view->usePopupMode}
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
    {else}
        window.history.back();
    {/if}
    return false;
}

function saveManufacturer() {
    cke_preload();
    if (typeof(CKEDITOR) == 'object'){
        for ( instance in CKEDITOR.instances ) {
            CKEDITOR.instances[instance].updateElement();
        }
    }
    $.post("{Yii::$app->urlManager->createUrl('categories/brand-submit')}", $('#save_brand_form').serialize(), function(data, status){
        if (status == "success") {
            {if $app->controller->view->usePopupMode}
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove(); 
                $( ".brand_box" ).html(data);
                $('.edit_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing brand <span class='js-popup-brand-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_brand').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete brand</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                resetStatement();
            {else}      
                $('.brandEditPage').html(data);
            {/if}
            //$('#manufacturers_management_data').html(data);
            //$("#manufacturers_management").show();

            //$('.gallery-album-image-placeholder').html('');

            //$('.table').DataTable().search( '' ).draw(false);

            

        } else {
            alert("Request error.");
        }
    },"html");

    //$('input[name=manufacturers_image_loaded]').val();

    return false;
}
{/if}


$(document).ready(function(){
    $('.meta_title input').limitValue('title');
    $('.meta_description textarea').limitValue('description');

    $(".check_on_off").bootstrapSwitch(
      {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );
    {if $app->controller->view->usePopupMode }
      $('.ckeditor').ckeditor();
      {if {$smarty.const.WYSIWYG_EDITOR_POPUP_INLINE ==  'popup'}}
        $('.ckeditor').each(function() {
          $(this).before('<a class="icons popUp popup-editor" href="javascript:void(0);" onclick="popupEditor(\'' + this.form.name + '\', \'' + this.name + '\')">{$smarty.const.TEXT_OPEN_WYSIWYG_EDITOR}</a>');
        });
      {/if}
    {/if}

    {if $mInfo->manufacturers_name}
    $('.js-popup-brand-name').html(' - &quot;{$mInfo->manufacturers_name|escape:'javascript'}&quot;');
    {/if}
        
    $('div.stock-reorder input:checkbox')
            .off()
            .on('change', function() {
                $(this).closest('.stock-reorder').find('input:text.form-control').attr('disabled', 'disabled');
                if ($(this).prop('checked') == true) {
                    $(this).closest('.stock-reorder').find('input:text.form-control').removeAttr('disabled');
                }
            })
            .change();
        
})

function addInput (id, input) {
  $('#' + id).append(input);
}
$('body').on('click', '.del-pt.del-tag', function(){
  $(this).parent().remove();
});
</script>