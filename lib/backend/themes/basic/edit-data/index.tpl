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

    <div class="tabbable-custom">
    {if count($platforms) > 1}
        <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
            {foreach $platforms as $platform}
                <li{if $platformId && $platformId == $platform['id'] || !$platformId && $platform@index == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}">
                    <a>
                        <span>{$platform['text']}</span>
                    </a>
                </li>
            {/foreach}
        </ul>
        <div class="tab-content">
    {/if}
    {foreach $platforms as $platform}
        <div class="tab-pane topTabPane tabbable-custom{if $platformId && $platformId == $platform['id'] || !$platformId && $platform@index == 0} active{/if}" id="tab_{$platform['id']}">




            {if count($languages) > 1}
            <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
                {foreach $languages as $language}
                    <li{if $languageId && $languageId == $language['id'] || !$languageId && $language@index == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}_{$language['id']}">
                        <a>
                            {$language['logo']}<span>{$language['name']}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {/if}
                {foreach $languages as $language}
                    <div class="tab-pane topTabPane tabbable-custom{if $languageId && $languageId == $language['id'] || !$languageId && $language@index == 0} active{/if}" id="tab_{$platform['id']}_{$language['id']}">


                        {if $input}
                            <div class="" style="min-height: 200px; padding-top: 20px">
                                <input type="text"  name="field[{$platform['id']}][{$language['id']}]" value="{$fields[$platform['id']][$language['id']]}" class="form-control" style="width: 100%"/>
                            </div>
                        {else}
                            <div class="" style="min-height: 500px">
                                <textarea name="field[{$platform['id']}][{$language['id']}]" cols="30" rows="10" id="ckeditor-{$platform.id}-{$language.id}">{$fields[$platform['id']][$language['id']]}</textarea>
                            </div>
                        {/if}


                    </div>
                {/foreach}
                {if count($languages) > 1}
            </div>
            {/if}





        </div>
    {/foreach}
    {if count($platforms) > 1}
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
{if $ckEditor}
    <script>
        $(function(){


            {foreach $platforms as $platform}
            {foreach $languages as $language}
            var text_{$platform.id}_{$language.id} = $('#ckeditor-{$platform.id}-{$language.id}');
            CKEDITOR.replace( 'ckeditor-{$platform.id}-{$language.id}', {
                on: {
                    change: function( evt ) {
                        for ( instance in CKEDITOR.instances ) {
                            CKEDITOR.instances[instance].updateElement();
                        }
                        text_{$platform.id}_{$language.id}.trigger('change')
                    }
                },
                toolbarGroups: [{
                    "name": "basicstyles",
                    "groups": ["basicstyles"]
                },
                    {
                        "name": "links",
                        "groups": ["links"]
                    },
                    {
                        "name": "paragraph",
                        "groups": ["list", "blocks"]
                    },
                    {
                        "name": "document",
                        "groups": ["mode"]
                    },
                    {
                        "name": "insert",
                        "groups": ["insert"]
                    },
                    {
                        "name": "styles",
                        "groups": ["styles"]
                    },
                    {
                        "name": "about",
                        "groups": ["about"]
                    }
                ],
                // Remove the redundant buttons from toolbar groups defined above.
                removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
            } );
            {/foreach}
            {/foreach}


        });
        $('#edit-data-form').on('submit', function(){
            if (typeof(CKEDITOR) == 'object'){
                for ( instance in CKEDITOR.instances ) {
                    CKEDITOR.instances[instance].updateElement();
                }
            }
        })
    </script>
{/if}