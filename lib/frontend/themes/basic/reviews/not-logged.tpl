{$id = rand()}
<div class="review-login login-{$id}">
{\frontend\design\boxes\login\Returning::widget(['params' => $params])}
    <div style="text-align: center">
        <a href="{Yii::$app->urlManager->createUrl(['account/create'])}" class="no-ajax">{$smarty.const.TEXT_CREATE_ACCOUNT_DEFENETLY}</a>
    </div>
</div>

<script>
    tl(function(){
        var box = $('.login-{$id}');
        box.off('click', 'button', login).on('click', 'button', login);
        box.off('submit', 'input', login).on('submit', 'input', login);

        function login(){
            var data = $('input', box).serializeArray();
            data.push({ name: 'reviews', value: '1'})
            $.post('{Yii::$app->urlManager->createUrl(['account/login', 'action' => 'process'])}', data, function(d){
                if (d === 'ok') {
                    $.get('{Yii::$app->urlManager->createUrl(['reviews/write'])}', { products_id: $('input[name="products_id"]').val()}, function(write){
                        $('.product-reviews').html(write)
                    })
                    $(window).trigger('logged-in')
                } else {
                    box.html(d)
                }
            });
            return false;
        }
    })
</script>