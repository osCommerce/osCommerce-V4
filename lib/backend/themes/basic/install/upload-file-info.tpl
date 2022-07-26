{use class="yii\helpers\Html"}
{Html::beginForm($action, 'post', ['onsubmit' => "return fileUploadStart(this);", 'class' => 'uploadInfo'])}
{foreach $packagesSelectedList as $packagesSelected name=Selected}
    {if $smarty.foreach.Selected.first}
        <label>{$smarty.const.TEXT_REQUIRED_LIST}:</label>
    {/if}
    <p>{$packagesSelected}</p>
{/foreach}
{if $readyForInstall}
    {if $platformSelection}
        <label>{$smarty.const.TEXT_CHOOSE_PLATFORM}:</label> {Html::dropDownList('platform',\common\classes\platform::defaultId(),$platforms,['class'=>'form-control'])}
    {/if}
    {if $aclSelection}
        <label>{$smarty.const.TEXT_CHOOSE_ACL}:</label> {Html::dropDownList('acl', 'my', ['all' => $smarty.const.TEXT_SET_ACL_ALL, 'my' => $smarty.const.TEXT_SET_ACL_MY, '' => $smarty.const.TEXT_SET_ACL_NO], ['class'=>'form-control'])}
    {/if}
    {foreach $packagesDependedList as $id=>$packagesDepended name=Depended}
        {if $smarty.foreach.Depended.first}
            <label>{$smarty.const.TEXT_DEPENDED_LIST}:</label>
        {/if}
        <label class="checkbox"><input type="checkbox" name="depended[]" value="{$id}">{$packagesDepended}</label>
    {/foreach}
    <button class="btn">{$smarty.const.TEXT_CONTINUE_INSTALL}</button>
{else}
    <button class="btn">{$smarty.const.TEXT_CONTINUE_DOWNLOAD}</button>
{/if}
{Html::hiddenInput('readyForInstall', $readyForInstall)}
{Html::hiddenInput('id', $id)}
{Html::endForm()}