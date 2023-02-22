{if $designer_mode == 'expert'}
<div class="setting-row">
  <label for="">{$smarty.const.TEXT_LOAD_BOX}</label>
  <select name="setting[0][ajax]" id="" class="form-control">
    <option value=""{if $settings[0].ajax == ''} selected{/if}>{$smarty.const.TEXT_WITH_CONTENT}</option>
    <option value="1"{if $settings[0].ajax == '1'} selected{/if}>{$smarty.const.TEXT_BY_AJAX}</option>
  </select>
</div>
{/if}