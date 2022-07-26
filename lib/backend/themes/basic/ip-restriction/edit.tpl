{use class="\yii\helpers\Html"}
    <form action="{\yii\helpers\Url::to('ip-restriction/save')}" method="post" name="restriction">
    {Html::input('hidden', 'forbidden_id', $forbidden->forbidden_id)}
        <div class="or_box_head">{$forbidden->forbidden_address}</div>
        <div class="row_or_wrapp">
            <div class="">
                <label>{$smarty.const.TEXT_FORBIDDEN_ADDRESS}</label>
                <div>{Html::textInput('forbidden_address', $forbidden->forbidden_address, ['class' => 'form-control'])}</div>
            </div>
        </div>
        <div class="btn-toolbar btn-toolbar-order">
            <button class="btn btn-save btn-no-margin">{$smarty.const.IMAGE_SAVE}</button>
            <a class="btn btn-cancel btn-no-margin" onclick="resetStatement()">{$smarty.const.IMAGE_CANCEL}</a>
        </div>	
    </form>
<script>
$(document).ready(function(){
 
    $('form[name=restriction]').submit(function(e){
        $.post('ip-restriction/save', 
            $('form[name=restriction]').serialize(),
            function(data, status){
                if (status="success"){
                    if (data.hasOwnProperty('forbidden_address')){
                        if(Array.isArray(data.forbidden_address)){
                            str = '<center>';
                            $.each(data.forbidden_address, function(i, e){
                                str += "<br>" + e + "<br/>";
                            })
                            str += '<br/></center>';
                            alertMessage(str);
                        }
                    }
                    kTable.fnDraw(false);
                }
            }, "json");
         return false;
    });
});

function resetStatement(){
    kTable.fnDraw(false);
}
</script>