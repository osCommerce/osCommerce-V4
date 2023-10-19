{use class="\yii\helpers\Html"}

<div class="wl-td quarter">
    <label>{$smarty.const.TEXT_FROM}:</label>
    {Html::dropDownList('start_custom_quarter', $start_custom_quarter, $quarters, ['class' =>'form-control custom-block', 'prompt' => TEXT_QUARTER])}<span>/</span>{Html::dropDownList('start_custom_year', $start_custom_year, $years, ['class' =>'form-control custom-block', 'prompt' => TITLE_YEAR])}
</div>
<div class="wl-td quarter">
    <label>{$smarty.const.TEXT_TO}</label>
    {Html::dropDownList('end_custom_quarter', $end_custom_quarter, $quarters, ['class' =>'form-control custom-block', 'prompt' => TEXT_QUARTER])}<span>/</span>{Html::dropDownList('end_custom_year', $end_custom_year, $years, ['class' =>'form-control custom-block', 'prompt' => TITLE_YEAR])}
</div>
<script>

  var checkSelection = function(){
    //check custom
    if ($('.work').length > 0){
        if ($('.work:first').hasClass('range-block')){ //2
            var items = 0;
            $.each($('.work'), function(i, e){
                if ($(e).val() > 0) items++;
            });
            if (items == 2) return true;
        }
        if ($('.work:first').hasClass('custom-block')){ //4
            var items = 0;
            $.each($('.work'), function(i, e){
                if ($(e).val() > 0) items++;
            });
                        
            if (items == 4) {
                if ($('select[name=start_custom_year]').val() > $('select[name=end_custom_year]').val()) return false;
                if ($('select[name=start_custom_year]').val() == $('select[name=end_custom_year]').val() && 
                    $('select[name=start_custom_quarter]').val() > $('select[name=end_custom_quarter]').val()
                ) {
                    return false;
                }
                return true;
            } else if(items == 2 ){
                if ( ($('select[name=start_custom_year]').val() >0 && $('select[name=start_custom_quarter]').val() > 0 ) || 
                    ( $('select[name=end_custom_year]').val() >0 && $('select[name=end_custom_quarter]').val() > 0 )
                    ){
                        return true;
                    }
            }
        }
    } else {
        return true;
    }
    return false;
  }
  
 $(document).ready(function(){
    
   /* if ($('select[name=quarter]').val() == ''){
        $('select[name=quarter]').css({ 'background': '#eeeeee' }).removeClass('work').addClass('out');
    } else {
        $('select[name=quarter]').css({ 'background': '#ffffff' }).removeClass('out').addClass('work');
    }
    
    if ($('select[name=year]').val() == ''){
        $('select[name=year]').css({ 'background': '#eeeeee' }).removeClass('work').addClass('out');
    } else {
        $('select[name=year]').css({ 'background': '#ffffff' }).removeClass('out').addClass('work');
    }*/
 
    if ($('select[name=start_custom_quarter]').val() == ''){
        $('select[name=start_custom_quarter]').removeClass('work').addClass('out');
    } else {
        $('select[name=start_custom_quarter]').removeClass('out').addClass('work');
    }
    
    if ($('select[name=start_custom_year]').val() == ''){
        $('select[name=start_custom_year]').removeClass('work').addClass('out');;
    } else {
        $('select[name=start_custom_year]').removeClass('out').addClass('work');
    }
    
    if ($('select[name=end_custom_quarter]').val() == ''){
        $('select[name=end_custom_quarter]').removeClass('work').addClass('out');;
    } else {
        $('select[name=end_custom_quarter]').removeClass('out').addClass('work');
    }
    
    if ($('select[name=end_custom_year]').val() == ''){
        $('select[name=end_custom_year]').removeClass('work').addClass('out');;
    } else {
        $('select[name=end_custom_year]').removeClass('out').addClass('work');
    }
    
    $('select[name=start_custom_quarter], select[name=start_custom_year], select[name=end_custom_quarter], select[name=end_custom_year]').focus(function(){
        $('select[name=start_custom_quarter], select[name=start_custom_year], select[name=end_custom_quarter], select[name=end_custom_year]').removeClass('out').addClass('work');
        $('select[name=quarter], select[name=year]').removeClass('work').addClass('out');
        $('select[name=quarter], select[name=year]').val('');
    });
    
     $('select[name=quarter], select[name=year]').focus(function(){
        $('select[name=start_custom_quarter], select[name=start_custom_year], select[name=end_custom_quarter], select[name=end_custom_year]').removeClass('work').addClass('out');
        $('select[name=quarter], select[name=year]').removeClass('out').addClass('work');
        $('select[name=start_custom_quarter], select[name=start_custom_year], select[name=end_custom_quarter], select[name=end_custom_year]').val('');
    });
    
 });
 
</script>
