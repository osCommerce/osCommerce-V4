<style type="text/css">
    html, body {
        min-width: 0;
    }
    textarea {
        width: 100%;
        text-align: left;
    }
</style>
{use class = "yii\helpers\Html"}
{Html::beginForm($action, 'post', ['id' => 'edit-data-form'], false)}
    <input type="hidden" name="field" value="{$fieldName}"/>

{if $brandField}
    <div class="" style="padding: 0 0 20px">
        <label for="">{$linkTypeText}</label>
        <input type="text"  name="brand_field" value="{$brandField}" class="form-control" style="width: 100%"/>
    </div>
{/if}

    <div class="tabbable-custom">


            {if count($languages) > 1}
            <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
                {foreach $languages as $language}
                    <li{if $languageId && $languageId == $language['id'] || !$languageId && $language@index == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$language['id']}">
                        <a>
                            {$language['logo']}<span>{$language['name']}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {/if}
                {foreach $languages as $language}
                    <div class="tab-pane topTabPane tabbable-custom{if $languageId && $languageId == $language['id'] || !$languageId && $language@index == 0} active{/if}" id="tab_{$language['id']}">


                        {if !$hideField}
                            <div class="" style="padding: 20px 0">
                                <label for="">{$linkTypeText}</label>
                                <input type="text"  name="field[{$language['id']}]" value="{$fields[$language['id']]}" class="form-control" style="width: 100%"/>
                            </div>
                        {/if}
                        <div class="" style="padding: 20px 0">
                            <label for="">Name in menu</label>
                            <input type="text"  name="menu_item[{$language['id']}]" value="{$menuItems[$language['id']]}" class="form-control" style="width: 100%"/>
                        </div>


                    </div>
                {/foreach}
                {if count($languages) > 1}
            </div>
            {/if}


    </div>


    <div class="buttons-box">
        <div class="buttons-left">
            <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
        </div>
        <div class="buttons-right">
            <button type="submit" class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
{Html::endForm()}