<div class="row-fluid">
  <div class="span12">  
    <div id="errors"></div>
    <form class="form-horizontal" onsubmit="" method="post" action="./?action=contract_pay">
      <input type="hidden" name="submit" value="true"/>
      <input type="hidden" name="contract_reference" value="{$values.contract_reference}"/>
      <fieldset id="sf_fieldset_none">
        <div class="control-group {if $errors['charge_amount']}error{/if}">
          <label class="control-label" for="charge_amount"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Payment amount</label>          
          <div class="controls">
            <div class="input-prepend">
              <span class="add-on">$</span><input name="charge_amount" class="span6 currency" id="charge_amount" type="text" value="{$values.charge_amount}">
            </div>
            {if $errors['charge_amount']}<span class="help-inline">{$errors['charge_amount']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> 
              Contract {$values.contract_reference} has agreed charge amount of 
              <span class="label label-warning">$ {$values.charge_amount}</span>. You can pay more as a bonus payment or the agreed price.
            </span>
          </div>
        </div>
        <div class="control-group {if $errors['comments']}error{/if}">
          <label class="control-label" for="comments"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Comments</label>          
          <div class="controls">
            <textarea class="span12" rows="3" name="comments" id="body">{$values.comments}</textarea>  
            {if $errors['comments']}<span class="help-inline">{$errors['comments']}</span>{/if}
          </div>
        </div>
        <div class="control-group {if $errors['notes']}error{/if}">
          <label class="control-label" for="notes">Notes</label>          
          <div class="controls">
            <textarea class="span12" rows="3" name="notes" id="body">{$values.notes}</textarea>  
            {if $errors['notes']}<span class="help-inline">{$errors['notes']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> 
              Any private notes for the payment 
            </span>
          </div>
        </div>
      </fieldset>
    </form>
    <div class="well well-small"><sup style="color: red;"><i class="icon-asterisk"></i></sup> : Required field.</div>
  </div>  
</div>