{use class="yii\helpers\Html"}
<div>
    <form id="frmDirectoryConfig" action="javascript:void(0)">
    <table id="tblDirectorySetting" data-row_count="{count($directoryConfigs)}" width="100%" cellpadding="1" cellspacing="1">
        <thead>
        <tr>
            <td>{$smarty.const.TEXT_FILE_FORMAT}</td>
            <td>{$smarty.const.TEXT_FILE_TYPE}</td>
            <td>{$smarty.const.TEXT_RUN_FREQUENCY}</td>
            <td width="90">{$smarty.const.TEXT_TIME}</td>
            <td><div class="text-right"><button class="btn btn-add-more js-directory-config-add-more">{$smarty.const.TEXT_ADD_MORE}</button></div></td>
        </tr>
        </thead>
        <tbody>
        {foreach from=$directoryConfigs key=index item=config}
        <tr class="js_row js_row_{$index}">
            <td>{Html::dropDownList('directory_config['|cat:$index|cat:'][file_format]', $config['file_format'], $formatReaders['items'], ['class' => 'form-control'])}</td>
            <td>{Html::dropDownList('directory_config['|cat:$index|cat:'][job_provider]', $config['job_provider'], $providersList['items'], ['class' => 'form-control'])}</td>
            <td>{Html::dropDownList('directory_config['|cat:$index|cat:'][run_frequency]', $config['run_frequency'], $launchFrequency['items'], ['class' => 'form-control js-frequency-select'])}</td>
            <td>{Html::textInput('directory_config['|cat:$index|cat:'][run_time]', $config['run_time'], ['class' => 'form-control form-control-small on-time js-defined-time'])}</td>
            <td>
                {Html::hiddenInput('directory_config['|cat:$index|cat:'][filename_pattern]', $config['filename_pattern'])}
                <button class="btn btn-remove js-directory-config-remove">{$smarty.const.TEXT_REMOVE}</button>
            </td>
        </tr>
        {/foreach}
        </tbody>
        <tfoot style="display: none">
        <tr class="js_row js_row__cnt_">
            <td>{Html::dropDownList('directory_config_new_[file_format]', $formatReaders['selection'], $formatReaders['items'], ['class' => 'form-control'])}</td>
            <td>{Html::dropDownList('directory_config_new_[job_provider]', $providersList['selection'], $providersList['items'], ['class' => 'form-control'])}</td>
            <td>{Html::dropDownList('directory_config_new_[run_frequency]', $launchFrequency['selection'], $launchFrequency['items'], ['class' => 'form-control js-frequency-select'])}</td>
            <td>{Html::textInput('directory_config_new_[run_time]', $runTimeDefault, ['class' => 'form-control on-time js-defined-time'])}</td>
            <td>
                {Html::hiddenInput('directory_config_new_[filename_pattern]', '')}
                <button class="btn btn-remove js-directory-config-remove">{$smarty.const.TEXT_REMOVE}</button>
            </td>
        </tr>
        </tfoot>
    </table>
    </form>
</div>
