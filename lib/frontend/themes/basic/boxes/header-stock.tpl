{use class="frontend\design\Info"}{use class = "yii\helpers\Html"}
<div class="headerStock">
{Html::beginForm($url, 'post', [])}
<span>{$text}</span>
<input type="hidden" name="show_out_of_stock_update" value="1">
<input type="checkbox" name="show_out_of_stock" value="1" id="headerStock" class="check-on-off"{if $checked} checked=""{/if}>
{Html::endForm()}
</div>
<script type="text/javascript">
tl('{Info::themeFile('/js/bootstrap-switch.js')}', function(){
  {\frontend\design\Info::addBoxToCss('switch')}
  $('.check-on-off').bootstrapSwitch({
    onSwitchChange: function (element, arguments) {
      // switchStatement(element.target.value, arguments);
      this.form.submit();
      return true;
    },
    offText: '{$smarty.const.TEXT_NO}',
    onText: '{$smarty.const.TEXT_YES}'
  });
})
</script>