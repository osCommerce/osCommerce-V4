{use class="common\helpers\Translation"}
{use class="common\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

           <!--=== Page Content ===-->


							<div class="widget-content metatags_div">
                <form name="meta_tags" action="{$update_form_action}" method="post" {if $isMultiPlatform}class="wtabplform"{/if}>
              <!-- TABS-->
              {if $isMultiPlatform}<div class="tab-radius">{/if}
                <div class="tabbable tabbable-custom tabbable-ep">
                  <ul class="nav nav-tabs nav-tabs-scroll">
                    {foreach $tabs_data as $tab_data}
                      <li class="{if isset($tab_data.class)}{$tab_data.class}{/if}{if isset($tab_data.active) && $tab_data.active} active {/if}" data-bs-toggle="tab" data-bs-target="#{$tab_data.id}"><a><span>{$tab_data.tab_title}</span></a></li>
                    {/foreach}
                  </ul>
                  <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
                    {foreach $tabs_data as $tab_data}
                    <div id="{$tab_data.id}" class="tab-pane {if isset($tab_data.active) && $tab_data.active} active{/if}">
                        {if isset($tab_data.class) && $tab_data.class == 'deprecated-tags'}
                            <div class="deprecated-text">{$smarty.const.DEPRECATED_TEXT}</div>
                        {/if}

                      {if $isMultiPlatform}
                      <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs tab-light-gray {if $isMultiPlatform && false}tab-radius-ul tab-radius-ul-white{/if}">
                          {foreach $platforms as $platform}
                          <li {if $first_platform_id==$platform['id']} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$tab_data.id}_{$platform['id']}"><a><span>{$platform['text']}</span></a></li>
                          {/foreach}
                        </ul>
                      {/if}

                        <div {if $isMultiPlatform}class="tab-content"{/if}>
                          {foreach $platforms as $platform}
                          <div id="{$tab_data.id}_{$platform['id']}" class="tab-pane {if $first_platform_id==$platform['id']}active{/if}">
                            <div class="tabbable tabbable-custom">
                                {if count($languages) > 1}
                              <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                              {foreach $languages as $lang_idx=>$lang}<li{if $lang_idx == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$tab_data.id}_{$platform['id']}_{$lang['id']}"><a>{$lang['logo']}<span>{$lang['name']}</span></a></li>{/foreach}
                              </ul>
                              {/if}
                              <div class="tab-content {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                                {foreach $languages as $lang_idx=>$lang}
                                  <div class="tab-pane{if $lang_idx == 0} active{/if}" id="{$tab_data.id}_{$platform['id']}_{$lang['id']}">

                                    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="h-teg-table">
                                        {if isset($tab_data.class) && $tab_data.class == 'add-keys'}
                                            <tr>
                                                <td class="bigText">&nbsp;</td>
                                                <td class="bigText"><a href="#keys-popup" class="btn add-key-btn" data-popup-option="{if isset($tab_data.popupOption)}{$tab_data.popupOption}{/if}">{$smarty.const.TEXT_TEMPLATES_KEYS_BUTTON}</a></td>
                                            </tr>
                                        {/if}
                                      {foreach $tab_data.input_controls[$lang['id']|cat:'_'|cat:$platform['id']] as $cp_inp}
                                        <tr>
                                          <td class="bigText">{$cp_inp.label}</td>
                                          <td class="bigText">{$cp_inp.control}</td>
                                        </tr>
                                      {/foreach}
                                    </table>


                                  </div>
                                {/foreach}
                              </div>
                            </div>
                          </div>
                          {/foreach}
                        </div>

                      {if $isMultiPlatform}
                      </div>
                      {/if}
                    </div>
                    {/foreach}
                  </div>

                </div>
                                                      {if $isMultiPlatform}  </div>{/if}
              <!--END TABS-->
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE}" onClick="return updateMetaTags()">
                                                                </p>
                                                                  <script type="text/javascript">
function updateMetaTags() {
  $.post("meta_tags/update", $('form[name=meta_tags]').serialize(), function(data, status){
    if (status == "success") {
      $('.widget-content').slideUp();
      $('.widget-content').slideDown();
    } else {
        alert("Request error.");
    }
},"html");
  return false;
}
function addInput (id, input) {
  $('#' + id).append(input);
}
$('body').on('click', '.del-pt.del-tag', function(){
  $(this).parent().remove();
});
                                                                  </script>
                </form>
							</div>


<div id="keys-popup" style="display: none">
    <div class="popup-heading cat-head">{$smarty.const.TEXT_TEMPLATES_KEYS}</div>
    <div class="pop-up-content">
        <div class="pageLinksWrapper">
            <select name="key" class="form-control js-popup-keys">
                <option value=""></option>
                <option value="##PRODUCT_NAME##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_NAME}</option>
                <option value="##PRODUCT_MODEL##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_MODEL}</option>
                <option value="##CATEGORY_NAME##"
                        data-category="1"
                        data-common="1">{$smarty.const.CATEGORY_NAME}</option>
                <option value="##BRAND_NAME##"
                        data-brand="1"
                        data-common="1">{$smarty.const.BRAND_NAME}</option>
                <option value="##BREADCRUMB##"
                        data-delivery-location="1"
                        data-category="1"
                        data-common="1">{$smarty.const.TEXT_BREADCRUMB}</option>
                <option value="##DOCUMENTS##"
                        data-common="1">{$smarty.const.TEXT_DOCUMENTS}</option>
                <option value="##PRODUCT_TITLE_TAG##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_TITLE_TAG}</option>
                <option value="##PRODUCT_DESCRIPTION_TAG##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_DESCRIPTION_TAG}</option>
                <option value="##PRODUCT_SHORT_DESCRIPTION_NNN##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_SHORT_DESCRIPTION_LIMITED}</option>
                <option value="##PRODUCT_DESCRIPTION_NNN##"
                        data-common="1">{$smarty.const.TEXT_PRODUCT_DESCRIPTION_LIMITED}</option>
                {foreach $popup_keys as $popup_key}
                    {Html::tag('option', $popup_key['content'], $popup_key['options'])}
                {/foreach}
            </select>
        </div>
        <div class="pageLinksButton">
            <button class="btn btn-no-margin btn-insert-key">{$smarty.const.IMAGE_INSERT}</button>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $('.btn-insert-key:visible').on('click', function(){
                var key = $('.pageLinksWrapper select:visible').val();
                var input = window.currentInput;
                var inputValue = window.currentValue;
                var cursorPosition = window.currentPosition;

                inputValue = inputValue ?? '';
                console.log(inputValue)
                input.val(inputValue.substring(0, cursorPosition) + key + inputValue.substring(cursorPosition));

                $('.popup-box-wrap').remove()
            })
        })
    </script>
</div>

<script type="text/javascript">
$(function(){
    window.currentInput = 0;
    window.currentValue = '';
    window.currentPosition = 0;

    $('form[name="meta_tags"] input, form[name="meta_tags"] textarea').on('focus keyup click', function(){
        window.currentInput = $(this);
        window.currentValue = $(this).val();
        window.currentPosition = this.selectionStart;
    });

    $('.add-key-btn')
        .popUp()
        .on('click', function(){
            var option = $(this).data('popup-option') || '';
            var $options = $('.js-popup-keys option[value!=""]').show();
            if ( option=='' ) {
                $options.not('[data-common]').hide();
            }else{
                $options.not('[data-'+option+']').hide();
            }
            if (window.currentInput && window.currentInput.is(':visible')) {

            }else {
                window.currentInput = $('.wtabplform input:visible:first');
                window.currentValue = window.currentInput.val();
            }
        })

    $('.head-desc-tag').limitValue('description')
    $('.head-title-tag').limitValue('title')
})
</script>