tl([
    createJsUrl('main.js'),
], function(){

    $('.w-subscribers-unsubscribe-form form').each(function(){
        var $form = $(this);

        $('input', $form).validate();

        $form.submit(function(e){
            e.preventDefault();

            if ($('.required-error', $form).length === 0) {
                $.post($form.attr('action'), $form.serializeArray(), function (response) {
                    alertMessage(response)
                })
            }
        })
    })

});