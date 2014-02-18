<?php

/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {calc_status} plugin
 *
 * Type:     function<br>
 * Name:     calculate status<br>
 *
 * A couple examples. In order to determine:
 * 1) Who applied to the job
 * ( candidacy_status must be eq. to "in_process" and interview_status eq. to "waiting_for_buyer"
 *
 * 2) Who has been invited and not yet responded
 * candidacy_status eq. "in_process" and interview_status eq. "waiting_for_provider" and has_buyer_signed is true
 *
 * 3) Who has been invited and accepted 
 * candidacy_status eq. 'filled'
 *
 * 4) Who has been invited and rejected 
 * interview_status eq, "waiting_for_provider" and  candidacy_status eq. "cancelled" and has_buyer_signed is true
 *
 * * The above is true, in case you used "Make Offer" API
 *
 * In case you use "Invite to Interview" API, the conditions will look like:
 * 1) Invited and accepted:
 * candidacy_status eq. 'in_process' and interview_status eq. 'in_process' and has_provider_signed is true
 *
 * 2) invited and not responded:
 * candidacy_status eq. 'in_process' and interview_status eq. 'waiting_for_provider' and has_buyer_signed is false
 *
 * 3) invited and rejected (means employer invited, contractor rejected)
 * interview_status eq. 'waiting_for_provider' and candidacy_status eq. 'cancelled' and has_buyer_signed is false
 *
 * 4) Who accepted invitation and then withdraw it. (again, employer invited, contractor accepted, then contractor rejected)
 * interview_status eq. 'in_progress' and candidacy_status eq. 'cancelled'
 *
 * One more note as for description for the values of <has_buyer_signed> and <has_provider_signed>. 
 * Q: In what specific cases will these two nodes be 1 or 0?
 * A: in our workflows, has_buyer_signed will be true for offer and active discussion based on offer (offer -> discuss). 
 * has_provider_signed will be true for application (contractor applied), active discussion based on application (contractor applied, 
 * employer interviewed), active discussion based on invitation (employer invited, contractor accepted)
 */
function smarty_function_calc_status($params = array())
{
  $status = 'Archived';
  $code = 'label-inverse';
  
  if (empty($params['candidacy_status'])) {
    trigger_error("[plugin] calculate status 'candidacy_status' cannot be empty", E_USER_NOTICE);
    return;
  }
  if (empty($params['interview_status'])) {
    trigger_error("[plugin] calculate status 'interview_status' cannot be empty", E_USER_NOTICE);
    return;
  }
//  if (empty($params['has_buyer_signed'])) {
//    trigger_error("[plugin] calculate status 'has_buyer_signed' cannot be empty", E_USER_NOTICE);
//    return;
//  }
//  if (empty($params['has_provider_signed'])) {
//    trigger_error("[plugin] calculate status 'has_provider_signed' cannot be empty", E_USER_NOTICE);
//    return;
//  }
//  if (empty($params['status'])) {
//    trigger_error("[plugin] calculate status 'status' cannot be empty", E_USER_NOTICE);
//    return;
//  }
  
  if($params['candidacy_status'] == 'in_process' && $params['interview_status'] == 'waiting_for_buyer') {
    $status = 'in process';
    $code = '';
  }
  if($params['candidacy_status'] == 'in_process' && $params['interview_status'] == 'waiting_for_provider' && $params['has_buyer_signed'] == '1') {
    $status = 'Invited';
    $code = 'label-warning';
  }
  if($params['candidacy_status'] == 'in_process' && $params['interview_status'] == 'waiting_for_provider' && $params['has_buyer_signed'] == '0') {
    $status = 'Pending';
    $code = 'label-warning';
  }
  if($params['candidacy_status'] == 'in_process' && $params['interview_status'] == 'in_process' && $params['has_provider_signed'] == '1') {
    $status = 'Accepted';
    $code = 'label-success';
  }
  if($params['candidacy_status'] == 'filled') {
    $status = 'Accepted';
    $code = 'label-success';
  }
  if($params['candidacy_status'] == 'cancelled' && $params['interview_status'] == 'waiting_for_provider') {
    $status = 'Rejected by provider';
    $code = 'label-important';
  }
  
  return '<span class="label ' . $code . '">' . $status . '<span>';
}

?>