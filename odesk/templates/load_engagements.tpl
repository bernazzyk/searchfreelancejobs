{if $total > 0 }
  {foreach $engagements as $engagement}
  <tr>
    <td>
      <a href="https://www.odesk.com/e/{$team_ref->reference}/contracts/{$engagement->reference}" target="_blank">{$engagement->engagement_title}</a>
    </td>
    <td>
      <a href="https://www.odesk.com/jobs/{$engagement->job__reference}/applications/applied" target="_blank">{$engagement->job__title}</a>
    </td>
    <td>
      <a href="https://www.odesk.com/applications/{$engagement->offer__reference}" target="_blank">{$engagement->provider__id}</a>
    </td>
    <td>
      $ {$engagement->fixed_charge_amount_agreed}
    </td>
    <td>
      {$engagement->created_time|convertunix:"Y-m-d"}
    </td>
    <td>
      {$engagement->engagement_start_date|convertunix:"Y-m-d"}
    </td>
    <td>
      {if $engagement->engagement_end_date}{$engagement->engagement_end_date|convertunix:"Y-m-d"}{/if}
    </td>
    <td>
      <span class="label {if $engagement->status == 'active'}label-success{/if} {if $engagement->status == 'closed'}label-important{/if}">{$engagement->status}</span>
    </td>
    <td>
      {if $engagement->synced}
      <a href="#" class="popovertrigger" 
          rel="popover" 
          {if $engagement->requested_at && !$engagement->paid_at && !$engagement->closed_at}style="color:red;"{/if} 
          data-html="true" 
          data-title="<h5>Locally:</h5>" 
          data-content="
            <strong>Salt: </strong><span class='label'>{$engagement->synced}</span><br/>
            <strong>Synchronized: </strong>{$engagement->synchronized_at}<br/>
            <strong>Requested: </strong><span style='color:red;'>{$engagement->requested_at}</span><br/>
            <strong>Paid: </strong>{$engagement->paid_at}<br/>
            <strong>Closed: </strong>{$engagement->closed_at}<br/>
          "><i class="icon-info-sign"></i>
      </a>
      {/if}
    </td>
    <td>
      <div class="btn-group">
        <a class="btn btn-mini info_c" title="Info" data-remote="./index.php?action=contract&id={$engagement->reference}"><i class="icon-eye-open"></i></a>
        <a class="btn btn-mini btn-success s_message" title="Send message" data-remote="./index.php?action=message_new&rec={$engagement->provider__id}"><i class="icon-envelope"></i></a>
        <a class="btn btn-mini sync {if $engagement->synced}disabled{/if}" href="#" rel="&engagement={$engagement->reference}&contractor={$engagement->provider__id}" title="Synchronize contract"><i class="icon-refresh"></i></a>
        <a class="btn btn-mini btn-primary p_message {if !$engagement->synced}disabled{/if} {if $engagement->status == 'closed'}disabled{/if}" title="Send payment info message" data-remote="./index.php?action=message_new&rec={$engagement->provider__id}&salt={$engagement->synced}&sub=Payment%20Info"><i class="icon-envelope"></i></a>
        <a class="btn btn-mini btn-warning pay_c {if $engagement->status == 'closed'}disabled{/if}" title="Pay contractor" data-remote="./index.php?action=contract_pay&id={$engagement->reference}&amount={$engagement->fixed_charge_amount_agreed}"><i class="icon-money"></i></a>
        <a class="btn btn-mini btn-danger cls_c {if $engagement->status == 'closed'}disabled{/if}" title="Close contract" data-remote="./index.php?action=contract_close&id={$engagement->reference}"><i class="icon-remove"></i></a>
      </div>
    </td>
  </tr>
  {/foreach}
  {if $total >= 10}
    <tr>
      <td colspan="10">
        <a id="showmore-engagements" class="btn btn-primary" href="#" rel="{$last+$total}"><i class="icon-download"></i> Load more</a>
      </td>
    </tr>
  {/if}
{else}
{if $last == 0}
<tr>
  <td colspan="10">
    No contracts!!!
  </td>
</tr>
{/if}
{/if}