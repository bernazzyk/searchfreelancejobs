<div class="page-header">
  <h1>Contracts <small>{if $job}[Job: #{$job->reference}: {$job->title}]{else}[All Jobs]{/if}</small></h1>
  <div class="subnav">
    <ul class="nav nav-pills">
      <li class=""><a class="" href="./?action=contracts{if $job}&job={$job->reference}{/if}"><i class="icon-repeat"></i> Refresh contracts</a></li>
    </ul>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
  <div class="well">
    <h2>
      <i class="icon-question-sign"></i> Help
      {if $job}
      <small>Contracts for job: <strong>#{$job->reference}</strong>, <u>({$job->title})</u> under <strong>{$smarty.const.OD_COMPANY}</strong> account.</small>
      {else}
      <small>Contracts for jobs under <strong>{$smarty.const.OD_COMPANY}</strong> account.</small>
      {/if}
    </h2>
    <div class="row-fluid">
      <div class="span6">
        <p><strong><u>View contract info: </u></strong><br/> Debuging only!</p>
        <p><strong><u>Send message to contractor: </u></strong><br/> bla bla bla ...</p>
        <p><strong><u>Synchronize contract: </u></strong><br/> bla bla bla ...</p>
      </div>
      <div class="span6">
        <p><strong><u>Send message to contractor with payment info: </u></strong><br/> bla bla bla ...</p>
        <p><strong><u>Pay contract: </u></strong><br/> bla bla bla ...</p>
        <p><strong><u>Close contract: </u></strong><br/> bla bla bla ...</p>
      </div>
    </div>
  </div>
    <ul class="nav nav-pills">
      <li class="{if $status == 'all'}active{/if}"><a href="./?action=contracts&status=all">All</a></li>
      <li class="{if $status == 'active'}active{/if}"><a href="./?action=contracts&status=active">Active</a></li>
      <li class="{if $status == 'closed'}active{/if}"><a href="./?action=contracts&status=closed">Closed</a></li>
    </ul>
    <table class="table">
      <thead>
        <tr>
          <th>Engagement</th>
          <th>Job</th>
          <th>Offer</th>
          <th width="60px">Price</th>
          <th width="80px">Created</th>
          <th width="80px">Start</th>
          <th width="80px">End</th>
          <th width="50px">Status</th>
          <th width="10x"><i class="icon-refresh"></i></th>
          <th width="50x"></th>
        </tr>
      </thead>
      <tbody id="engagementslist">
      </tbody>
    </table>
  </div>
</div>
<script>
  var job = null;
  {if $job}
  var job = {$job->reference};
  {/if}
  var _status = '{$status}';
</script>
<script type="text/javascript" src="resources/js/contracts.js"></script>