<div class="row-fluid">
  <div class="span12">  
    <div id="errors"></div>
    <form class="form-horizontal" onsubmit="" method="post" action="./?action=contract_close">
      <input type="hidden" name="submit" value="true"/>
      <input type="hidden" name="contract_reference" value="{$values.contract_reference}"/>
      <fieldset id="sf_fieldset_none">
        <div class="control-group ">
          <label class="control-label" for="reason"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Reason</label>          
          <div class="controls">
            <select id="reason" name="reason" class="span12">
              <option {if $values.visibility == 'API_REAS_MISREPRESENTED_SKILLS'}selected{/if} value="API_REAS_MISREPRESENTED_SKILLS">Contractor misrepresented his/her skills</option>
              <option {if $values.visibility == 'API_REAS_CONTRACTOR_NOT_RESPONSIVE'}selected{/if} value="API_REAS_CONTRACTOR_NOT_RESPONSIVE">Contractor not responsive</option>
              <option {if $values.visibility == 'API_REAS_HIRED_DIFFERENT'}selected{/if} value="API_REAS_HIRED_DIFFERENT">Hired a different contractor</option>
              <option {if $values.visibility == 'API_REAS_JOB_COMPLETED_SUCCESSFULLY'}selected{/if} value="API_REAS_JOB_COMPLETED_SUCCESSFULLY">Job was completed successfully</option>
              <option {if $values.visibility == 'API_REAS_WORK_NOT_NEEDED'}selected{/if} value="API_REAS_WORK_NOT_NEEDED">No longer need this work completed</option>
              <option {if $values.visibility == 'API_REAS_UNPROFESSIONAL_CONDUCT'}selected{/if} value="API_REAS_UNPROFESSIONAL_CONDUCT">Unprofessional conduct</option>
            </select>
            <span class="help-block"><i class="icon-question-sign"></i> 
              <strong>The reason</strong> you ending this contract.<br/>
            </span>
          </div>
        </div>
        <div class="control-group {if $errors['would_hire_againnotes']}error{/if}">
          <label class="control-label" for="would_hire_againnotes"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Would hire again?</label>          
          <div class="controls">
            <label class="radio inline">
              <input type="radio" name="would_hire_againnotes" id="yes" value="yes" checked>
              Yes
            </label>
            <label class="radio inline">
              <input type="radio" name="would_hire_againnotes" id="no" value="no">
              No
            </label>
          </div>
        </div>
      </fieldset>
    </form>
    <p class="well well-small"><sup style="color: red;"><i class="icon-asterisk"></i></sup> : Required field.</p>
  </div>  
</div>