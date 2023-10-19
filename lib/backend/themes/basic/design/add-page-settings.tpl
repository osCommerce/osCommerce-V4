    {if $page_type == 'products'}

    <p><label><input type="checkbox" name="added_page_settings[no_filters]"{if $added_page_settings.no_filters} checked{/if}/> {$smarty.const.TEXT_NO_FILTERS}</label></p>
    <p><label>
        <input type="checkbox" name="added_page_settings[wedding_registry]"{if $added_page_settings.wedding_registry} checked{/if}/>
        {$smarty.const.TEXT_WEDDING_REGISTRY}
      </label></p>
      <p><label>
          <input type="checkbox" name="added_page_settings[brands]"{if $added_page_settings.brands} checked{/if}/>
          {$smarty.const.TEXT_BRANDS}
        </label></p>

    {/if}

    {if $page_type == 'categories'}

    <p><label><input type="checkbox" name="added_page_settings[no_filters]"{if $added_page_settings.no_filters} checked{/if}/> {$smarty.const.TEXT_NO_FILTERS}</label></p>

    {/if}

    {if $page_type == 'product'}

    <p><label><input type="checkbox" name="added_page_settings[has_attributes]"{if $added_page_settings.has_attributes} checked{/if}/> {$smarty.const.TEXT_HAS_ATTRIBUTES}</label></p>

    <p><label><input type="checkbox" name="added_page_settings[is_bundle]"{if $added_page_settings.is_bundle} checked{/if}/> {$smarty.const.TEXT_IS_BUNDLE}</label></p>

    <p><label><input type="checkbox" name="added_page_settings[popup_product]"{if $added_page_settings.popup_product} checked{/if}/> Product page in pop up</label></p>

    {/if}

    {if $page_type == 'home'}

    <p><label><input type="checkbox" name="added_page_settings[first_visit]"{if $added_page_settings.first_visit} checked{/if}/> {$smarty.const.TEXT_FIRST_VISIT}</label></p>

    <p><label><input type="checkbox" name="added_page_settings[more_visits]"{if $added_page_settings.more_visits} checked{/if}/> {$smarty.const.TEXT_MORE_THEN_ONE_VISIT}</label></p>

    <p><label><input type="checkbox" name="added_page_settings[logged_customer]"{if $added_page_settings.logged_customer} checked{/if}/> {$smarty.const.TEXT_LOGGED_CUSTOMER}</label></p>

    <p><label><input type="checkbox" name="added_page_settings[not_logged]"{if $added_page_settings.not_logged} checked{/if}/> {$smarty.const.TEXT_NOT_LOGGED_CUSTOMER}</label></p>

    {/if}

    {if $page_type == 'order' || $page_type == 'label' || $page_type == 'invoice' || $page_type == 'packingslip' || $page_type == 'creditnote'}
        {include 'settings/pdf-page-settings.tpl'}
    {/if}

    <input type="hidden" name="theme_name" value="{$theme_name}"/>
    <input type="hidden" name="page_name" value="{$page_name}"/>