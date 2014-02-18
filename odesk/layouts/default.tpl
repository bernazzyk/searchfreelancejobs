<!DOCTYPE html >
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="title" content="CMDB for AM team">
    <title>Aesthetics oDesk App</title>

    <link rel="stylesheet" type="text/css" media="all" href="resources/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="resources/css/greenish.css">
    <link rel="shortcut icon" href="/favicon.ico"><link rel="stylesheet" type="text/css" media="all" href="resources/css/bootstrap-responsive.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="resources/css/font-awesome.css">
    <!--[if lt IE 8]><link rel="stylesheet" type="text/css" media="screen" href="resources/css/font-awesome-ie7.css" /><![endif]-->
    <link rel="stylesheet" type="text/css" media="all" href="resources/css/bootstrap-datepicker.css">
    <link rel="stylesheet" type="text/css" media="all" href="resources/css/main.css">
    <script type="text/javascript" src="resources/js/spin.min.js"></script>
    <script type="text/javascript" src="resources/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="resources/js/prettify.js"></script>
    <script type="text/javascript" src="resources/js/jquery.spin.js"></script>
    <script type="text/javascript" src="resources/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="resources/js/bootbox.js"></script>
    <script type="text/javascript" src="resources/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="resources/js/jquery.form.js"></script>
    <script type="text/javascript" src="resources/js/jquery.maskMoney.js"></script>
    <script type="text/javascript" src="resources/js/jquery.chained.js"></script>
    <script type="text/javascript" src="resources/js/application.js"></script>
  </head>
  <body data-spy="scroll" data-target=".bs-docs-sidebar">
    <div id="busy">
      <div id="busy-loader"></div>
      <div id="busy-msg">Please wait</div>
    </div>
    <!-- NAVBAR -->
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="./?action=index"><span>Aesthetics</span> <span style="font-size: 13px;">[oDesk App]</span></a>
          <div class="nav-collapse">
            <ul class="nav">
              {if $is_authed}
              <li class="{if !$active || $active=='index'}active{/if}"><a href="./?action=index"><i class="icon-home"></i> Home</a></li>
              <li class="dropdown {if $active=='jobs' || $active=='new_job'}active{/if}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tasks"></i> Jobs <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li class="{if $active=='jobs'}active{/if}"><a href="./?action=jobs"><i class="icon-tasks"></i> List</a></li>
                  <li class="divider"></li>
                  <li class="{if $active=='new_job'}active{/if}"><a href="./?action=new_job"><i class="icon-plus"></i> Add new</a></li>
                </ul>
              </li>
              <li class="{if $active=='offers'}active{/if}"><a href="./?action=offers"><i class="icon-user"></i> Offers </a></li>
              <li class="{if $active=='contracts'}active{/if}"><a href="./?action=contracts"><i class="icon-certificate"></i> Contracts</a></li>
              {/if}
            </ul>
            <ul class="nav pull-right">
              <li class="{if $active=='contact'}active{/if}"><a href="./?action=contact"><i class="icon-envelope-alt"></i> Contact</a></li>
              {if $is_authed}
              <li class="{if $active=='help'}active{/if}"><a href="./?action=help"><i class="icon-question-sign"></i> Help</a></li>
              <li><a href="./?action=logout&od=1"><i class="icon-signout"></i> Sign out</a></li>
              {/if}
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- NAVBAR END -->
    <div class="container">
      <div class="row-fluid">
        <div class="span12">
          {if $message}
          <div class="alert alert-{$message['type']}">
            <button type="button" class="close" data-dismiss="alert" style="font-size: 15px;"><i class="icon-remove"></i></button>
            {$message['body']}
            {if $message['type'] == 'error'}
            <p>
              <a class="btn" href="javascript:history.go(-1)"><i class="icon-chevron-left"></i> Go Back</a>
              <a class="btn" href="./"><i class="icon-home"></i> Home</a>
            </p>
            {/if}
          </div>
          {/if}
          {$content}
        </div>
      </div>
      <hr>
      <footer>
        <p class="pull-left">Developed by <a target="_blank" href="http://www.mediascape.gr" style="color: #B94A48;"><strong>Mediascape Ltd</strong></a> <br>© 2012, Harvard University.</p>
      </footer>
    </div>

  </body>
</html>
