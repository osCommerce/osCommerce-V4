<div class="creditHistoryPopup">
    <table class="table table-striped table-hover table-responsive table-ordering no-footer" order_list="3" order_by="desc">
              <thead>
              <tr>
                  <th>Date Added</th>
                  <th>Credit</th>
                  <th>Customer Notified</th>
                  <th>Comments</th>
                  <th> Processed by</th>
              </tr>
              </thead>
              <tbody>
                  {foreach $history as $Item}
                  <tr>
                      <td>{$Item['date']}</td>
                      <td>{$Item['credit']}</td>
                      <td><i class="icon-{if $Item['notified'] == 1}checked{else}close{/if}">&nbsp;</i></td>
                      <td>{$Item['comments']}</td>
                      <td>{$Item['admin']}</td>
                  </tr>
                  {/foreach}
              </tbody>
            </table>
</div>