{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
<div>
    {Html::a('Apply ', ['multi-cart/apply-cart', 'uid' => $uid],['class' => 'js-apply-cart'])}
    {Html::a('Delete ', ['multi-cart/delete-cart', 'uid' => $uid],['class' => 'js-delete-cart'])}
    {Html::a('Clear ', ['multi-cart/clear-cart', 'uid' => $uid],['class' => 'js-clear-cart'])}
</div>
<div class="cart_product_container">
  {$products}
</div>
<div class="cart_totals_container">
    {$totals}
</div>

{Html::beginForm([], 'post', [])}
   <input type="hidden" value="{$uid}" name="cart">
{Html::endForm()}

<script>
    tl('{Info::themeFile('/js/main.js')}', function () {

        $('body').on('click', '.js-delete-cart', function (e) {
            e.preventDefault();
            var _this = $(this);
            confirmMessage('{$smarty.const.DELETE_CART_TEXT}', function () {
                window.location = _this.attr('href')
            });
            return false;
        });

        $('body').on('click', '.js-clear-cart', function (e) {
            e.preventDefault();
            var _this = $(this);
            confirmMessage('{$smarty.const.CLEAR_CART_TEXT}', function () {
                window.location = _this.attr('href')
            });
            return false;
        });

        $('body').on('click', '.js-apply-cart', function(e){
            e.preventDefault();
            $.post($(this).attr('href'),
                {
                    _csrf : $('input[name=_csrf]').val(),
                },
                function(data){
                    if(data.success){
                        alertMessage(data.dialog);
                    }

                }, 'json');
            return false;
        });


    })
</script>

