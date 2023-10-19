 {if is_array($attributes) && count($attributes) }
     <div class="product-attributes attributes w-line-row-2 w-line-row-22">
      {foreach $attributes as $item}
        <div>
          <div class="edp-line">
              <label>{$item.title}:</label>
              <div>
                {assign var = 'aName' value = 'product_info[][id]['|cat:$item.id|cat:']'}
                {assign var = 'attrTextName' value = 'product_info[][attr_text]['|cat:$item.id|cat:']'}
                {if $complex}
                    {$aName = 'product_info[]'|cat:$item.id}{*configurator*}                
                {/if}                
                  <select name='{$aName}' data-required="{$smarty.const.PLEASE_SELECT} {$item.title}" onchange="{$settings['onchange']}" class="form-select">
                      {*<option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>*}
                      {foreach $item.options as $option}
                          {if $option.id==$item.selected}{$selected_type = $option.type}{/if}
                          <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                      {/foreach}
                  </select>
                  {if $selected_type == 1}
                      <div class="textinput js-attr-text-input">
                          <input type="text" size=14 name="{$attrTextName}" value="{$attrText[$item.id]|default:null}" style="width:100%">
                      </div>
                  {/if}
                  {if $selected_type == 2}
                      <div class="textarea js-attr-area-input">
                          <textarea cols=12 rows=1 name="{$attrTextName}" style="width:100%">{$attrText[$item.id]|default:null}</textarea>
                      </div>
                  {/if}
              </div>
          </div>
        </div>
      {/foreach}
    </div>
  {/if}