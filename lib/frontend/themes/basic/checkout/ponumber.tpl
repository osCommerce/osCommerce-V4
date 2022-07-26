{use class="common\helpers\Html"}
<div>
      <div class="dholder">
          <span class="heading">{$smarty.const.TEXT_PO_NUMBER}</span>
          {Html::input('text', 'purchase_order', '', ['id' => 'purchase_order_all'])}
      </div>
  </div>

<script type="text/javascript">
  tl([
    ], function () {
        $('input#purchase_order').attr('disabled', 'disabled');
        $('input#purchase_order').attr('placeholder', '{$smarty.const.TEXT_ENTER_ABOVE|escape:'quotes'}');
        $('#purchase_order_all').on('change', function() {
          $('input#purchase_order').val($(this).val());
        });
        //$('input#purchase_order').parent().hide();
      })
</script>