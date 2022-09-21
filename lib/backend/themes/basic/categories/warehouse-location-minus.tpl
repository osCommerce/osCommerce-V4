{use class="yii\helpers\Html"}
<div>
    <label>{$smarty.const.TABLE_HEADING_LOCATION}:</label>
{foreach $locationList as $location}
<div class="form-group">
        <label class="control-label">{$location.name}</label>
        <div class="input-slider">
                <div class="slider-controls slider-value-top">
                        {Html::input('text', 'stock_minus_qty_'|cat:$location.id, 0, ['class'=>'form-control form-control-small-qty', 'onchange' => 'updateMinusSlider("'|cat:$location.id|cat:'");'])}
                </div>
                <div id="slider-minus-{$location.id}"></div>
        </div>
    <div class="available-stock-info">{$smarty.const.AVAILABLE_STOCK}: <span class="available-stock-val">{$location.qty}</span></div>
</div>
{/foreach}
<div class="form-group summary">
    <label class="control-label">{$smarty.const.TEXT_SUMMARY}:</label>
    <div class="">
            <span id="slider-minus-qty-total">0</span>
    </div>
</div>
</div>
<script type="text/javascript">
var minus_allocated_stock = [];
function updateMinusSlider(id) {
    var val = $('input[name="stock_minus_qty_'+id+'"]').val();
    $('#slider-minus-'+id).slider('value', val);
    minus_allocated_stock[id] = parseInt(val);
    updateMinusSliderTotal();
}
function updateMinusSliderTotal() {
    var total_allocated_stock = 0;
    for (var i in minus_allocated_stock) {
        total_allocated_stock += minus_allocated_stock[i];
    } 
    $('#slider-minus-qty-total').text(total_allocated_stock);
}
{foreach $locationList as $location}
    minus_allocated_stock['{$location.id}'] = 0;
    $( '#slider-minus-{$location.id}' ).slider({
            range: 'min',
            value: 0,
            min: 0,
            max: {$location.qty},
            slide: function( event, ui ) {
                minus_allocated_stock['{$location.id}'] = ui.value;
                updateMinusSliderTotal();
                $('input[name="stock_minus_qty_{$location.id}"]').val(ui.value);
            }
    });
{/foreach}

</script>