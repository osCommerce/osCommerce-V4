{if !empty($alphabets) && is_array($alphabets) }
<div class="brands-abc"><a name="abc_top" />
  {foreach $alphabets as $_a_name => $alphabet}
    {if $alphabets|@count>2 && $_p_name!='0-9'}
    <div class="brands-abc brands-abc-l">
    {/if}

    {if $_p_name!='0-9'}
      <ul class="w-menu-horizontal brands-abc-list">
    {/if}

    {foreach $alphabet.letters as $letter }
      <li class="brands-abc-letter main-content"><h4>
          {if in_array($letter, $alphabet.active)}
            <a href="catalog/brands#brand_l_{$letter}" class="a-on-page" >{$letter}</a>
          {else}
            {$letter}
          {/if}
      </h4></li>
    {/foreach}
    {if $_a_name!='0-9'}
      </ul>
    {/if}

    {if $alphabets|@count>2 && $_a_name!='0-9'}
    </div>
    {/if}
    {$_p_name=$_a_name}
  {/foreach}
</div>
{/if}
<div class="brands-listing">
  {foreach $brands as $brand}
    {if !empty($alphabets) && $prevChar != $brand.f_letter}
      {$prevChar = $brand.f_letter}
      
      <h3><div class="brands-new-char "><a name="brand_l_{$brand.f_letter}">{$brand.f_letter}</a></div></h3>
      <a href="catalog/brands#abc_top" class="a-on-page a-to-top">{$smarty.const.GO_TO_TOP}</a>
      
    {/if}
        <div class="item">
              <a class="brand-link" href="{$brand.link}">
                  {$brand.img}
                    <table class="wrapper"><tr><td><div class="name">
                                            <h2>
                                        {if $brand.h2}
                                            {$brand.h2}
                                        {else}
                                            {$brand.manufacturers_name}
                                        {/if}</h2>
                                      </div></td></tr></table>
              </a>
        </div>

  {/foreach}
</div>