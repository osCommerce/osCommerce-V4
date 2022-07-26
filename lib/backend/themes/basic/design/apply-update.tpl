<form action="design/apply-update-submit?theme_name={$theme_name}" method="post" class="apply-form">
<div class="apply-updates">
  {foreach $attributes['attributes_new'] as $media => $media_box}

    {foreach $media_box as $selector => $selectors_box}

      {foreach $selectors_box as $attribute}
        {if $attribute.value != $attribute.value_exist}
          {if $attribute@index == 0}
            {if $selectors_box@index == 0}
              {if $media_box@index == 0}
                <div class="tide-heading">New Styles</div>
              {/if}
              {if $media}
                <div class="media-heading">@media {$media}</div>
              {/if}
            {/if}
            <div class="selector-heading">{$selector} { </div>
            <div class="attribute-row">
              <div class="attribute-check heading">&nbsp;</div>
              <div class="attribute-name heading">&nbsp;</div>
              <div class="attribute-exist heading">exist</div>
              <div class="attribute-new heading">new</div>
            </div>
          {/if}
          <div class="attribute-row">
            <div class="attribute-check">
              <input type="checkbox" name="attributes_new[{$attribute.local_id}]"
                      {if !$attribute.value_exist} checked{/if}>
            </div>
            <div class="attribute-name">{$attribute.attribute}:</div>
            <div class="attribute-exist">{$attribute.value_exist}&nbsp;</div>
            <div class="attribute-new">{$attribute.value}</div>
          </div>
          {if $attribute@last}
            <div class="selector-footer">}</div>
          {/if}
        {/if}
      {/foreach}
    {/foreach}

  {/foreach}


  {foreach $attributes['attributes_changed'] as $media => $media_box}

    {foreach $media_box as $selector => $selectors_box}

      {foreach $selectors_box as $attribute}
        {if $attribute.value != $attribute.value_exist}
          {if $attribute@index == 0}
            {if $selectors_box@index == 0}
              {if $media_box@index == 0}
                <div class="tide-heading">Changed Styles</div>
              {/if}
              {if $media}
                <div class="media-heading">@media {$media}</div>
              {/if}
            {/if}
            <div class="selector-heading">{$selector} { </div>
            <div class="attribute-row">
              <div class="attribute-check heading">&nbsp;</div>
              <div class="attribute-name heading">&nbsp;</div>
              <div class="attribute-old heading">old</div>
              <div class="attribute-exist heading">exist</div>
              <div class="attribute-new heading">new</div>
            </div>
          {/if}
          <div class="attribute-row">
            <div class="attribute-check">
              <input type="checkbox" name="attributes_changed[{$attribute.local_id}]"
                      {if $attribute.value_old == $attribute.value_exist && $attribute.value_old != $attribute.value} checked{/if}>
            </div>
            <div class="attribute-name">{$attribute.attribute}:</div>
            <div class="attribute-old">{$attribute.value_old}&nbsp;</div>
            <div class="attribute-exist">{$attribute.value_exist}&nbsp;</div>
            <div class="attribute-new">{$attribute.value}</div>
          </div>
          {if $attribute@last}
            <div class="selector-footer">}</div>
          {/if}
        {/if}
      {/foreach}
    {/foreach}

  {/foreach}


  {foreach $attributes['attributes_delete'] as $media => $media_box}

    {foreach $media_box as $selector => $selectors_box}

      {foreach $selectors_box as $attribute}
        {if $attribute.value != $attribute.value_exist}
          {if $attribute@index == 0}
            {if $selectors_box@index == 0}
              {if $media_box@index == 0}
                <div class="tide-heading">Deleted Styles</div>
              {/if}
              {if $media}
                <div class="media-heading">@media {$media}</div>
              {/if}
            {/if}
            <div class="selector-heading">{$selector} { </div>
            <div class="attribute-row">
              <div class="attribute-check heading">&nbsp;</div>
              <div class="attribute-name heading">&nbsp;</div>
              <div class="attribute-old heading">old</div>
              <div class="attribute-exist heading">exist</div>
            </div>
          {/if}
          <div class="attribute-row">
            <div class="attribute-check">
              <input type="checkbox" name="attributes_delete[{$attribute.local_id}]">
            </div>
            <div class="attribute-name">{$attribute.attribute}:</div>
            <div class="attribute-old">{$attribute.value}&nbsp;</div>
            <div class="attribute-exist">{$attribute.value_exist}</div>
          </div>
          {if $attribute@last}
            <div class="selector-footer">}</div>
          {/if}
        {/if}
      {/foreach}
    {/foreach}

  {/foreach}
  <div class="btn-bar">
    <div class="btn-right">
      <button type="submit" class="btn btn-confirm">Apply</button>
    </div>
  </div>
</div>
</form>
