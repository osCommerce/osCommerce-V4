<div class="subscribe_box">
  <div class="left_box">
    <div class="stitle">{$smarty.const.NEWSLETTER_BOX1}</div>
    <div class="heading-3">{$smarty.const.NEWSLETTER_BOX2}</div>
    <p>{$smarty.const.NEWSLETTER_BOX3}</p>
  </div>
  <div class="right_box">
      <div class="sb_ta">
        <div class="sb_tc">
          <input type="email" name="subscribers_email_address" class="subscribers-email-address" data-pattern="email">
        </div>
        <div class="sb_tc">
          <span class="btn-submit">Submit</span>
        </div>
      </div>
  </div>
</div>

<script type="text/javascript">
  tl(function(){
    let $box = $('#box-{$id}');

    $('.btn-submit', $box).on('click', function(){
      let emailAddress = $('.subscribers-email-address', $box).val();

      if (!isValidEmailAddress(emailAddress)) {
        alertMessage('{$smarty.const.EMAIL_REQUIRED}');
        return false;
      }

      window.location.href = '{Yii::$app->urlManager->createAbsoluteUrl('subscribers')}?subscribers_email_address=' + emailAddress;
    })

    {literal}
    function isValidEmailAddress(emailAddress) {
      var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.) {2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
      return pattern.test(emailAddress);
    }
    {/literal}
  })

</script>