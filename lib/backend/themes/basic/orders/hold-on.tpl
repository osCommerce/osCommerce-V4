{use class="common\helpers\Html"}
<div class="pop-up-content">
    <div class="popup-heading">{$smarty.const.POPUP_HEAD_HOLD_ON_DATE}</div>
    <div class="popup-content js-popup-holdon">
        {Html::hiddenInput('hold_on_date', $currentHoldOnDate, ['id' => 'txtHoldOnDate'])}
        <div class="text-center">
            <div class="pickHoldOnDate" style="display: inline-block"></div>
            <button type="button" class="btn js-hold-on-reset">Reset hold on date</button>
        </div>
    </div>
</div>
<div class="noti-btn js-popup-holdon-buttons">
    <div><span class="btn btn-close">{$smarty.const.IMAGE_CANCEL}</span></div>
    <div><span class="btn btn-primary js-update">{$smarty.const.IMAGE_UPDATE}</span></div>
</div>
<script>
    $(window).scrollTop(0);
    $('.js-popup-holdon-buttons .btn').click(function(event){
        var _self = event.target;
        if ( $(_self).hasClass('js-update') ) {

            $.ajax({
                url: '{$updateUrl}',
                method: 'POST',
                data:{ hold_on_date: $('#txtHoldOnDate').val() },
                success:function(data)
                {
                    if ( data.status=='ok' ) {
                        if ( data.hold_on_date ) {
                            $('.btn-hold-on').addClass('holdOnOrder');
                        }else{
                            $('.btn-hold-on').removeClass('holdOnOrder');
                        }
                    }
                    $(_self).parents('.popup-box').find('.pop-up-close').trigger('click');
                }
            })
        }else{
            $(_self).parents('.popup-box').find('.pop-up-close').trigger('click');
        }
    });
    var initHoldOnDate = $('#txtHoldOnDate').val();
    $('.js-popup-holdon .pickHoldOnDate').datepicker({
        'defaultDate':$.datepicker.parseDate('yy-mm-dd', $('#txtHoldOnDate').val()),
        //'format':'{$smarty.const.DATE_FORMAT_DATEPICKER}',
        'format':'yy-mm-dd',
        'autoclose':true,
        'numberOfMonths': 2,
        'stepMonths': 2,
        'todayHighlight': true,
        'altField':'#txtHoldOnDate',
        'altFormat':'yy-mm-dd',
        'startDate':'0'
    });
    var resetSelectedDate = function(){
        $('.js-popup-holdon .pickHoldOnDate').datepicker('setDate', null);
        $('#txtHoldOnDate').val('');
        var $today =$('.js-popup-holdon .pickHoldOnDate .ui-datepicker-today');
        $today.removeClass('ui-datepicker-days-cell-over');
        $today.find('.ui-state-active').removeClass('ui-state-active');
        $today.find('.ui-state-hover').removeClass('ui-state-hover');
    };
    if ( initHoldOnDate.length==0 ) {
        resetSelectedDate();
    }
    $('.js-hold-on-reset').on('click',function(){
        resetSelectedDate();
    });
</script>
