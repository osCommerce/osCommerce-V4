{use class="yii\helpers\Html"}
<div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TEXT_RUN_FREQUENCY" required_text=""}</label>{Html::dropDownList('run_frequency', $run_frequency, $runFrequencyVariants, ['class' => 'form-control','id'=>'txtRunFrequency'])}
            <div id="selFreqPeriod">
                <span><input type="radio" name="freq_period" value="job" {if $freq_period=='job'}checked="checked"{/if}> {$smarty.const.TEXT_RUN_ONCE}</span>
                <span><input type="radio" name="freq_period" value="directory" {if $freq_period!='job'}checked="checked"{/if}> {$smarty.const.TEXT_RUN_PERIODICALLY}</span>
            </div>
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TEXT_TIME" required_text=""}</label>
            {Html::textInput('run_time', $run_time, ['class' => 'on-time form-control','id'=>'txtRunTime'])}
        </div>
    </div>
</div>