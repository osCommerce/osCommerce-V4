;(function(){

    let tradeFormDelay = setInterval(function(){
        if (typeof jQuery === "function"){
            clearInterval(tradeFormDelay);

            let allScripts = ['postcode.decorator.js', 'jquery-ui.min.js', 'yii-cookie/js.cookie.js']
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

        $('input[data-type="postcode"], input[data-type="addressbook_postcode"]').each(function(){
            var $postcode = $(this);
            var groupId = $postcode.data('group-id');

            var $state = $('input[data-type="state"][data-group-id="'+groupId+'"], input[data-type="addressbook_state"][data-group-id="'+groupId+'"]');
            var $city = $('input[data-type="city"][data-group-id="'+groupId+'"], input[data-type="addressbook_city"][data-group-id="'+groupId+'"]');
            var $company = $('input[data-type="company"][data-group-id="'+groupId+'"], input[data-type="addressbook_company"][data-group-id="'+groupId+'"]');
            var $suburb = $('input[data-type="suburb"][data-group-id="'+groupId+'"], input[data-type="addressbook_suburb"][data-group-id="'+groupId+'"]');
            var $streetAddress = $('input[data-type="street_address"][data-group-id="'+groupId+'"], input[data-type="addressbook_street_address"][data-group-id="'+groupId+'"]');

            /*$state.closest('.box').hide();
            $city.closest('.box').hide();
            $company.closest('.box').hide();
            $suburb.closest('.box').hide();
            $streetAddress.closest('.box').hide();*/

            var $manually = $('<div class="manually-link">' + entryData.tr.ENTER_YOUR_ADDRESS_MANUALLY + '</div>');
            /*$postcode.after($manually);*/
            var $btn = $('<span class="btn">' + entryData.tr.TEXT_FIND_ADDRESS + '</span>');
            /*$postcode.after($btn);*/

            $manually.on('click', function(){
                $manually.hide();
                $state.closest('.box').show();
                $city.closest('.box').show();
                $company.closest('.box').show();
                $suburb.closest('.box').show();
                $streetAddress.closest('.box').show();
            })

            var decorator = new Decorator();
            decorator.setInlineBuildType();
            decorator.setControlFields([$postcode]);
            decorator.setControlFunction(function(target){


                //$btn.on('click', function(){
                    $(target).autocomplete({
                        create: function( event, ui ) {
                            $(event.target).autocomplete( "option", "appendTo", $(event.target).closest('div') );
                        },
                        source: function (request, response) {
                            var status = $(target).autocomplete( "option", "manual");
                            if (!status){
                                var countryIso = 'GB';
                                var country = $('select[data-type="country_id"]');
                                if (country.is('select')) {
                                    var iso = country.data('iso');
                                    try {
                                        if (Object.keys(iso).length) {
                                            if (iso.hasOwnProperty(country.val())) {
                                                countryIso = iso[country.val()];
                                            }
                                        }
                                    } catch (error) {
                                        console.error('Invalid iso list');
                                    }
                                }
                                $.getJSON("https://ws.postcoder.com/pcw/" + entryData.tradeForm.pcaKey + "/address/" + countryIso + "/" + escape(request.term), {}, function (data) {
                                    if (Array.isArray(data)) {
                                        values = [];
                                        $.each(data, function (i, item) {
                                            values.push({ 'label': item.summaryline, 'value': item });
                                        });
                                        return response(values);
                                    }
                                });
                            }
                        },
                        minLength: 2,
                        autoFocus: true,
                        delay: 500,
                        select: function (event, ui) {
                            if (ui.hasOwnProperty('item')) {

                                $manually.hide();
                                $state.closest('.box').show();
                                $city.closest('.box').show();
                                $company.closest('.box').show();
                                $suburb.closest('.box').show();
                                $streetAddress.closest('.box').show();

                                if (ui.item.value.hasOwnProperty('postcode')) {
                                    $postcode.val(ui.item.value.postcode);
                                }
                                if (ui.item.value.hasOwnProperty('county')) {
                                    $state.val(ui.item.value.county);
                                }
                                if (ui.item.value.hasOwnProperty('posttown')) {
                                    $city.val(ui.item.value.posttown);
                                }
                                if (ui.item.value.hasOwnProperty('organisation')) {
                                    $company.val(ui.item.value.organisation);
                                }

                                var street = [];
                                if (ui.item.value.hasOwnProperty('organisation')) {
                                    street.push(ui.item.value.organisation);
                                }
                                if (ui.item.value.hasOwnProperty('premise')) {
                                    street.push(ui.item.value.premise);
                                }
                                if (ui.item.value.hasOwnProperty('street')) {
                                    street.push(ui.item.value.street);
                                }
                                if (ui.item.value.hasOwnProperty('number')) {
                                    street.push(ui.item.value.number);
                                }
                                if (ui.item.value.hasOwnProperty('dependentlocality')) {
                                    if ($suburb.is('input')) {
                                        $suburb.val(ui.item.value.dependentlocality);
                                    } else {
                                        street.push(ui.item.value.dependentlocality);
                                    }
                                }
                                $streetAddress.val(street.join(', '));

                                decorator.done();
                            }
                            return false;
                        }
                    });
                    $(target).on('keyup keypress keydown', function(){
                        $(target).autocomplete( "option", "manual", true );
                        $(target).autocomplete( "close" );
                        $(target).autocomplete( "option", "manual", false );
                        $(target).autocomplete( "search" );
                    });

                    $(target).autocomplete( "option", "manual", false );
                $(target).autocomplete( "close" );
                })
            //});
        });

    }
})();
