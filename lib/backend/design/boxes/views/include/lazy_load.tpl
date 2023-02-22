
{if $settings.designer_mode == 'expert'}
<div class="setting-row lazy-load">
  <label for="">Lazy load images</label>
  <select name="setting[0][lazy_load]" id="" class="form-control">
    <option value=""{if $settings[0].lazy_load == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
    <option value="1"{if $settings[0].lazy_load == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
  </select>
</div>
{/if}