<div class="pr-add-det-wrapp after {if $sameAddress}pr-add-det-wrapp2{/if}">
    {if !$sameAddress}
        <div class="pr-add-det-box pr-add-det-box02 after">
            <div class="pra-sub-box after">
                <div class="pra-sub-box-map">
                    {$manager->render('Map', ['marker' => 'gmap_markers1'])}
                </div>
            </div>
            <div class="pra-sub-box after">
                <div class="pra-sub-box-map">
                    {$manager->render('Map', ['marker' => 'gmap_markers2'])}
                </div>
            </div>
            {$manager->render('MapJS', ['addresses' => [ ['address' => $order->delivery , 'marker' => 'gmap_markers1'], ['address' => $order->billing , 'marker' => 'gmap_markers2'] ], 'order' => $order ])}
        </div>
    {else}
        <div class="pr-add-det-box pr-add-det-box02 pr-add-det-box03 after">
            <div class="pra-sub-box after">
                <div class="pra-sub-box-map">


                </div>
                <div class="pra-sub-box-map">
                    {$manager->render('Map', ['marker' => 'gmap_markers'])}
                </div>
                {$manager->render('MapJS', ['addresses' => [ ['address' => $order->delivery , 'marker' => 'gmap_markers'] ], 'order' => $order ])}
            </div>
        </div>
    {/if}
</div>