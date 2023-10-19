{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_FILTERS}
  </div>
  <div class="popup-content">



    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">





          <div class="setting-row">
            <label for="">{$smarty.const.ADD_FILTER_ITEM}</label>
              <input type="hidden" name="setting[0][filter_items]" class="filter-items-input" value="{$settings[0].filter_items}"/>

              <div class="filter-items"></div>
              <div class="filter-items-add"><span class="btn">{$smarty.const.IMAGE_ADD}</span></div>
          </div>


<script type="text/javascript">
  $(function () {
      $.get('{Yii::$app->urlManager->createUrl(['categories/filter-tab-list', 'cID' => $categories_id])}', function (d) {

          let data = JSON.parse(d).data;
          let text;
          let options = [];
          options[0] = '';

          data.forEach(function(val){
              let $intem = $('<div>' + val[0] + '</div>');
              let text = $('.module_title', $intem).text()
              let id = $('.cell_type', $intem).val() + '-' + $('.cell_identify', $intem).val()
              options[id] = text;
          });

          let filterItems = $('.filter-items-input').val().split(';');

          filterItems.forEach(function(val){
              $('.filter-items').append(dropdown(options, val))
          });

          $('.filter-items-add .btn').on('click', function(){
              $('.filter-items').append(dropdown(options, ''))
          })
      })
  })


    function dropdown (options, value){
        let content = '';
        for (let val in options) {
            let text = options[val];
            let selected = val === value ? ' selected' : '';
            content += `<option value="${ val}"${ selected}>${ text}</option>`
        }

        let $item = $(`<div class="filter-item"><select class="filter-item form-control">${ content}</select><span class="remove"></span></div>`);

        $item.on('change', changeFilterItemsInput);

        $('.remove', $item).on('click', function () {
            $item.remove();
            changeFilterItemsInput()
        })

        return $item
    }

    function changeFilterItemsInput (){
        let filterItemsInput = [];
        $('.filter-items select').each(function(){
            filterItemsInput.push($(this).val())
        })
        $('.filter-items-input').val(filterItemsInput.join(';')).trigger('change')
    }
</script>




        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>