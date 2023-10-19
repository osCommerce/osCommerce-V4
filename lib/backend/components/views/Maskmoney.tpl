{if $js_mask_type == 'maskMoney'}
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.maskMoney.js"></script>
{/if}
{if $js_mask_type == 'accounting'}
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/accounting.min.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.maskedinput.js"></script>
{/if}
<script type="text/javascript">
        var currency_id = {$currencies_id['currencies_id']};
        var curr_hex = [];
        {$response}
	{if $js_mask_type == 'maskMoney'}		
		$.fn.setMaskMoney = function(options){
            var _options = $.extend({
                currency_id: currency_id,
            }, options);
			return this.each(function() {
				var _this = $(this);
				_this.attr('placeholder', curr_hex[_options.currency_id].symbol_left + '0' + curr_hex[_options.currency_id].decimal_point + '00' + curr_hex[_options.currency_id].symbol_right);
				_this.maskMoney({
					prefix: curr_hex[_options.currency_id].symbol_left,
					suffix: curr_hex[_options.currency_id].symbol_right,
					decimal: curr_hex[_options.currency_id].decimal_point,
					thousands: curr_hex[_options.currency_id].thousands_point,
					precision: curr_hex[_options.currency_id].decimal_places
				});
				_this.each(function () { 
					if (_this.val()!=''){
						_this.trigger('mask');
					}
				});
			});
		}
	{/if}	
	{if $js_mask_type == 'accounting'}
		$.fn.setMaskMoney = function(options){
            var _options = $.extend({
                currency_id: currency_id,
            }, options);
			return this.each(function() {
				var _this = $(this);
                                var currency = _this.data('currency');
                                if (typeof(currency) == 'undefined') currency = _options.currency_id;
                                var precision = _this.data('precision');
                                if (typeof(precision) == 'undefined') precision = curr_hex[currency].decimal_places;
				_this.on('blur', function() {
                                        var symbol, pos;
                                        if (curr_hex[currency].symbol_left == '') {
                                            symbol = curr_hex[currency].symbol_right;
                                            pos = "%v %s";
                                        } else {
                                            symbol = curr_hex[currency].symbol_left;
                                            pos = "%s%v";
                                        }
                                        var result =accounting.formatMoney($(this).val(), {
                                                symbol: symbol,
                                                precision: precision,
                                                thousand: curr_hex[currency].thousands_point,
                                                decimal: curr_hex[currency].decimal_point,
                                                format: {
                                                        pos : pos,
                                                        neg : "-"+pos,
                                                        zero: pos
                                                }
                                        });
					//if (result == curr_hex[currency].symbol_left + '0.00') {
						//result = '';
					//}
					$(this).val(result);
				}).blur();

				_this.on('focus', function(){
					var result = $(this).val();
                                        if (result != '.' && result != '0' && result != '0.' && result != '0.0') {
                                            result = accounting.unformat(result, curr_hex[currency].decimal_point);
                                            if (result == 0) {
                                                    result = '';
                                            }
                                            $(this).val(result);
                                        }
				});

				_this.on('keydown', function(e){
					if (e.keyCode == 13) {
						var focusable = $('input,a,select,button,textarea').filter(':visible');
						$(this).blur()
						focusable.eq(focusable.index(this)+1).focus();
						return false;
					}
				});				
			});
		}				
	{/if}
    $(document).ready(function () { 
        var mask_money = $('.mask-money');
        if (mask_money && mask_money.length<0+{intval($smarty.const.CATALOG_SPEED_UP_DESIGN)})
        mask_money.setMaskMoney();
    });
function unformatMaskField(selector, $container)
{
    var result;
    if (typeof($container) != 'undefined') {
      result = $(selector, $container).val();
    } else {
      result = $(selector).val();
    }
{if $js_mask_type == 'accounting'}
    var currency;
    if (typeof($container) != 'undefined') {
      currency = $(selector, $container).data('currency');
    } else {
      currency = $(selector).data('currency');
    }
    if (typeof(currency) == 'undefined' || !curr_hex[currency]) return result;
        
    result = accounting.unformat(result, curr_hex[currency].decimal_point);
{/if}
    return result;
}
function unformatMaskMoney(_class, $container)
{
{if $js_mask_type == 'accounting'}
    if (typeof(_class) == 'undefined') _class = '.mask-money';
    var els;
    if (typeof($container) != 'undefined') {
      els = $(_class, $container);
    } else {
      els = $(_class);
    }
    try {
    els.each(function () {
        var result = $(this).val();
        var currency = $(this).data('currency');
        if (typeof(currency) == 'undefined') currency = currency_id;
        result = accounting.unformat(result, curr_hex[currency].decimal_point);
        if (result == 0) {
            result = '';
        }
        $(this).val(result);
    });
  } catch (e) { }
{/if}
}
</script>
