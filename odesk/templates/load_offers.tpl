{if $total > 0 }
  {foreach $offers as $offer}
    <tr>
      <td>
        <a href="{$offer->provider__profile_url}" target="_blank">{$offer->provider__name}</a>
      </td>
      <td>
        {$offer->job__title}
      </td>
      <td>
        $ {$offer->fixed_charge_amount_agreed}
      </td>
      <td>
        {$offer->country}
      </td>
      <td>
        {$offer->estimated_duration}
      </td>
      <td>
        {calc_status 
          candidacy_status=$offer->candidacy_status 
          interview_status=$offer->interview_status 
          has_buyer_signed=$offer->has_buyer_signed 
          has_provider_signed=$offer->has_provider_signed 
          status=$offer->status
        }
<!--        {if $offer->status}
          {if $offer->status == 'rejected'}
          <span class="label label-important">Rejected</span>
          {elseif $offer->status == 'open'}
          <span class="label label-info">Open</span>
          {elseif $offer->status == 'accepted'}
          <span class="label label-success">Accepted</span>
          {elseif $offer->status == 'cancelled'}
          <span class="label label-warning">Canceled</span>
          {else}
            <span class="label">{$offer->status}</span>
          {/if}
        {else}
          <span class="label">no candidacy</span>
        {/if}-->
      </td>
      <td>
        <a class="btn btn-mini btn-success" target="_blank" title="review online" href="https://www.odesk.com/applications/{$offer->reference}"><i class="icon-eye-open"></i></a>
      </td>
    </tr>
  {/foreach}
  {if $total >= 10}
    <tr>
      <td colspan="7">
        <a id="showmore-offers" class="btn btn-primary" href="#" rel="{$last+$total}{if $job}&job={$job}{/if}"><i class="icon-download"></i> Load more</a>
      </td>
    </tr>
  {/if}
{else}
{if $last == 0}
<tr>
  <td colspan="7">
    No offers!!!
  </td>
</tr>
{/if}
{/if}