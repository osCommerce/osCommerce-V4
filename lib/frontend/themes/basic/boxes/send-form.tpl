{use class = "yii\helpers\Html"}
{Html::beginForm($formAction, 'post', ['class' => 'send-form'])}

    <div class="info" style="display: none"></div>

    {$form}

    <input type="hidden" name="id" value="{$id}">
    <input type="hidden" name="send_form" value="1">
{Html::endForm()}

<div class="success" style="display: none">
    {$success}

    <div class="buttons">
        <div class="right-buttons"><span class="btn send-more">{$smarty.const.SEND_ONE_MORE_MESSAGE}</span></div>
    </div>
</div>

<script type="text/javascript">
    tl(function(){
        var box = $('#box-{$id}');

        var sendForm = $('.send-form', box);
        var success = $('.success', box);
        var info = $('.info', box);
        var sendMore = $('.send-more', box);

        sendForm.on('submit', function(){
            info.html('').hide();

            $.post(sendForm.attr('action'), sendForm.serializeArray(), function(data){
                if (data === 'success') {
                    sendForm.slideUp(300);
                    success.slideDown(300)
                } else {
                    info.html(data).slideDown(300)
                }
            });

            return false;
        });

        sendMore.on('click', function(){
            sendForm.slideDown(300);
            success.slideUp(300)
        })
    })
</script>