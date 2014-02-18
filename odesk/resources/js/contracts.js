$(document).ready(function(){
    
  function getErrors(errors){
    $.ajax({
      type: 'GET',
      dataType: 'HTML',
      url: 'index.php?action=message_errors',
      data: errors
    }).done(function(responce){
      $('#errors').empty();
      $('#errors').append(responce);
    });
  }
    
  function sendMessage(){
    $.ajax({
      type: 'POST',
      dataType: 'JSON',
      url: 'index.php?action=message_send',
      data: 'recipients=' + $("input#recipients").val() + '&subject=' + $("input#subject").val() + '&body=' + $("textarea#body").val()
    }).done(function(responce){
      if (!responce['success']){
        var errors = responce['errors'];
        getErrors($.param(errors));
        return false;
      }
      else {
        bootbox.hideAll();
        bootbox.alert('Message sent!!!', "OK");
      }
    });
  }
    
  function synchronize(params){
    $.ajax({
      type: 'GET',
      dataType: 'JSON',
      url: 'index.php?action=contract_sync' + params
    }).done(function(responce){
      bootbox.alert(responce['message'], function(){
        location.reload()
      });
    });
  }
    
    
  function prepMessage($el) {
    $.ajax({
      type: 'GET',
      dataType: 'HTML',
      url: $el.attr('data-remote')
    }).done(function(responce){
      bootbox.dialog(responce, [
      {
        "label" : "Send",
        "class" : "btn-success",
        "icon"  : "icon-plane",
        "callback": function() {
          sendMessage();
          return false;
        }
      }, {
        "label" : "Cancel",
        "class" : "btn",
        "icon"  : "icon-undo",
        "callback": function() {

        }
      }], 
      {
        "header": $el.attr('title'),
        "backdrop" : "static",
        "keyboard" : false,
        "show"     : true
      });
    });
      
  }
  function loadEngagements(last){
    $.ajax({
      type: 'GET',
      dataType: 'HTML',
      url: 'index.php?action=load_engagements&status=' + _status + '&last=' + last
    //        async: false
    }).done(function(responce){
      $("a#showmore-engagements").parent('td').parent('tr').remove();
      $('#engagementslist').append(responce); 
      $("a#showmore-engagements").click(function(e){
        e.preventDefault();
        doLoadEngagements($(this).attr('rel'));
      })
      $("a.info_c").not('.disabled').click(function(e){
        e.preventDefault();
        $el = $(this);
        $.ajax({
          type: 'GET',
          dataType: 'HTML',
          url: $el.attr('data-remote')
        }).done(function(responce){
          bootbox.dialog(responce, [{
            "label" : "OK",
            "callback": function() {}
          }], 
          {
            "header": $el.attr('title'),
            "backdrop" : "static",
            "keyboard" : false,
            "show"     : true
          });
        });
          
      });
      $("a.sync").not('.disabled').click(function(e){
        e.preventDefault();
        synchronize($(this).attr('rel'));
      })
      $("a.sync.disabled").click(function(e){
        e.preventDefault();
        bootbox.alert('Action cannot be performed! Contract already synchronized.');
      })
      $("a.s_message, a.p_message").not('.disabled').click(function(e){
        e.preventDefault();
        $el = $(this);
        prepMessage($el);
      });
      $("a.cls_c").not('.disabled').click(function(e){
        e.preventDefault();
        $el = $(this);
        $.ajax({
          type: 'GET',
          dataType: 'HTML',
          url: $el.attr('data-remote')
        }).done(function(responce){
          bootbox.dialog(responce, [
          {
            "label" : "Close contract",
            "class" : "btn-danger",
            "callback": function() {
              var $form = jQuery('.bootbox').find('form');
              var $modal = $('.bootbox');
              $modal.hide();
              bootbox.dialog('<strong>Confirm?</strong><br/>Are you sure? You cannot activate the contract again once it is closed!',
                [{
                  "No": function() {
                    $modal.show();
                  }
                  
                },{
                  "Yes": function() {
                    $modal.show();
                    $form.ajaxSubmit({
                      dataType: 'json',
                      success: function(responseText, statusText, xhr, $form){
                        if (!responseText.success){
                          $('#errors').empty();
                          responseText.message ? $('#errors').append(responseText.message) : $('#errors').append(responseText);
                        } else {
                          bootbox.hideAll();
                          bootbox.alert(responseText.message, function(){
                            location.reload()
                          });
                        }
                      }
                    });
                  }
                }], 
                {
                  "backdrop": false
                }
              );
              return false;
            }
          }, 
          {
            "label" : "Cancel",
            "callback": function() {

            }

          }], 
          {
            "header": $el.attr('title'),
            "backdrop" : "static",
            "keyboard" : false,
            "show"     : true
          });
        });
          
      });
      $("a.pay_c").not('.disabled').click(function(e){
        e.preventDefault();
        $el = $(this);
        $.ajax({
          type: 'GET',
          dataType: 'HTML',
          url: $el.attr('data-remote')
        }).done(function(responce){
          bootbox.dialog(responce, [
          {
            "label" : "Pay contract",
            "class" : "btn-success",
            "callback": function() {
              $form = jQuery('.bootbox').find('form');
              $form.ajaxSubmit({
                dataType: 'json',
                success: function(responseText, statusText, xhr, $form){
                  if (!responseText.success){
                    $('#errors').empty();
                    $('#errors').append(responseText.message);
                  } else {
                    bootbox.hideAll();
                    bootbox.alert(responseText.message, function(){
                      //                          location.reload()
                      });
                  }
                }
              });
              return false;
            }
          },
          {
            "label" : "Cancel",
            "callback": function() {

            }

          }], 
          {
            "header": $el.attr('title'),
            "backdrop" : "static",
            "keyboard" : false,
            "show"     : true
          });
        });
          
      });
      $("a.pay_c.disabled, a.cls_c.disabled, a.s_message.disabled, a.p_message.disabled").click(function(e){
        bootbox.alert('Action cannot be performed!');
      });
      $("a[rel=popover]").click(function(e){
        e.preventDefault()
      }).popover();
    });
  }

  function doLoadEngagements(last){
    loadEngagements(last);
  }

  $("a#showmore-engagements").click(function(e){
    e.preventDefault();
    doLoadEngagements($(this).attr('rel'));
  })
  if (job){
    doLoadEngagements(0+'&job='+job);
  } else {
    doLoadEngagements(0);
  }
});