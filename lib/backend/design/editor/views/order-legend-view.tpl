<div class="popup-heading">{$smarty.const.TEXT_ORDER_LEGEND}</div>
<div class="popup-content pop-mess-cont">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
          <li class="active" data-bs-toggle="tab" data-bs-target="#history"><a><span>{$smarty.const.TEXT_ORDER_HISTORY}</span></a></li>
          <li data-bs-toggle="tab" data-bs-target="#marketing"><a><span>{$smarty.const.TEXT_MARKETING}</span></a></li>
          <li data-bs-toggle="tab" data-bs-target="#contacts"><a><span>{$smarty.const.T_CONTACT}</span></a></li>
          <li data-bs-toggle="tab" data-bs-target="#info"><a><span>{$smarty.const.IMAGE_DETAILS}</span></a></li>
          <li data-bs-toggle="tab" data-bs-target="#errors"><a><span>{$smarty.const.TEXT_ERRORS}</span></a></li>
        </ul>
          <div class="tab-content" id="">
           <div id="history" class="tab-pane active">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font">
                    {foreach $history as $Item}
                    <tr>
                        <td><b>{$Item['date']}</b></td>
                        <td>{$Item['comments']}</td>
                        <td>{$Item['admin']}</td>
                    </tr>
                    {/foreach}
                </table>
            </div>                          
            <div id="marketing" class="tab-pane">
                <table border="0" cellspacing="0" cellpadding="2"  class="main" width="100%">
                  <tr><td width="50%">{$smarty.const.TEXT_CUSTOMER_ORIGIN}:</td><td>{$ga['origin']}</td></tr>
                  <tr><td>{$smarty.const.TEXT_COMPAING}:</td><td>{$ga['utmccn']}</td></tr>
                  <tr><td>{$smarty.const.HEADING_TITLE_SEARCH}</td><td>{$ga['utmcmd']}</td></tr>
                  <tr><td>{$smarty.const.TEXT_SEARCH_KEY}:</td><td>{$ga['utmctr']}</td></tr>
                </table>  
            </div>
            <div id="contacts" class="tab-pane">
                <table border="0" cellspacing="0" cellpadding="2"  class="main" width="100%">
                  <tr><td width="50%">Returned Customer:</td><td>{$scart['recovered']}</td></tr>
                  <tr><td>{$smarty.const.TEXT_CONTACTED}:</td><td>{$scart['contacted']}</td></tr>
                  <tr><td>{$smarty.const.TEXT_WORKEDOUT}:</td><td>{$scart['workedout']}</td></tr>
                  {if is_array($coupons)}
                    {foreach $coupons as $cop}
                      <tr><td>{$cop['coupon_type']}:</td><td>{$cop['coupon_amount']}</td></tr>
                    {/foreach}
                  {/if}
                  <tr><td>{$smarty.const.TEXT_NOTE}:</td><td>{$scart['note']}</td></tr>
                </table>
            </div>
            <div id="info" class="tab-pane">
              <table border="0" cellspacing="0" cellpadding="2" class="main" width="100%">
                <tr><td width="50%">IP:</td><td>{$ga['ip_address']}</td></tr>
                <tr><td>{$smarty.const.TEXT_BROWSER}:</td><td>{$ua->agent_name}</td></tr>
                <tr><td>{$smarty.const.TEXT_OPERATING_SYSTEM}:</td><td>{$ua->os_name}</td></tr>
                <tr><td>{$smarty.const.TEXT_SCREEN_RESOLUTION}:</td><td>{$ga['resolution']}</td></tr>
                <tr><td>{$smarty.const.TEXT_JAVA_SUPPORT}:</td><td>{$ga['java']}</td></tr>
              </table>
            </div>
            <div id="errors" class="tab-pane">
              <table border="0" cellspacing="0" cellpadding="2" class="main" width="100%">
               <tr>
                <td width="15%"><b>{$smarty.const.HEADING_TYPE}</b></td>
                <td width="25%"><b>{$smarty.const.TEXT_TITLE_}</b</td>
                <td  width="35%"><b>{$smarty.const.TABLE_HEADING_COMMENTS}</b</td>
                <td width="25%"><b>{$smarty.const.TEXT_DATE_ADDED}</b</td>
               </tr>                              
               {foreach $errors as $_e}                               
               <tr>
                <td width="15%">{$_e['error_entity']}</td>
                <td width="25%">{$_e['error_title']}</td>
                <td  width="35%">{$_e['error_message']}</td>
                <td width="25%">{date(DATE_FORMAT, strtotime($_e['error_date']))}</td>
               </tr>
               {/foreach}
              </table>
            
            </div>
            
          </div>
        </div>
</div>
<div class="note-block noti-btn">
  <div></div>
  <div><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_OK}</span></div>
</div>
