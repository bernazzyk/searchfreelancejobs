{if $message}
<div class="alert alert-{$message['type']}">
  <button type="button" class="close" data-dismiss="alert" style="font-size: 15px;"><i class="icon-remove"></i></button>
  {$message['body']}
</div>
{/if}