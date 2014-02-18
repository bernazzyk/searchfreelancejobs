{if $errors}
<div class="alert alert-error">
  <button type="button" class="close" data-dismiss="alert" style="font-size: 15px;"><i class="icon-remove"></i></button>
  <h4>Oh snap! <small>please address the following errors:</small></h4>
  {foreach $errors as $error}
  <i class="icon-info-sign"></i> {$error}</br>
  {/foreach}
</div>
{/if}
<div class="page-header">
  <h1>Post new Job <small>on oDesk <sup>1</sup></small></h1>
</div>

<div class="row-fluid">
  <div class="span9">
    <form class="form-horizontal" onsubmit="" method="post" action="./index.php?action=new_job">      
      <fieldset id="sf_fieldset_none">
<!--        <h2>Job Options <small><sup>1</sup></small></h2>-->
        <div class="control-group {if $errors['title']}error{/if}">
          <label class="control-label" for="title"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Title</label>          
          <div class="controls">
            <input class="span9" type="text" name="title" id="title" value="{$values.title}">
            {if $errors['title']}<span class="help-inline">{$errors['title']}</span>{/if}
          </div>
        </div>
        <div class="control-group {if $errors['description']}error{/if}">
          <label class="control-label" for="description"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Description</label>          
          <div class="controls">
            <textarea class="span12" rows="6" name="description" id="description">{$values.description}</textarea>  
            {if $errors['description']}<span class="help-inline">{$errors['description']}</span>{/if}
          </div>
        </div>
        <div class="control-group {if $errors['category']}error{/if}">
          <label class="control-label" for="category"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Category</label>          
          <div class="controls">
            <select id="category" name="category">
              <option value="">--</option>
              {foreach $categories as $category}
              <option {if $values.category == $category->title}selected{/if} value="{$category->title}">{$category->title}</option>
              {/foreach}
            </select>
            {if $errors['category']}<span class="help-inline">{$errors['category']}</span>{/if}
          </div>
        </div>
        <div class="control-group {if $errors['subcategory']}error{/if}">
          <label class="control-label" for="subcategory"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Subcategory</label>          
          <div class="controls">
            <select id="subcategory" name="subcategory">
              <option value="">--</option>
              {foreach $categories as $category}
              {foreach $category->topics as $subcategory}
              <option {if $values.subcategory == $subcategory->title}selected{/if} value="{$subcategory->title}" class="{$category->title}">{$subcategory->title}</option>
              {/foreach}
              {/foreach}
            </select>
            {if $errors['subcategory']}<span class="help-inline">{$errors['subcategory']}</span>{/if}
          </div>
        </div>
        <div class="control-group ">
          <label class="control-label" for="job_type"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Type</label>          
          <div class="controls">
            <select id="type" name="job_type">
              <option value="fixed-price">Fixed price</option>
            </select>
          </div>
        </div>
        <div class="control-group ">
          <label class="control-label" for="visibility"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Visibility</label>          
          <div class="controls">
            <select id="visibility" name="visibility">
              <option {if $values.visibility == 'public'}selected{/if} value="public">Public</option>
              <option {if $values.visibility == 'private'}selected{/if} value="private">Private</option>
              <option {if $values.visibility == 'odesk'}selected{/if} value="odesk">oDesk</option>
              <option {if $values.visibility == 'invite-only'}selected{/if} value="invite-only">Invite only</option>
            </select>
            <span class="help-block"><i class="icon-question-sign"></i> 
              <strong>Public</strong>: jobs are available to all users who search jobs.<br/>
              <strong>Private</strong>: job is visible to employer only.<br/>
              <strong>oDesk</strong>: jobs appear in search results only for oDesk users who are logged into the service.<br/>
              <strong>Invite only</strong>: jobs do not appear and search and are used for jobs where the buyer wants to control the potential applicants. 
            </span>
          </div>
        </div>
        <div class="control-group {if $errors['start_date']}error{/if}">
          <label class="control-label" for="start_date">Start date</label>          
          <div class="controls">
            <div class="input-append date datepicker" data-date="{if $values.start_date}{$values.start_date}{else}{$todayte}{/if}" data-date-format="mm-dd-yyyy">
              <input name="start_date" id="start_date" class="span9" size="16" type="text" value="{if $values.start_date}{$values.start_date}{/if}"><span class="add-on"><i class="icon-calendar"></i></span></div>
            {if $errors['start_date']}<span class="help-inline">{$errors['start_date']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> The start date of the Job, e.g. 06-15-2011. If start date is not included the job will default to starting immediately.</span>
          </div>
        </div>
        <div class="control-group {if $errors['end_date']}error{/if}">
          <label class="control-label" for="end_date"><sup style="color: red;"><i class="icon-asterisk"></i></sup> End date</label>          
          <div class="controls">
            <div class="input-append date datepicker" data-date="{if $values.end_date}{$values.end_date}{else}{$todayte}{/if}" data-date-format="mm-dd-yyyy">
              <input name="end_date" id="end_date" class="span9" size="16" type="text" value="{if $values.end_date}{$values.end_date}{/if}" ><span class="add-on"><i class="icon-calendar"></i></span></div>
            {if $errors['end_date']}<span class="help-inline">{$errors['end_date']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> The end date of the Job, e.g. 06-15-2011.</span>
          </div>
        </div>
        <div class="control-group {if $errors['budget']}error{/if}">
          <label class="control-label" for="budget"><sup style="color: red;"><i class="icon-asterisk"></i></sup> Budget</label>          
          <div class="controls">
            <div class="input-prepend">
              <span class="add-on">$</span><input name="budget" class="span6 currency" id="budget" type="text" value="{$values.budget}">
            </div>
            {if $errors['budget']}<span class="help-inline">{$errors['budget']}</span>{/if}
            <span class="help-block"><i class="icon-question-sign"></i> Minimum budget is 5 US Dollars.</span>
          </div>
        </div>
      </fieldset>
      <p></p>
      <div class="form-actions span12">
        <input name="submit" type="submit" value="Submit" class="btn btn-success btn-large">               
        <a class="btn  btn-danger btn-large  pull-right" href="./?action=jobs">Cancel</a>            
      </div>
    </form>
  </div>
  <div  class="span3 admin_bar admin_bar_resource">
    <div class="well">
      <h2><i class="icon-question-sign"></i> Help</h2>
      <p>Fill in the form to post a new "Fixed Price" Job on oDesk platform under <strong>{$smarty.const.OD_COMPANY}</strong> account.</p>
      <p><sup>1</sup>: Via this form you can post a job under your account in oDesk. <strong>Advance options</strong> are available <u>only in oDesk site application</u>. If you wish to further edit your job, visit the job post in oDesk site after posting through this form.</p>
      <p><sup style="color: red;"><i class="icon-asterisk"></i></sup> : Required field.</p>
    </div>
  </div>
</div>