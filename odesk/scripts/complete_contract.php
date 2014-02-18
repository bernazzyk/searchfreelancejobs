<?php
if (!isset($_REQUEST['p'])){
  exit;
}

$request = Application::requestCompleteContract($_REQUEST['p']);

if ($request) {
  $content = sprintf(<<<EOF
<div class='alert alert-success'>
<strong>Success!</strong><br/>
Thank you for completed your task!!
Your payment request was delivered successfully at %s.
</div>
EOF
   ,date('Y-m-d H:i:s', time())
);
} else {
  $content = <<<EOF
<div class='alert alert-error'>
<strong>Error!</strong><br/>
Some error took place and couldn't process your request!
Please contact us for consulting.
</div>
EOF;
}