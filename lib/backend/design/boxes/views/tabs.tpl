{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_TABS}
  </div>
  <div class="popup-content">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

          <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_TABS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="type">

{if !$oldTabs}

            <div class="setting-row tab-name" data-count="1">
              <label for="">{$smarty.const.TEXT_TAB_NAME} 1</label>
              <input type="text" name="setting[0][tab_1]" value="{$settings[0].tab_1}" class="form-control form-control-width"/>
            </div>
            <div class="setting-row tab-name" data-count="2">
              <label for="">{$smarty.const.TEXT_TAB_NAME} 2</label>
              <input type="text" name="setting[0][tab_2]" value="{$settings[0].tab_2}" class="form-control form-control-width"/>
            </div>

            <div class="translation-by-keys"></div>

          <div class="setting-row" style="text-align: right; padding-right: 153px">
            <span class="btn add-tab">Add tab</span>
          </div>


            {$addTabs = '['}
            {$count = 3}
            {while $settings[0]['tab_'|cat:$count]}
                {if $count > 3}
                    {$addTabs = $addTabs|cat:','}
                {/if}
                {$addTabs = $addTabs|cat:'{"value": "'|cat:$settings[0]['tab_'|cat:$count]|cat:'"}'}
                {$count = $count+1}
            {/while}
            {$addTabs = $addTabs|cat:']'}

<script>
    (function(){
        const tabNameText = '{$smarty.const.TEXT_TAB_NAME}';
        let addTabs = JSON.parse('{$addTabs}');

        const firsTab = 3;
        const tabNamesBlock = $('.translation-by-keys');

        $('.add-tab').on('click', function(){
            $('input', tabNamesBlock).each(function(i){
                addTabs[i].value = $(this).val()
            });
            addTabs.push({ 'value': ''});
            additionalTabs()
        });

        tabNamesBlock.on('click', '.btn-remove', function(){
            let row = $(this).closest('.tab-name');

            let count = row.data('count');
            $('input', tabNamesBlock).each(function(i){
                addTabs[i].value = $(this).val()
            });
            addTabs = addTabs.filter((current, i) => i+firsTab !== count);
            $('input:last', tabNamesBlock).val('').trigger('change');
            additionalTabs();
        });

        additionalTabs();

        function additionalTabs(){
            tabNamesBlock.html('');
            addTabs.forEach((current, i) => tabNamesBlock.append(tabRowTemplate({ ...current, count: i+firsTab})));
            $('input', tabNamesBlock).trigger('change')
        }

        function tabRowTemplate(props){
            if (!props) return false;
            if (!props.count) return false;
            if (!props.value) props.value = '';

            return `
            <div class="setting-row tab-name" data-count="${ props.count}">
                <label>${ tabNameText} ${ props.count}</label>
                <input
                    type="text"
                    name="setting[0][tab_${ props.count}]"
                    value="${ props.value}"
                    class="form-control form-control-width"/>
                <span class="btn-remove icon-trash"></span>
            </div>`
        }
    })()
</script>


{else}


          <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

              {foreach $languages as $language}
                <li{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a>{$language.logo} {$language.name}</a></li>
              {/foreach}

            </ul>
            <div class="tab-content">

              {foreach $languages as $language}
                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">

                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 1</label>
                    <input type="text" name="setting[{$language.id}][tab_1]" value="{$settings[$language.id].tab_1}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 2</label>
                    <input type="text" name="setting[{$language.id}][tab_2]" value="{$settings[$language.id].tab_2}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 3</label>
                    <input type="text" name="setting[{$language.id}][tab_3]" value="{$settings[$language.id].tab_3}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 4</label>
                    <input type="text" name="setting[{$language.id}][tab_4]" value="{$settings[$language.id].tab_4}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 5</label>
                    <input type="text" name="setting[{$language.id}][tab_5]" value="{$settings[$language.id].tab_5}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 6</label>
                    <input type="text" name="setting[{$language.id}][tab_6]" value="{$settings[$language.id].tab_6}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 7</label>
                    <input type="text" name="setting[{$language.id}][tab_7]" value="{$settings[$language.id].tab_7}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 8</label>
                    <input type="text" name="setting[{$language.id}][tab_8]" value="{$settings[$language.id].tab_8}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 9</label>
                    <input type="text" name="setting[{$language.id}][tab_9]" value="{$settings[$language.id].tab_9}" class="form-control form-control-width"/>
                  </div>
                  <div class="setting-row">
                    <label for="">{$smarty.const.TEXT_TAB_NAME} 10</label>
                    <input type="text" name="setting[{$language.id}][tab_10]" value="{$settings[$language.id].tab_10}" class="form-control form-control-width"/>
                  </div>

                </div>
              {/foreach}

            </div>
          </div>
{/if}


            {if $settings.media_query|default:array()|@count > 0}
              <div style="margin: 20px 0; overflow: hidden;">
                <h4>Accordion instead tabs</h4>
                  {foreach $settings.media_query as $item}
                    <p style="float: left; width: 33%"><label>
                        <input type="checkbox" name="visibility[0][{$item.id}][accordion]"{if $visibility[0][$item.id].accordion} checked{/if}/>
                            {$smarty.const.WINDOW_WIDTH}: {$item.title}
                      </label></p>
                  {/foreach}
              </div>
            {/if}


        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">Save</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>
<script type="text/javascript">
  $(function(){

    $('.block-type input:checked').parent().addClass('active');
    $('.block-type').on('click', function(){
      $('.block-type .active').removeClass('active');
      $('input:checked', this).parent().addClass('active')
    })
  });

</script>