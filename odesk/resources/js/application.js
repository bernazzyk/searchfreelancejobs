$(document).ready(function(){
  window.prettyPrint && prettyPrint()
  $("a[rel=tooltip]").click(function(e){e.preventDefault()}).tooltip();
  $("span[rel=tooltip]").tooltip();
  $("abbr[rel=tooltip]").tooltip();
  $("a[rel=popover]").click(function(e){e.preventDefault()}).popover();
  $("span[rel=popover]").popover();
  $("abbr[rel=popover]").popover();

  $("#busy-loader").spin("large");
  
  $('.datepicker').datepicker();
  
  $("input.currency").maskMoney({
    thousands: ',',
    decimal: '.',
    precision: 2
  });
  
  $("#subcategory").chained("#category");  
  
  var $win = $(window)
  , $nav = $('.subnav')
  , navHeight = $('.navbar').first().height()
  , navTop = $('.subnav').length && $('.subnav').offset().top - navHeight
  , isFixed = 0

  processScroll()

  $win.on('scroll', processScroll)

  function processScroll() {
    var i, scrollTop = $win.scrollTop()
    if (scrollTop >= navTop && !isFixed) {
      isFixed = 1
      $nav.addClass('subnav-fixed')
    } else if (scrollTop <= navTop && isFixed) {
      isFixed = 0
      $nav.removeClass('subnav-fixed')
    }
  }
});

$.ajaxSetup({
  beforeSend:function(){
    $("#busy").height($(document).height());
    $("#busy").show();
  },
  complete:function(){
    $("#busy").hide();
    window.prettyPrint && prettyPrint()
    $("a[rel=tooltip]").click(function(e){e.preventDefault()}).tooltip();
    $("span[rel=tooltip]").tooltip();
    $("abbr[rel=tooltip]").tooltip();
    $("a[rel=popover]").click(function(e){e.preventDefault()}).popover();
    $("span[rel=popover]").popover();
    $("abbr[rel=popover]").popover();
    $("input.currency").maskMoney({
      thousands: ',',
      decimal: '.',
      precision: 2
    });
    
  }
});