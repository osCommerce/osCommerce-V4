<div style="padding:5px;">
    <strong>{$job['title']}</strong>: {date("Y-m-d H:i:s", $job['createTime']/1000)} - {if is_array($job['uiState'])}{implode(" : ", array_values($job['uiState']))}{/if}
</div>