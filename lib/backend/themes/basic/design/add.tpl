
<div class="popup-heading">{$smarty.const.TEXT_ADD_COMMENTS}</div>
<div class="popup-content pop-mess-cont">
  <textarea class="comments" name="" id="" rows="10" style="width: 100%"></textarea>
</div>
<div class="noti-btn">
  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
  <div><span class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</span></div>
</div>

<script type="text/javascript">
  (function($){
    $('.btn-save').on('click', function(){
      var $comment = $('.comments').val();
      $('.popup-content').html('<div class="preloader"></div>');
      $.post("{Yii::$app->urlManager->createUrl('design/backup-submit')}", { 'theme_name' : '{$theme_name}', 'comments': $comment }, function(data, status){
        $('.popup-box-wrap').remove();
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      })
    })
  })(jQuery);
</script>