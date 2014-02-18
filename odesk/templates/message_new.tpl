<div class="row-fluid">
  <div class="span12">  
    <div id="errors"></div>
    <form class="form-horizontal" onsubmit="" method="post" action="./?action=message_send">      
      <fieldset id="sf_fieldset_none">
        <div class="control-group {if $errors['recipients']}error{/if}">
          <label class="control-label" for="recipients"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Recipients</label>          
          <div class="controls">
            <input class="span9" type="text" name="recipients" id="recipients" value="{$values.recipients}">
            {if $errors['recipients']}<span class="help-inline">{$errors['recipients']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> The <b>userid</b> of the intended recipients, use comma (",") to separate ids in list.</span>
          </div>
        </div>
        <div class="control-group {if $errors['subject']}error{/if}">
          <label class="control-label" for="subject"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Subject</label>          
          <div class="controls">
            <input class="span9" type="text" name="subject" id="subject" value="{$values.subject}">
            {if $errors['subject']}<span class="help-inline">{$errors['subject']}</span>{/if}
          </div>
        </div>
        <div class="control-group {if $errors['body']}error{/if}">
          <label class="control-label" for="body"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Body</label>          
          <div class="controls">
            <textarea class="span12" rows="6" name="body" id="body">{$values.body}</textarea>  
            {if $errors['body']}<span class="help-inline">{$errors['body']}</span>{/if}
          </div>
        </div>
      </fieldset>
    </form>
    <p style="text-align: right;"><sup style="color: red;"><i class="icon-asterisk"></i></sup> : Required field.</p>
  </div>  
</div>