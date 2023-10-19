{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_BLOCK}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="type">

                    <div class="setting-row">
                        <label for="">Use description if has't short description</label>
                        <select name="setting[0][just_short]" id="" class="form-control">
                            <option value=""{if $settings[0].just_short == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].just_short == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>

                    <div class="setting-row use-tags">
                        <label for="">Remove html tags from description</label>
                        <select name="setting[0][use_tags]" id="" class="form-control">
                            <option value=""{if $settings[0].use_tags == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].use_tags == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>

                    <div class="setting-row cut-description">
                        <label for="">Cut description if it is too long</label>
                        <select name="setting[0][full_description]" id="" class="form-control">
                            <option value=""{if $settings[0].full_description == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].full_description == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>

                    <div class="setting-row description-characters">
                        <label for="">Cut description after characters</label>
                        <input type="text" name="setting[0][description_characters]" value="{$settings[0].description_characters}" class="form-control"/>
                    </div>

                    <script type="text/javascript">
                        $(function(){
                            cutDescription();
                            useTags()
                            $('.use-tags select').change(useTags)
                            $('.cut-description select').change(cutDescription)

                            function useTags() {
                                if ($('.use-tags select').val() === '1'){
                                    $('.cut-description').hide();
                                    cutDescription()
                                } else {
                                    $('.cut-description').show();
                                    cutDescription()
                                }
                            }

                            function cutDescription(){
                                var cutDescriptionVal = $('.cut-description select').val();
                                var useTagsVal = $('.use-tags select').val();
                                if (cutDescriptionVal === '' && useTagsVal === '') {
                                    $('.description-characters').show()
                                } else {
                                    $('.description-characters').hide()
                                }
                            }
                        })
                    </script>

                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

                </div>
                <div class="tab-pane" id="visibility">
                    {include '../include/visibility.tpl'}
                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>