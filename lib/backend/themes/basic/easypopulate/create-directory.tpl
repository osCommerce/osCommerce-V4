{use class="yii\helpers\Html"}
<div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="HEADING_TYPE" required_text=""}</label>{Html::dropDownList('directory[directory_type]', '', $directoryTypeVariants, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TABLE_TEXT_NAME" required_text="*"}</label>{Html::textInput('directory[name]', '', ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>{field_label const="TEXT_DIRECTORY" required_text="*"}</label>{Html::textInput('directory[directory]', '', ['class' => 'form-control'])}
        </div>
    </div>
</div>
