{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<span type="button" class="btn btn-reassing">{$smarty.const.TEXT_REASSING}</span>



<script>
    (function($){
        $('.btn-reassing').on('click', function () {
            const $popUp = alertMessage(`
                <div class="customer-assign-box popup-content">
                    <label for="">{$smarty.const.SELECT_EXISTING_CUSTOMERS}</label>
                    <div class="up-box">
                        <div class="search-fields">
                            <label>{$smarty.const.TEXT_SEARCH_CUSTOMER}<span class="required">*</span></label>
                        </div>
                        <div class="search-box auto-wrapp">
                            {Html::textInput('search_customer', '', ['placeholder' => $smarty.const.TYPE_FIND_CUSTOMER, 'class' => 'form-control'])}

                            {Html::button($smarty.const.TEXT_CHOOSE, ['class' => 'btn btn-primary btn-choose-customer', 'disabled' => 'disabled'])}
                        </div>
                    </div>
                    <div class="down-box">
                        <div>
                            <label>{$smarty.const.OR_IF_YOUD_LIKE}</label>
                        </div>
                        {Html::a($smarty.const.TEXT_ADD_NEW_CUSTOMER, Url::to($queryParams), ['class' => 'btn btn-primary add-customer', 'data-class' => 'add-customer-box'])}
                    </div>
                </div>
            `);

            $('input[name=search_customer]', $popUp).autocomplete({
                create: function(){
                    $(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
                        var el = $( "<li></li>" )
                            .data( "item.autocomplete", item );
                        if (item.id) {
                            el.append( "<a><span>" + item.label + "</span></a>");
                        } else {
                            el.append( "<span>" + item.label + "</span>");
                        }
                        return el.appendTo( ul );
                    };
                },
                source: function(request, response){
                    if (request.term.length > 2){

                        $.post($urlCheckout, {
                            'action':'search_customer',
                            'search': request.term,
                        }, function(data){
                            response($.map(data, function(item, i) {
                                return {
                                        values: item.text,
                                        label: item.text,
                                        id: parseInt(item.id),
                                    };
                                }));
                        }, 'json');
                    } else {
                        $('.btn-choose-customer').attr('disabled', 'disabled');
                    }
                },
                minLength: 2,
                autoFocus: true,
                delay: 0,
                appendTo: '.auto-wrapp',
                select: function(event, ui) {
                    if (ui.item.id > 0){
                        $('.btn-choose-customer').attr('disabled', false);
                        $('.btn-choose-customer').data('id', ui.item.id);
                    }
                },
            });

            $('.add-customer', $popUp).popUp();
        
            $('.btn-choose-customer', $popUp).click(function(){
                let id = parseInt($(this).data('id'));
                if (id > 0 ){
                    order.reassignCustomer(id);
                }
            });

        });
    })(jQuery)
</script>