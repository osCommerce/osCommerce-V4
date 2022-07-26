;(function(){
    let tradeFormDelay = setInterval(function(){
        if (typeof jQuery === "function"){
            clearInterval(tradeFormDelay);

            let allScripts = ['bootstrap.min.js', 'main.js', 'bootstrap-datetimepicker.min.js']
                .map((url) => new Promise((resolve, reject) => {
                    $.ajax({
                        url: createJsUrl(url),
                        success: resolve,
                        error: reject,
                        dataType: 'script',
                        cache: true
                    });
                }))

            Promise.all(allScripts).then(tradeForm)
        }
    }, 100);


    function tradeForm() {
        var $save = $('.btn-save-tf');
        var $continue = $('.btn-continue-tf');

        $('*[data-required]').validate();

        $save.on('click', function(){
            $.ajax({
                url: getMainUrl() + "/account/trade-form-submit",
                type: 'POST',
                data: new FormData($('#customers_edit').get(0)),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (data, status){
                    alertMessage(data.error ? data.error : 'Saved');
                    setTimeout(function(){ $('.popup-box-wrap:last').remove() }, 1000)
                },
                error: function (xhr, desc, err){
                    alertMessage("Request error.");
                }
            });
        });

        $continue.on('click', function(){

            $('*[data-required]').trigger('check');
            if ($('.required-error').length) {
                $('#customers_edit').trigger('submit')
                return;
            }

            var href = $(this).data('href');

            $.ajax({
                url: getMainUrl() + "/account/trade-form-submit",
                type: 'POST',
                data: new FormData($('#customers_edit').get(0)),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (data, status){
                    if (data.error) {
                        console.log('error2');
                        alertMessage(data.error)

                    } else {
                        //window.location.href = getMainUrl() + '/' + href;
                         if($continue.attr('target')){
                             window.open(getMainUrl() + '/' + href, '_blank');
                         }
                         else{
                             window.location.href = getMainUrl() + '/' + href;
                         }
                    }
                },
                error: function (xhr, desc, err){
                    console.log('error1');
                    alertMessage("Request error.");
                }
            });
        });



        $('.radio-other-value').each(function(){
            var $label = $(this);
            $('input[type="text"]', $label).on('change keyup focus', function(){
                $('input[type="radio"]', $label).prop('checked', true).attr('value', $(this).val())
            })
        });


        $('.multifield-box').each(function(){
            var $box = $(this);

            var firstID = $('input[type="text"]:first', $box).data('field-id');

            var $template = $('.multifield-box-item', $box).clone();
            $('*[id]', $template).removeAttr('id');
            $('input', $template).val('').removeAttr('data-required');
            $template.append('<span class="multifield-box-item-remove"></span>')


            if (entryData.multifields &&
                entryData.multifields[firstID] &&
                entryData.multifields[firstID].length > 1
            ) {
                for (let i = 1; i < entryData.multifields[firstID].length; i++) {
                    let $item = $template.clone();
                    $('input', $item).each(function(){
                        var id = $(this).data('field-id');
                        if (entryData.multifields[id] && entryData.multifields[id][i]) {
                            $(this).val(entryData.multifields[id][i])
                        }
                    });
                    $('.multifield-box-item:last', $box).after($item)
                }
            }
            $('.btn-add-multifield', $box).on('click', function(){
                $('.multifield-box-item:last', $box).after($template.clone())
            });

            $box.on('click', '.multifield-box-item-remove', function () {
                $(this).parent().remove()
            })
        })

        $('.payment-method-tf input')
            .each(switchDebitInformation)
            .on('change', switchDebitInformation);
        function switchDebitInformation(){
            if ($('.payment-method-tf input:checked').val() === '0') {
                $('.debit-information').show()
            } else {
                $('.debit-information').hide()
            }
        }

        var checkedInput = $('.payment-method-tf input:checked').val();
        $('.payment-method-tf input').on('click', function(e){
            if ($(this).val() == checkedInput) {
                $(this).prop('checked', false);
                checkedInput = -1;
            } else {
                checkedInput = $(this).val();
            }
        });

        var currentDate = $('input[data-type="current_date"]');
        if (currentDate.length > 0) {
            $('head').append('<link rel="stylesheet" href="' + createJsUrl('bootstrap.css') + '">');
            $('head').append('<link rel="stylesheet" href="' + createJsUrl('bootstrap-datetimepicker.min.css') + '">');
            currentDate.datetimepicker();
        }

        (function(){
            var selectName = $('.vat-area select').attr('name');
            var inputName = $('.vat-area input').attr('name');
            $('.is-vat input').on('change', vatArea);
            vatArea();
            function vatArea(){
                if ($('.is-vat input').prop('checked')){
                    $('.vat-area').css({opacity: ''})
                    $('.vat-area select')
                        .attr('name', selectName)
                        .removeAttr('disabled');
                    $('.vat-area input')
                        .attr('name', inputName)
                        .removeAttr('disabled');
                } else {
                    $('.vat-area').css({opacity: '0.7'});
                    $('.vat-area select')
                        .removeAttr('name')
                        .attr('disabled', 'disabled');
                    $('.vat-area input')
                        .removeAttr('name')
                        .attr('disabled', 'disabled');
                }
            }
        })();

        (function(){
            $('.vat-area select').on('change', function(){
                let val = $(this).val();
                if (val == 'IE') {
                    $('select[name="field[138]"]').val(1)
                }
                if (val == 'GB' || val == 'XI') {
                    $('select[name="field[138]"]').val(2)
                }
            })
        })();


        (function(){
            $('.name-date-dields input').on('keyup change', dateTimeApply)
            dateTimeApply();
            function dateTimeApply(){
                var fielded = true;
                $('.name-date-dields input').each(function(){
                    if (!$(this).val()) {
                        fielded = false;
                    }
                })
                if (fielded) {
                    $('.hide-apply-button').remove();
                } else {
                    $('.apply-button').css('position', 'relative');
                    $('.apply-button').append($('<div/>', {'class': 'hide-apply-button'}).css({
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        width: '100%',
                        height: '100%',
                        background: '#fff',
                        opacity: '0.7'
                    }))
                }
            }
        })();

        (function(){
            var fielded = true;
            $('.name-date-dields input').each(function(){
                if (!$(this).val()) {
                    fielded = false;
                }
            });
            if (fielded) {
                $('.hide-download-pdf-btn').remove();
            } else {
                $('.download-pdf-btn').css('position', 'relative');
                $('.download-pdf-btn').append($('<div/>', {'class': 'hide-download-pdf-btn'}).css({
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '100%',
                    background: '#fff',
                    opacity: '0.7'
                }))
            }
            $('.apply-button').on('click', function(){
                setTimeout(function(){
                    $('.hide-download-pdf-btn').remove();
                }, 300)
            })
        })();

        (function(){
            var val = $('select[name="field[138]"]').val();
            $('select[name="field[138]"]').on('change', function(){
                if ($(this).val() == 0) {
                    $(this).val(val)
                }
                val = $(this).val();
            })
        })();

    }

})();