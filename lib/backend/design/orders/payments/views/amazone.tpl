<table class="table table-st amazon_payment_details" padding="0" spacing="0">
  <tr>
    <th colspan="2">{$smarty.const.AMAZON_ORDER}</th>
    <th colspan="2">{$smarty.const.AMAZON_AUTHORIZATION}</th>
    <th colspan="2">{$smarty.const.AMAZON_CAPTURE}</th>
    {if !empty($amazonInfo['amazon_refund_id'])}
    <th colspan="2">{$smarty.const.AMAZON_REFUND}</th>
    {/if}
  </tr>
  <tr>
    <td>
        <a title="{htmlspecialchars(IMAGE_UPDATE)}" href='javascript:updateStatus("{htmlspecialchars($amazonInfo['amazon_order_id'])}";)'>{$amazonInfo['amazon_order_id']}</a>
    </td>
    <td>
        <span id='ampOrderStatus'>{$amazonInfo['amazon_status']}</span>
        {if $allowClose}
            <a id="ampCloseLink" href='javascript:amazonClose("{htmlspecialchars($amazonInfo['amazon_order_id'])}";)'>{$smarty.const.IMAGE_CLOSE}</a>
        {/if}
    </td>
    <td>
        <a title="{htmlspecialchars(IMAGE_UPDATE)}" href='javascript:updateAuth("{htmlspecialchars($amazonInfo['amazon_auth_id'])}";)'>{$amazonInfo['amazon_auth_id']}</a>
    </td>
    <td>
        <span id="ampAuthStatus">{$amazonInfo['amazon_auth_status']}</span>
        {if $allowCapture}
            <a id="ampCaptureLink" href='javascript:amazonCapture("{htmlspecialchars($amazonInfo['amazon_auth_id'])}";)'>{$smarty.const.IMAGE_CAPTURE}</a>
        {/if}
    </td>
    <td>
    {if $amazonInfo['amazon_capture_id']}
        <a title="{htmlspecialchars(IMAGE_UPDATE)}" href='javascript:updateCapture("{htmlspecialchars($amazonInfo['amazon_capture_id'])}";)'>{$amazonInfo['amazon_capture_id']}</a>
    {/if}
    </td>
    <td>
        <span id="ampCaptureStatus">{$amazonInfo['amazon_capture_status']}</span>
        {if $allowRefund}
            <a id="ampRefundLink" href='javascript:amazonRefund("{htmlspecialchars($amazonInfo['amazon_capture_id'])}";)'>{$smarty.const.IMAGE_REFUND}</a>
        {/if}
    </td>
    {if !empty($amazonInfo['amazon_refund_id'])}
        <td>
            <a title="{htmlspecialchars(IMAGE_UPDATE)}" href='javascript:updateRefund("{htmlspecialchars($amazonInfo['amazon_refund_id'])}";)'>{$amazonInfo['amazon_refund_id']}</a>
        </td>
        <td>
            <span id="ampCaptureStatus">{$amazonInfo['amazon_refund_status']}</span>
        </td>
    {/if}
  </tr>
  </table>
<style>.amazon_payment_details td:nth-child(2n+1), .amazon_payment_details th { border-left: solid 1px #ddd;} .amazon_payment_details th:last-child, .amazon_payment_details td:last-child { border-right: solid 1px #ddd;}</style>
<pre id="ampLog" style="display:none;max-height:400px; overflow:scroll">{count($amazonLog)} {print_r($amazonLog,1)}</pre>
<script>
function updateStatus(aoid) {
    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
        'ref': aoid, 'operation': 'getOrderDetails'
    }, function (data, status) {
        if (status == "success") {            
            if (data.error == 0) {
              location.reload(); //shipping address could update
            } else {
              if (typeof data.msg != 'undefined')
                alert(data.msg);
            }
        } else {
            alert("Request error.");
        }
    }, "json");
}
function updateAuth(aoid) {
    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
        'ref': aoid, 'operation': 'getAuthDetails'
    }, function (data, status) {
        if (status == "success") {
            if (data.error == 0) {
              location.reload(); //shipping address could update
            } else {
              if (typeof data.msg != 'undefined')
                alert(data.msg);
            }
        } else {
            alert("Request error.");
        }
    }, "json");
}
function updateCapture(aoid) {
    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
        'ref': aoid, 'operation': 'getCaptureDetails'
    }, function (data, status) {
        if (status == "success") {
            if (data.error == 0) {
              location.reload(); //updated order history
            } else {
              if (typeof data.msg != 'undefined')
                alert(data.msg);
            }
        } else {
            alert("Request error.");
        }
    }, "json");
}

function updateRefund(aoid) {
    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
        'ref': aoid, 'operation': 'getRefundDetails'
    }, function (data, status) {
        if (status == "success") {
            if (data.error == 0) {
              location.reload(); //updated order history
            } else {
              if (typeof data.msg != 'undefined')
                alert(data.msg);
            }
        } else {
            alert("Request error.");
        }
    }, "json");
}

function amazonClose(authId) {
    bootbox.dialog({
        message: '{htmlspecialchars(CONFIRM_CLOSE_QUESTION)}',
        title: "{htmlspecialchars(CONFIRM_CLOSE)}",
        buttons: {
            cancel: {
                label: "{$smarty.const.IMAGE_CANCEL}",
                className: "btn-cancel"
            },
            success: {
                label: "{$smarty.const.TEXT_BTN_OK}",
                className: "btn",
                callback: function () {
                    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
                        'ref': authId, 'operation': 'close'
                    }, function (data, status) {
                        if (status == "success") {
                          if (data.error == 0) {
                            location.reload(); //updated order history
                          } else {
                            if (typeof data.msg != 'undefined')
                              alert(data.msg);
                          }
                        } else {
                            alert("Request error.");
                        }
                    }, "json");
                }
            }
        }
    });
    return false;
}

function amazonCapture(authId) {
    bootbox.dialog({
        message: '{htmlspecialchars(CONFIRM_CAPTURE_QUESTION)}<input name="amazon_payment_ca" id="amazon_payment_ca" value="{$order->info['total_inc_tax']}">',
        title: "{htmlspecialchars(CONFIRM_CAPTURE)}",
        buttons: {
            cancel: {
                label: "{$smarty.const.IMAGE_CANCEL}",
                className: "btn-cancel"
            },
            success: {
                label: "{$smarty.const.TEXT_BTN_OK}",
                className: "btn",
                callback: function () {
                    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
                        'ref': authId, 'operation': 'capture', 'amount': $("#amazon_payment_ca").val()
                    }, function (data, status) {
                        if (status == "success") {
                          if (data.error == 0) {
                            location.reload(); //updated order history
                          } else {
                            if (typeof data.msg != 'undefined')
                              alert(data.msg);
                          }
                        } else {
                            alert("Request error.");
                        }
                    }, "json");
                }
            }
        }
    });
    return false;
}
function amazonRefund(ref) {
    bootbox.dialog({
        message: '{$smarty.const.TEXT_REFUND_AMOUNT} <input name="amazon_payment_ra" id="amazon_payment_ra" value="{$order->info['subtotal_inc_tax']}">',
        title: "{$smarty.const.TEXT_INFO_HEADING_REFUND_ORDER}",
        buttons: {
            cancel: {
                label: "{$smarty.const.IMAGE_CANCEL}",
                className: "btn-cancel"
            },
            success: {
                label: "{$smarty.const.TEXT_BTN_OK}",
                className: "btn",
                callback: function () {
                    $.post("{tep_catalog_href_link('callback/amazon-update', '', 'SSL', $order->info['platform_id'])}", {
                                'ref': ref, 'operation': 'refund', 'amount': $("#amazon_payment_ra").val()
                    }, function (data, status) {
                        if (status == "success") {
                          if (data.error == 0) {
                            location.reload(); //updated order history
                          } else {
                            if (typeof data.msg != 'undefined')
                              alert(data.msg);
                          }
                        } else {
                            alert("Request error.");
                        }
                    }, "json");
                }
            }
        }
    });
    return false;
}
</script>
{if !$amazonInfo['address_full']}
    <div class="alert alert-danger" id="ampFullAddress">{$smarty.const.NOT_FULL_ADDRESS}</div>
{/if}