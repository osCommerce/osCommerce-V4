{use class="common\helpers\Html"}
<div class="scroll-table-workaround" id="ep_datasource_config">

    <div class="widget box">
        <div class="widget-header">
            <h4>API Access details</h4>
            <div class="toolbar no-padding">
                <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                </div>
            </div>
        </div>
        <div class="widget-content">
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Your API Id:</label> {Html::textInput('datasource['|cat:$code|cat:'][client][API_ID]', $client['API_ID'], ['class' => 'form-control'])}
                </div>
            </div>
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Your API Key:</label> {Html::textInput('datasource['|cat:$code|cat:'][client][API_KEY]', $client['API_KEY'], ['class' => 'form-control'])}
                </div>
            </div>
        </div>
    </div>


    <div class="widget box">
      <div class="widget-header">
          <h4>Orders</h4>
          <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
          </div>
      </div>
      <div class="widget-content">

          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Export Order statuses:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][export_statuses]', $order['export_statuses']['value'], $order['export_statuses']['items'], $order['export_statuses']['options'])}
              </div>
          </div>

          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Set Order status after export:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][export_success_status]', $order['export_success_status']['value'], $order['export_success_status']['items'], $order['export_success_status']['options'])}
              </div>
          </div>

          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Set Order status after server dispatch:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][so_complete_status]', $order['so_complete_status']['value'], $order['so_complete_status']['items'], $order['so_complete_status']['options'])}
              </div>
          </div>
          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Cancelled Sale Order status:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][so_cancel_status]', $order['so_cancel_status']['value'], $order['so_cancel_status']['items'], $order['so_cancel_status']['options'])}
              </div>
          </div>


          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Order modification update:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][disable_order_update]', $order['disable_order_update']['value'], $order['disable_order_update']['items'], $order['disable_order_update']['options'])}
              </div>
          </div>

          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Export Shipping and Fees as:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][export_surcharge]', $order['export_surcharge']['value'], $order['export_surcharge']['items'], $order['export_surcharge']['options'])}
              </div>
          </div>

          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Order shipping product code:</label> {Html::textInput('datasource['|cat:$code|cat:'][order][shipping_product]', $order['shipping_product'])}
              </div>
          </div>
          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Fee product code:</label> {Html::textInput('datasource['|cat:$code|cat:'][order][fee_product]', $order['fee_product'])}
              </div>
          </div>

            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>PO complete status:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][po_complete_status]', $order['po_complete_status']['value'], $order['po_complete_status']['items'], $order['po_complete_status']['options'])}
                </div>
            </div>

            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>PO cancelled status:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][order][po_cancel_status]', $order['po_cancel_status']['value'], $order['po_cancel_status']['items'], $order['po_cancel_status']['options'])}
                </div>
            </div>

      </div>
  </div>


    <div class="widget box">
      <div class="widget-header">
          <h4>Shipping Methods Map</h4>
          <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
          </div>
      </div>
      <div class="widget-content">
        {foreach $shipping as $shipping_class => $data}
          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>{$data['title']}:</label> {Html::textInput('datasource['|cat:$code|cat:'][shipping]['|cat:$shipping_class|cat:']', $data['value'])}
              </div>
          </div>
        {/foreach}

      </div>
  </div>


    <div class="widget box">
      <div class="widget-header">
          <h4>Products </h4>
          <div class="toolbar no-padding">
            <div class="btn-group">
              <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
          </div>
      </div>


      <div class="widget-content">
          <div class="w-line-row w-line-row-1">
              <div class="wl-td">
                  <label>Default Tax Class</label> {Html::textInput('datasource['|cat:$code|cat:'][products][default_tax_class]', $products['default_tax_class'])}
              </div>
          </div>
      </div>
  </div>

</div>