 {if is_array($attributes) && count($attributes) }
     <div class="product-attributes attributes w-line-row-2 w-line-row-22">
      {foreach $attributes as $item}
        <div>
          <div class="edp-line">
              <label>{$item.title}:</label>
              <div>
                {assign var = 'aName' value = 'product_info[][id]['|cat:$item.id|cat:']'}
                {if $complex}
                    {$aName = 'product_info[]'|cat:$item.id}{*configurator*}                
                {/if}                
                  <select name='{$aName}' data-required="{$smarty.const.PLEASE_SELECT} {$item.title}" onchange="{$settings['onchange']}" class="form-control">
                      {*<option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>*}
                      {foreach $item.options as $option}
                          <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                      {/foreach}
                  </select>
              </div> 
          </div>
        </div>
      {/foreach}
    </div>
  {/if}