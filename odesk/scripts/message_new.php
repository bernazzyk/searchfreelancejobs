<?php
$values = array(
  'recipients' => $_REQUEST['rec'] ? $_REQUEST['rec'] : '',
  'subject' => $_REQUEST['sub'] ? $_REQUEST['sub'] : '',
  'body' => $_REQUEST['body'] ? $_REQUEST['body'] : '',
);
if ($_REQUEST['salt']) {
  $values['body'] = sprintf(<<<EOF
Dear %s,
  
After you completed your task, you need to visit the following url to request payment.

[ %s ]

Best regard,
%s

EOF
  ,
  $_REQUEST['rec'],
  str_replace("\n", '', $helper->makeBitlyUrl(BASE_URL . '?action=complete_contract&p=' . $_REQUEST['salt'], BITLY_LOGIN, BITLY_APK)),
  $application->getUser()->auth_user->uid
  );
}
$smarty->assign('values', $values);
$content = $smarty->fetch('message_new.tpl');

die($content);
