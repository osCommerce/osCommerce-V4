{use class="\yii\helpers\Html"}
<div>
    {if $printers}
        <div style="padding-top:10px;">
        {Html::dropDownList('printers', '', $printers, ['class' => 'form-control-small'])}
        {*Html::button('Describe printer', ['class' => 'btn btn-primary describe-cloud-printer'])*}
        {Html::button('Accept printer', ['class' => 'btn btn-primary accept-cloud-printer'])}
        </div>
    {else}
        <label>No registered cloud printers</label>
    {/if}
    <script>
        $(document).ready(function(){
            $('.describe-cloud-printer').click(function(){
                if ($('select[name=printers]').val()){
                    $.post('printers/describe', $('form[name=printers_form]').serializeArray(), function(){
                        
                    })
                }
                
            })
            
            $('.accept-cloud-printer').click(function(){
                if ($('select[name=printers]').val()){
                    $.post('printers/accept', $('form[name=printers_form]').serializeArray(), function(data){
                        errors(data);
                        drawPrinters();
                    })
                }
                
            })
        })
    </script>
</div>