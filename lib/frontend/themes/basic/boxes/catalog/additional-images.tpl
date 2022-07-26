<ul class="images-listing">
    {foreach $images as $image}
        <li>
            <a href="{$image['image_url']}" data-fancybox-group="category">{$image['img']}</a>
        </li>
    {/foreach}
</ul>
{\frontend\design\Info::addBoxToCss('fancybox')}