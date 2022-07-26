<style type="text/css">
.news a { display: flex;padding: 5px 0;color: inherit;width: 100%;text-decoration: none}
.news a:hover { text-decoration: none;}
.news a + a { border-top: 1px solid #dedede}
.news .image { width: 25%;padding-right: 10px;}
.news .image img { max-width: 100%;width: auto;height: auto;}
.news .item-holder { width: 75%;}
.news .title { display: block;font-size: 1em;font-weight: bold;}
.news .description { display: block;margin-bottom: 5px;font-size: .9em;}
.news .date { display: block;font-size: .8em;opacity: 0.7;}
.title-holder { display: flex;justify-content: space-between;align-items: baseline; width: 100%}
.date { white-space: nowrap}
.item-wrapper { width: 100%}
</style>

<div class="news">
{foreach $news as $item}
        <a href="{$item.link}" target="_blank">
            {if $item.image}
                <span class="image">{$item.image}</span>
            {/if}
            <span class="{if $item.image}item-holder{else}item-wrapper{/if}">
                <span class="title-holder">
                    <span class="title">{$item.title}</span>
                    <span class="date" title="{$item.dateTime}">{$item.date}</span>
                </span>
                <span class="description">{$item.description|truncate:200:"..."}</span>
            </span>
        </a>
{/foreach}
</div>