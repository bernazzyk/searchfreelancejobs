{if $total > 0 }
  {foreach $jobs as $job}
  <tr>
    <td>
      <a href="{$job->public_url}" target="_blank">{$job->title}</a>
    </td>
    <td>
      {$job->created_time|convertunix:"Y-m-d H:i"}
    </td>
    <td style="text-align: right;">
      {if $job->count_total_applicants > 0}
      <span class="label label-success">{$job->count_total_applicants}</span>
      {else}
      <span class="label">{$job->num_active_candidates}</span>
      {/if}
    </td>
    <td>
      {if $job->status == 'cancelled'}
      <span class="label label-warning">{$job->status}</span>
      {elseif $job->status == 'open'}
      <span class="label label-success">{$job->status}</span>
      {else}
      <span class="label">{$job->status}</span>
      {/if}
    </td>
    <td>
      <div class="btn-group">
        <a class="btn btn-mini" href="{$job->public_url}" target="_blank" title="View job online"><i class="icon-search"></i> online</a>
        <a class="btn btn-mini btn-primary" href="./?action=offers&job={$job->reference}" title="View job's offers"><i class="icon-search"></i> offers</a>
        {if $job->status == 'open'}<a class="btn btn-mini btn-danger cncl_j" title="Cancel job" data-remote="./index.php?action=job_cancel&id={$job->reference}"><i class="icon-remove"></i></a>{/if}
      </div>
    </td>
  </tr>
  {/foreach}
  {if $total >= 10}
    <tr>
      <td colspan="5">
        <a id="showmore-jobs" class="btn btn-primary" href="#" rel="{$last+$total}"><i class="icon-download"></i> Load more</a>
      </td>
    </tr>
  {/if}
{else}
{if $last == 0}
<tr>
  <td colspan="5">
    No jobs!!!
  </td>
</tr>
{/if}
{/if}