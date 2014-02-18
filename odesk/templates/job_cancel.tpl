<div class="row-fluid">
  <div class="span12">  
    <div id="errors"></div>
    <form class="form-horizontal" onsubmit="" method="post" action="./?action=job_cancel">
      <input type="hidden" name="submit" value="true"/>
      <input type="hidden" name="job" value="{$values.job}"/>
      <fieldset id="sf_fieldset_none">
        <div class="control-group ">
          <label class="control-label" for="reason_code"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Reason</label>          
          <div class="controls">
            <select id="reason" name="reason_code" class="span12">
              <option {if $values.reason_code == '67'}selected{/if} value="67">Accidental opening creation</option>
              <option {if $values.reason_code == '51'}selected{/if} value="51">All positions filled </option>
              <option {if $values.reason_code == '49'}selected{/if} value="49">Filled by alternate source </option>
              <option {if $values.reason_code == '41'}selected{/if} value="41">Project was cancelled</option>
              <option {if $values.reason_code == '34'}selected{/if} value="34">No developer for requested skills</option>
            </select>
            <span class="help-block"><i class="icon-question-sign"></i> 
              <strong>The reason</strong> you are canceling this job.<br/>
            </span>
          </div>
        </div>
      </fieldset>
    </form>
    <p class="well well-small"><sup style="color: red;"><i class="icon-asterisk"></i></sup> : Required field.</p>
  </div>  
</div>