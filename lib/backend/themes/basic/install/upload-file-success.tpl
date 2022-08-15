{$uploadInfo}
<p>{$message}</p>
{foreach $packagesSynergyList as $package name=Selected}
    {if $smarty.foreach.Selected.first}
        <label>Synergy packages list:</label>
    {/if}
    <p>{$package}</p>
{/foreach}