{use class="yii\helpers\Html"}
<div>
    <form id="frmDirectoryConfig" action="javascript:void(0)">
        {$smarty.const.TEXT_HOW_LONG_KEEP_PROCESSED}
        {Html::dropDownList('directory_config[cleaning_term]', $directoryConfigs['cleaning_term'], $cleaningTerm['items'], ['class' => 'form-control'])}
    </form>
</div>
