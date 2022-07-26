{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<span style="position:relative;">
    <div class="customer-assign-box" {if $hide}style="display:none"{/if}>
		<div class="customer-assign-box-close"></div>
        <div class="up-box">
            <label>Select from existing customers</label>
            <div class="search-fields">
                <label>Email/Name/Phone<span class="required">*</span></label>
            </div>
            <div class="search-box auto-wrapp">
                {Html::textInput('search_customer', '', ['placeholder' => 'Type to find customer', 'class' => 'form-control'])}
                
                {Html::button('Choose', ['class' => 'btn btn-primary btn-choose-customer', 'disabled' => 'disabled'])}
            </div>
        </div>
        <div class="down-box">
            <div>
                <label>or, if you'd like</label>
            </div>
            {Html::a('Add new customer', Url::to($queryParams), ['class' => 'btn btn-primary add-customer popup', 'data-class' => 'add-customer-box'])}            
        </div>
    </div>    
</span>
<script>
    (function($){
        $('input[name=search_customer]').autocomplete({
			create: function(){
				$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )	
                        .append( "<a><span>" + item.label + "</span></a>")                        
						.appendTo( ul );
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
        })
        
        $('.btn-choose-customer').click(function(){
            let id = parseInt($(this).data('id'));
            if (id > 0 ){
                order.reassignCustomer(id);
            }
        })
    })(jQuery)
</script>