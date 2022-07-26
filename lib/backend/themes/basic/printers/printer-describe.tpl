<div style="padding:10px;">
    {if is_array($data)}
        {if $data['orient']}
            <label><b>Orientation:</b> 
                {implode(", ", $data['orient'])}
            </label>            
        {/if}
        {if $data['formats']}
            <label><b>Media:</b> 
                {implode(", ", $data['formats'])}
            </label>            
        {/if}
        {if $data['dpi']}
            <label><b>Dpi:</b> 
                {implode(", ", $data['dpi'])}
            </label>            
        {/if}
    {/if}
</div>