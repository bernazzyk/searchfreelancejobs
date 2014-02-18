<div class="alert alert-error">
  <button type="button" class="close" data-dismiss="alert" style="font-size: 15px;"><i class="icon-remove"></i></button>
  <h4>Oh snap! <small>please address the following errors:</small></h4>
  {foreach $errors as $error}
  <i class="icon-info-sign"></i> {$error}</br>
  {/foreach}
</div>