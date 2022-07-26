tl([
    createJsUrl('main.js'),
    createJsUrl('bootstrap-switch.js')
], function(){

    $('.w-subscribers-subscribe-form form').each(function(){
        var $form = $(this);

        $('input[name="privacy"]', $form).bootstrapSwitch({
            offText: entryData.tr.TEXT_NO,
            onText:  entryData.tr.TEXT_YES,
            onSwitchChange: function (element) {
                $(element.target).trigger('keyup')
            }
        });

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