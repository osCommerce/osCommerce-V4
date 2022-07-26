{use class="frontend\design\Info"}
<div class="compare_pagination">
  <span class="compare_select_items">Select 2-4 items to</span>
  <a class="compare_button popup" href="{Yii::$app->urlManager->createUrl('catalog/compare')}">{$smarty.const.BOX_HEADING_COMPARE_LIST}</a>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    var params = { compare: [] };
    $('.compare_button').popUp({
      box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCompare'><div class='pop-up-close'></div><div class='popup-heading compare-head'>{$smarty.const.BOX_HEADING_COMPARE_LIST}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
      data: params,
      beforeSend: function() {
        params.compare.splice(0, params.compare.length);
        $('input[name="compare[]"]').each(function(i, e) {
          if (e.checked) {
            params.compare.push(e.value);
          }
        })
      }
    })
  });
</script>