{use class="\yii\helpers\Html"}
<div class="tl_filters_title">{$smarty.const.TEXT_RANGE}</div>
<div class="wl-td">
<label>{$smarty.const.TITLE_YEAR}</label>
{Html::dropDownList('year', $year, $years, ['class' =>'form-control', 'prompt' => TEXT_SELECT])}
</div>
<div class="tl_filters_title">{$smarty.const.TEXT_CUSTOM}</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_FROM}</label>{Html::input('text', 'start_custom', $start_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
<div class="wl-td">
    <label>{$smarty.const.TEXT_TO}</label>{Html::input('text', 'end_custom', $end_custom, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
<script>
    var checkSelection = function(){
        //check custom    
        return true;
    }
    
    $(document).ready(function(){
    
        if ($('input[name=start_custom]').val().length == 0){
            $('input[name=start_custom]').css({ 'background': '#eeeeee' });
        }
        if ($('input[name=end_custom]').val().length == 0){
            $('input[name=end_custom]').css({ 'background': '#eeeeee' });
        }
        
        if ($('select[name=year]').val().length == 0){
            $('select[name=year]').css({ 'background': '#eeeeee' });
        }
        
        $('select[name=year]').focus(function(){
            $(this).css({ 'background': '#ffffff' });
            $('input[name=start_custom], input[name=end_custom]').css({ 'background': '#eeeeee' }).val('');
        });
        
        $('input[name=year]').datepicker({ 
            'minViewMode':2,
            'format':'yyyy',
            autoclose:true,
            }).on('changeDate', function(){
                $('input[name=start_custom]').val('');
                $('input[name=end_custom]').val('');
            });
            
        $('input[name=start_custom]').datepicker({ 
            'minViewMode':1, 
            'format':'mm/yyyy',
            'autoclose':true,
            'immediateUpdates': true,
            beforeShowMonth: function(date){
                var $end = $('input[name=end_custom]').val();
                if ($end.length > 0){
                    $_end = $end.split("/");
                    $end = $_end[0]+'/01/' + $_end[1];
                    $gend = new Date($end);
                    return date <= $gend;
                }
                return true;
            }
        }).on('show', function(e){
            var $end = $('input[name=end_custom]').val();
            var $send = new Date(e.date);
            if ($end.length > 0){
                $_end = $end.split("/");
                $end = $_end[0]+'/01/' + $_end[1];
                $gend = new Date($end);
                if ($gend.getFullYear() == $send.getFullYear() || isNaN($send.getFullYear())){
                    $('input[name=start_custom]').datepicker('setEndDate', $gend);
                } else {
                    $('input[name=start_custom]').datepicker('setEndDate', '');
                }
            }
        }).focus(function(){
            $('select[name=year]').css({ 'background': '#eeeeee' }).val('');
            $(this).css({ 'background': '#ffffff' });
            $('input[name=end_custom]').css({ 'background': '#ffffff' });
        });
        
        $('input[name=end_custom]').datepicker({ 
            'minViewMode':1,
            'format':'mm/yyyy',
            'autoclose':true,
            'immediateUpdates': true,
            beforeShowMonth: function(date){
                var $start = $('input[name=start_custom]').val();
                if ($start.length > 0){
                    $_start = $start.split("/");
                    $start = $_start[0]+'/01/' + $_start[1];
                    $gstart = new Date($start);
                    return date >= $gstart;
                }
                return true;
            }
        }).on('show', function(e){
            var $start = $('input[name=start_custom]').val();
            var $sstart = new Date(e.date);
            if ($start.length > 0){
                $_start = $start.split("/");
                $start = $_start[0]+'/01/' + $_start[1];
                $gstart = new Date($start);
                if ($gstart.getFullYear() == $sstart.getFullYear() || isNaN($sstart.getFullYear()) ){
                    $('input[name=end_custom]').datepicker('setStartDate', new Date($start));
                } else {
                    $('input[name=end_custom]').datepicker('setStartDate', '');
                }                
            }
        }).focus(function(){
            $('select[name=year]').css({ 'background': '#eeeeee' }).val('');
            $('input[name=start_custom]').css({ 'background': '#ffffff' });
            $(this).css({ 'background': '#ffffff' });
        });

    })
    
</script>