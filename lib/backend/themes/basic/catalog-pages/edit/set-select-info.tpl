{use class = "yii\helpers\Html"}
<tr role="row" prefix="set-information-{$assignInformationForm->information_id}" {if $assignInformationForm->hide} class="dis_prod"{/if}>
    <td class="ast-name-element">
        {$assignInformationForm->page_title}
        {Html::activeInput('hidden', $assignInformationForm, '['|cat:$assignInformationForm->information_id|cat:']information_id', []) }
    </td>
    <td class="remove-ast" onclick="deleteSelectedInfoPage(this)"></td>
</tr>