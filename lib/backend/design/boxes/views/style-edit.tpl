{use class="Yii"}
<div class="pop-up-close"></div>
<form action="{Yii::getAlias('@web')}/design/style-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">{$smarty.const.TEXT_EDIT_STYLES}</div>
  <div class="popup-content">




          {include 'include/style.tpl'}





  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>

    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    <script type="text/javascript">
      $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap, .popup-draggable').remove();
        $('#dynamic-style').remove()
      })
    </script>
    {if $settings.data_class}<input type="hidden" name="data_class" value="{$settings.data_class}"/>{/if}
  </div>
</form>
<script type="text/javascript">


  /*$('#box-save').on('submit', function(){
    var values = $(this).serializeArray();
    values = values.concat(
      $('input[type=checkbox]:not(:checked)', this).map(function() {
        return { "name": this.name, "value": 0}
      }).get()
    );
    values = values.concat(
            $('.visibility input[disabled]', this).map(function() {
              return { "name": this.name, "value": 1}
            }).get()
    );

    var data = values.reduce(function(obj, item) {
      obj[item.name] = item.value;
      return obj;
    }, { });

    $.post('design/style-save', { 'values': JSON.stringify(data)}, function(){
      $('.info-view.active').editTheme({
        theme_name: '{$settings.theme_name}'
      }).removeClass('active')
    });

    $('.popup-draggable').remove();
    setTimeout(function(){
      $(window).trigger('reload-frame')
    }, 300);
    return false
  })*/
</script>