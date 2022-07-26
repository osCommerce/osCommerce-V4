{use class="yii\helpers\Html"}
<div id="blockNewDatasource">
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="HEADING_TYPE" required_text=""}</label>{Html::dropDownList('new_datasource[class]', '', $availableSourcesVariants, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TABLE_TEXT_NAME" required_text="*"}</label>{Html::textInput('new_datasource[name]', '', ['class' => 'form-control', 'id'=>'txtDSName'])}
        </div>
    </div>
{*    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TEXT_DIRECTORY" required_text="*"}</label>{Html::textInput('new_datasource[directory]', '', ['class' => 'form-control', 'id'=>'txtDSDirectory'])}
        </div>
    </div>*}
</div>
