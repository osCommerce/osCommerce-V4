{use class="yii\helpers\Html"}
<!--=== Page Content ===-->
<div id="texts_management_data">
<!--===Customers List ===-->
<form name="save_item_form" id="save_item_form" onSubmit="return saveItem();" method="post" action="{$app->urlManager->createUrl('texts/submit')}">
<div class="box-wrap">
    <div class="cedit-top redit-top after">
        <div>
            <div class="status-left" style="float: left;">
                <span>{$smarty.const.TABLE_HEADING_LANGUAGE_KEY}:</span>
                <input type="text" name="translation_key" value="" class="form-control" />
            </div>
        </div>
        <div>
            <div class="status-left" style="float: left;">
                <span>{$smarty.const.TABLE_HEADING_LANGUAGE_ENTITY}:</span>
                <div class="f_td_group f_td_group-pr">
                <input type="text" name="translation_entity" id="selectEntity" value="" class="form-control" />
                </div>
            </div>
        </div>
    </div>
            
    <div class="create-or-wrap after create-cus-wrap">
        <div class="widget box box-no-shadow" style="margin-bottom: 0;">
            <div class="widget-header widget-header-review">
                <h4>{$translation_key}</h4>
            </div>
            <div class="widget-content">
                {foreach $values as $value}
                <div class="wedit-rev after">
                    <label>{$value.flag}</label>
                    {Html::textarea($value.name, $value.text, ['class' => 'form-control', 'rows' => '10'])}
                </div>
                {/foreach}
            </div>
        </div>        
    </div>
</div>
<input type="hidden" name="to_main" value="1">
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
</div>
</form>
<script>
function saveItem() {
    /*$.post("{$app->urlManager->createUrl('texts/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#texts_management_data').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");*/

    return true;
}

function backStatement() {
    window.history.back();
    return false;
}
$(document).ready(function() {
   $('#selectEntity').autocomplete({
        source: "{Yii::$app->urlManager->createUrl('texts/entity-list')}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
});
</script>
</div>
<!-- /Page Content -->




