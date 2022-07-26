{use class="\yii\helpers\Html"}
{\backend\assets\BDPAsset::register($this)|void}
<div class="wl-td">
<label>{$smarty.const.TEXT_DATE}:</label>
{Html::input('text', 'day', $day, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
{if !is_null($day_cmp)}
<div class="wl-td">
<label>{$smarty.const.TEXT_DAY_CMP}:</label>
{Html::input('text', 'day_cmp', $day_cmp, ['class' =>'form-control', 'placeholder' => TEXT_SELECT])}
</div>
{/if}
<script>
    var checkSelection = (function() {
        //check custom
        return true;
    });
    $(document).ready(function() {
        $('input[name=day]').datepicker({
            'minViewMode': 0,
            'format': 'dd/mm/yyyy',
            'autoclose': true,
        });
        $('input[name=day_cmp]').datepicker({
            'minViewMode': 0,
            'format': 'dd/mm/yyyy',
            'autoclose': true,
        });
    });
</script>