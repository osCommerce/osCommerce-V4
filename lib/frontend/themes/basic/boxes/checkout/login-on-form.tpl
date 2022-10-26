<div class="closeable-box">
    <div class="closeable-heading closeable-event closed">
        <div class="heading-2">{$smarty.const.ALREADY_HAVE_AN_ACCOUNT}</div>
    </div>
    <div class="closeable-content">

        {\frontend\design\boxes\login\Returning::widget(['params' => $params])}

    </div>
</div>



<script>
    tl(function(){
        applyForm();
        function applyForm(){
            var $loginForm = $('.w-checkout-login-on-form .login-box');
            var $btnOld = $('.btn-2', $loginForm);
            var $btn = $('<span class="btn-2">' + $btnOld.text() + '</span>', $loginForm);
            $btnOld.after($btn);
            $btnOld.remove();

            $loginForm.keydown( function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    $btn.trigger('click');
                    return false;
                }
            });

            $btn.on('click', function(){
                var request_details = $('input, select', $loginForm).serializeArray();
//                request_details.push({ name:'_csrf', value: $('meta[name="csrf-token"]').attr('content')});
                $.ajax({
                    type: "POST",
                    url: '{$params.action}',
                    data: request_details,
                    complete: function(jqXHR, textStatus) {
                        if (jqXHR.status > 300) { //textStatus error
                            document.location.reload();
                        }
                    },
                    success: function(data, textStatus) {
                        if ($(data).hasClass('login-box')) {
                            $loginForm.replaceWith(data);
                            applyForm();
                        } else {
                            document.location.reload();
                        }
                    }
                });

/*no success on 302                $.post('{$params.action}', request_details, function(data){
                    if ($(data).hasClass('login-box')) {
                        $loginForm.replaceWith(data);
                        applyForm();
                    } else {
                        document.location.reload();
                    }
                });*/
            });
        }
    });
</script>