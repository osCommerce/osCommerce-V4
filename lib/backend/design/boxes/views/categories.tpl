{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_CATEGORIES}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_SETTINGS}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#product"><a>{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_MAX_ITEMS}</label>
            <input type="text" name="setting[0][max_items]" class="form-control" value="{$settings[0].max_items}"/>
          </div>

          <div class="setting-row lazy-load">
            <label for="">Skip use product image for categories without own image</label>
            <select name="setting[0][skip_product_image]" id="" class="form-control">
              <option value=""{if $settings[0].skip_product_image == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings[0].skip_product_image == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row lazy-load">
            <label for="">Show images</label>
            <select name="setting[0][hide_images]" id="" class="form-control">
              <option value=""{if $settings[0].hide_images == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings[0].hide_images == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_VIEW_AS}</label>
            <select name="setting[0][view_as]" id="" class="form-control">
              <option value=""{if $settings[0].view_as == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
              <option value="carousel"{if $settings[0].view_as == 'carousel'} selected{/if}>{$smarty.const.TEXT_CAROUSEL}</option>
            </select>
          </div>

          {if $selectCategory}
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_CATEGORY}</label>

            <select name="setting[0][parent_category_id]" class="form-control categories-select">
              {foreach \common\helpers\Categories::get_category_tree(0, '', '', '', false, true) as $item}
                <option value="{$item.id}"{if $settings[0].parent_category_id == $item.id} selected{/if}>{$item.text}</option>
              {/foreach}
            </select>
          </div>
          {/if}

            {include 'include/lazy_load.tpl'}

            {include 'include/ajax.tpl'}

          {include 'include/col_in_row.tpl'}

        </div>
        <div class="tab-pane" id="product">
          {include 'include/listings-product.tpl'}
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

<script type="text/javascript">
  (function($) {
    $('.categories-select').multipleSelect({
      filter: true,
      place: '{$smarty.const.TEXT_SEARCH_ITEMS}',
      maxHeight: 300,
      selectAll: false,
      data: [
        {foreach \common\helpers\Categories::get_category_tree(0, '', '', '', false, true) as $item}
        {
          text: wrapCategory(`{$item.text}`),
          value: `{$item.id}`,
          {if $settings[0].parent_category_id == $item.id} selected: true{/if}
        },
        {/foreach}
      ],
      onFilter: function (t) {
        if (t) {
          $('.categories-select').addClass('searching')
        } else {
          $('.categories-select').removeClass('searching')
        }
      }
    });


    function wrapCategory(str) {
      let lastIndex = str.lastIndexOf("&nbsp;&nbsp;&gt;&nbsp;&nbsp;");

      if (lastIndex !== -1) {
        lastIndex = lastIndex + 28;
        const startCategory = str.substring(0, lastIndex);
        const endCategory = str.substring(lastIndex);

        return `<span class="in-category">${ startCategory}</span>${ endCategory}`.replaceAll('&nbsp;', '<span class="nbsp">&nbsp;</span>');
      }

      return str;
    }
  })(jQuery)
</script>
<style type="text/css">
  .categories-select .ms-drop {
    border-radius: 0;
    box-shadow: none;
    border-color: var(--border-color-midle);
    margin-top: 0;
  }
  .categories-select .ms-drop ul {
    height: 400px;
  }

  .categories-select .popup-box {
    width: 1000px;
  }
  .categories-select .in-category {
    font-size: 0;
  }
  .categories-select.searching ul .in-category {
    font-size: 9px;
  }
  .categories-select .nbsp {
    width: 6px;
    display: inline-block;
  }
  .categories-select.searching .nbsp {
    width: 1px;
  }
  .categories-select .ms-choice .in-category {
    display: none;
  }
  .categories-select .ms-drop ul>li.hide-radio.selected {
    color: var(--main-color);
    background: none;
  }
  .categories-select .ms-drop ul>li.hide-radio.selected + .selected {
    color: var(--main-color);
  }
  .categories-select .ms-drop ul>li:hover {
    background: var(--background-color-1);
  }
  .categories-select .ms-drop ul>li input:checked + span {
    color: var(--primary-color);
  }
</style>