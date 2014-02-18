$(function() {
	$(".button").each(function() {
		$(this).click(function() {
			$(this).closest("form").submit();
			return false;
		});
	});

	$.extend($.ui.dialog.prototype.options, {
		modal : true,
		width : 400,
		position : [ 'center', 50 ],
		autoOpen : false
	});
    
    $("#shadow").css("height", $(document).height()).hide();
    
    $( '#sign_in_submit' ).submit( function() {
        var output = jqueryPost( '/auth/loginAjax', {
            'email': $( '#sign_in_email' ).val(),
            'password': $( '#sign_in_password' ).val(),
            'remember_me': $('#sign_in_remember_me:checked').length
        }, true );
        
		if ( output.success == true )
		{
			$('p#login_message').attr('class','message f_success_msg');
			$('p#login_message').html('Login success');
			closeLoginForm();
			
			var myhref = '/user/pauth';
			location.href = myhref;
		}
		else
		{
			//alert( output.message );
		    $('#sign_in_email, #sign_in_password').css('border', '2px solid red');
			$('p#login_message').attr('class','message f_error_msg');
			$('p.message[id="login_message"]').html(output.message);
		}
		
		return false;
	});
	
	$( '#sign_up_submit' ).click( function() {
		var output = jqueryPost( 'account/signup', { 'name' : $( '#sign_up_name' ).val(), 'email' : $( '#sign_up_email' ).val(), 'password' : $( '#sign_up_password' ).val() }, true );
		
		if ( output.success == true )
		{
			alert( 'Signup success' )
		}
		else
		{
			alert( output.message );
		}
	});
});



$( '#logout' ).click(function() {
	data = jqueryPost( 'account/logout', '', true);

	if ( data && data.success == true ) 
	{
		document.location = data.redirect;
	}

	return false;
});

var keyBuffer =
{
    bufferVars: false,
    
    bufferTime: 300,

    go : function( action, variables, json, destination )
    {
		$.doTimeout
		(
			'keyBuffer', 
			this.bufferTime,
			function()
			{
				if( this.bufferVars != variables || force )
				{
					this.bufferVars = variables;
					
					var output = jqueryPost( action, variables, json );
					
					destination.html( output ).show();
				}
			}
		);
    },
    
    go2 : function( action, variables, json, callback )
    {
		$.doTimeout
		(
			'keyBuffer',
			
			this.bufferTime,
			
			function(  )
			{
				if( this.bufferVars != variables || force )
				{
					this.bufferVars = variables;

					o.value = jqueryPost( action, variables, json );
					
					callback();
				}
			}
		);
    }
};

function jqueryPost( action, variables, json, displayLoader, asyncMode ) {
	return jqueryAction( 'post', action, variables, json, displayLoader, asyncMode );
}

function jqueryGet( action, variables, json, displayLoader, asyncMode ) {
	return jqueryAction( 'get', action, variables, json, displayLoader, asyncMode );
}

function jqueryAction( method, action, variables, json, displayLoader, asyncMode ) {
	var result = '';
	
	var ajaxVars = {
		type : method,
		url : action,
		async : asyncMode == true ? true : false,
		data : variables,
		success : function(data) 
		{
			result = data;
		},
		dataType : json == true ? 'json' : '',
	}
	
	if ( displayLoader == true )
	{
		ajaxVars.beforeSend = function() { $( '#loader' ).show(); $( '#shadow' ).show() };
		
		ajaxVars.complete   = function() { $( '#loader' ).hide(); $( '#shadow' ).hide() };
	}

	$.ajax( ajaxVars );

	return result;
}


///////////////////////////////////////////////////////

function isInt(n) {
   return n % 1 === 0;
}

/*
function slider_1_dis_ena(){
	
	$("#slider_1").attr('aria-disabled',true);
	$("#slider_1").addClass('undefined-disabled ui-state-disabled');
	
	if ($('#chk_price_hourly').attr("checked")) {
		$("#slider_1").slider({disabled : false});
	}
	else {
		$("#slider_1").slider({disabled : true});
	}
	
}

function slider_2_dis_ena(){
	
	$("#slider_2").attr('aria-disabled',true);
	$("#slider_2").addClass('undefined-disabled ui-state-disabled');

	if ($('#chk_price_fixed').attr("checked")) {
		$("#slider_2").slider({disabled : false});
		//$("#slider_2").slider("enable");
	}
	else {
		$("#slider_2").slider({disabled : true});
		//$("#slider_2").slider("disable");
	}
}
*/

function actionLogin (popup_id) {
	var marginTop = 100;
	showLoginForm(popup_id);
	
	var loginWin = $(popup_id);
	$('html, body').animate({
		scrollTop: (loginWin.offset().top - marginTop)
	}, 500);
	
	return false;
}


function showLoginForm(popup_id) {
	var loginWin = $(popup_id);
	//$(".popup_register").hide();
	$(".overlay").show();
	
	loginWin.fadeIn("fast");
//	loginWin.find('input[name=email]').focus();
}

function closeLoginForm() {
	$(".overlay").click (function () {
		$(".popup_type").fadeOut("fast",function () {
				hideOverlay();
		});
		//clearFormErrors('login-form');
	});
}

function hideOverlay() {
	$(".overlay").hide();
//	$(".preloader").hide();
}

var active_row;

function LoadPlatformFields()
{
    if ($("select#select_platform option:selected").val() == '0') {
        $('#no_account').slideUp();
        $('#popup_remote_login #sign_in_container').slideUp();
        $(".remote_credentials").slideUp();
    } else {
        $('#no_account').slideDown();
        $('#popup_remote_login #sign_in_container').slideDown();
        $(".remote_credentials").slideDown();
    }
    
    $('#register_for_account').attr('href','/profile/remoteregister/pl/' + $("select#select_platform option:selected").val());
}

var trDisabled = false;
$(document).ready(function() {
    $('input[name=posted_start], input[name=posted_end]').click(function() {
        $('input[name=posted_date]').attr('checked', 'checked');
    });
    
    $('input[name=posted_start]').change(function() {
        $('input[name=posted_end]').datepicker( "option", "minDate", $(this).val());
    });
    $('input[name=posted_end]').change(function() {
        $('input[name=posted_start]').datepicker( "option", "maxDate", $(this).val());
    });
    
    $('.pagination_insider a').live('click', function() {
        var url = $(this).attr('href');
        if (url) {
            document.body.style.cursor = 'wait';
            $('.projects_block_container').load(url, function(){
                window.scrollTo(0, 0);
                document.body.style.cursor = 'default';
                $.address.value(url);
            });
        }
        return false;
    });
    
    var justLoaded = true;
    $.address.externalChange(function(event) {
        if (justLoaded) {
            justLoaded = false;
            return;
        }
        var url = event.path;
        if (url.match(/^\/projects\/index/)) {
            document.body.style.cursor = 'wait';
            $('.projects_block_container').load(url, function(){
                window.scrollTo(0, 0);
                document.body.style.cursor = 'default';
            });
        }
    });
    
    if ($('#my_ptc').length) {
        if ($('#my_ptc').attr('platform_to_connect')) {
            var platform_to_connect = parseInt($('#my_ptc').attr('platform_to_connect'));
            actionLogin('#popup_remote_login');
            $('select#select_platform').val(platform_to_connect);
            LoadPlatformFields();
        }
    }
    
    $('#add_new_platform').click(function () {
        actionLogin('#popup_remote_login');
    });
    
    $('#select_platform').change(function() {
        $('#popup_remote_login .append_section').html('');
        LoadPlatformFields();
    });
    
    $('#submit_remote').click(function () {
        var selected_option = $("select#select_platform option:selected");
        var platformId = selected_option.val();
        var username = $('#remote_login_form input[name="username"]').val();
        var password = $('#remote_login_form input[name="password"]').val();
        
        $.post('/profile/connect',
            {
                platformId: platformId,
                username: username,
                password: password
            },
            function(response) {
                location.href = location.href;
            }
        );
        
        return false;
    });
    
    $('#submit_remote2').click(function () {
        var platformId = $('#platformId').val();
        var username = $('#remote_login_form input[name="username"]').val();
        var password = $('#remote_login_form input[name="password"]').val();
        
        $.post('/profile/connect',
            {
                platformId: platformId,
                username: username,
                password: password
            },
            function(response) {
                location.href = location.href;
            }
        );
        
        return false;
    });
    
    $("img#close").click(function () {
        $(".popup_type").fadeOut("fast",function () {
            hideOverlay();
        });
    });
    
    $(".login-btn").click(function () {
        return actionLogin('#popup_login');
    });
    
    $("#getacoder_login").click(function () {
        return actionLogin('#popup_login_getacoder');
    });
    
    closeLoginForm() ;

    $('table.tab_project_list tr').live('click', function () {
        if (trDisabled) {
            trDisabled = false;
            return;
        }
        var project_id = $(this).attr('prjid');
        var url = $(this).attr('url');
        var detaileInfoBlock = $('#project_detailed_' + project_id + ' td').html();
        var element = $(this);
        
        $('tr.active_tr').html(active_row);
        $('tr.active_tr').removeClass('active_tr');
        element.addClass('active_tr');
        active_row = $('tr.active_tr').html();
        element.html('<td id="extended" colspan="7" onclick="if (!trDisabled) { $(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove(); }">'+ detaileInfoBlock +'</td>');
        
        var offset = element.offset().top + element.outerHeight(true) - $(window).scrollTop();
        var offset2 = element.offset().top - $(window).scrollTop();
        if(offset > window.innerHeight || offset2 < 0){
            $.scrollTo(this, 800);
            return false;
        }
    });

		$('tra').hover(
		  function () {
			var project_id = $(this).attr('prjid');
			var url = $(this).attr('url');
			
			var detaileInfoBlock;
			$.ajax({
				url: '/index/ajaxdetailed/projectid/'+ project_id +'/',
				async : false,
				success: function(res) {
					//alert(res);
					detaileInfoBlock = res;
				}
			});
			
			/*var proposalBlock;
			$.ajax({
				url: '/proposal/index/projectid/'+ project_id +'/',
				async : false,
				success: function(res) {
					//alert(res);
					proposalBlock = res;
				}
			});*/
			
			
			//$(this).find('td.hover_box_place').append('<div id="hover_box"><input id="btn_details" type="button" /><a id="btn_apply" href="/proposal/index/projectid/'+ project_id +'/"> </a><!--<input id="btn_apply"  type="button" />--></div>');  
			$(this).find('td.hover_box_place').append('<div id="hover_box"><input id="btn_details" type="button" /><a id="btn_apply" href="/projects/detail/?title='+ url +'"> </a><!--<input id="btn_apply"  type="button" />--></div>');  
			$(this).find('td.hover_box_place').css('z-index','10');
			
			
			$('input#btn_details').click(
				function () {
				$('tr.active_tr').html(active_row);
				$('tr.active_tr').find('div#hover_box').remove();
				$('tr.active_tr').removeClass('active_tr');
				$(this).parent().parent().parent().addClass('active_tr');
				active_row = $('tr.active_tr').html();
				//$(this).parent().parent().parent().html('<td id="extended" colspan="7" onclick="$(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove();"><div id="extended_box"><span id="ext_title">Need 1000 Facebook Fans</span><span id="ext_new"><img src="images/elements/new_arrow.png" alt="" title="" /></span><br /><span id="ext_operator_logo"><img src="images/logos/recent_logo_odesk.png" alt="" title="" /></span><p id="ext_description_title">Description</p><p id="ext_description_text">Our company will be placing an advertisement in a major architectural magazine in the next couple of onths. The goal of this project is to develop that ad. The advertisement will be a 1/6th page ad. The ad will be application. I will have someone else code the website, I just need you to create the graphics.  This is just a 1-page layout, so it should be a very simple project.  Once the page is designed, I will need you to modify the color scheme so that we have the same layout with several different color schemes. These will be for a client of mine who is a lawyer, so the site needs to look professional and very similar to what you see at the link above.  In order to be considered for this project, please be sure to start your proposal off with the answer to the following math problem, so I know you read this entire posting. The math problem you should answer is (11-4).</p><div id="ext_tags_row"><span id="ext_tags_title">Tags:</span><a href="#" class="ext_tags_item">facebook</a><a href="#" class="ext_tags_item">SEM</a><a href="#" class="ext_tags_item">SEO</a></div><div id="ext_bottom_box"><div id="ext_bottom_left"><div class="ext_bottom_row"><span class="title_name">Posted:</span><span id="ext_bott_posted_value" class="value">4h, 1m ago</span></div><div class="ext_bottom_row"><span class="title_name">Time Left:</span><span id="ext_bott_time_left" class="value">14d, 19h</span></div></div><div id="ext_bottom_middle"><div class="ext_bottom_row"><span class="title_name">Fixed Price Job</span></div><div class="ext_bottom_row"><span class="title_name">Bids:</span><span id="ext_bott_bids_value" class="value">7</span></div><div class="ext_bottom_row"><span class="title_name">Average:</span><span id="ext_bott_average" class="value">200$</span></div></div><div id="ext_bottom_right"><p>Budget:&nbsp;<span id="ext_bott_budget">275$</span></p><input id="btn_apply" type="button" /></div><div class="clear"></div></div></div></td>');
				$(this).parent().parent().parent().html('<td id="extended" colspan="7" onclick="$(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove();">'+ detaileInfoBlock +'</td>');
										}
									);
									
		/*	$('input#btn_apply').click(
				
				function () {
				window.location('/proposal/index/projectid/'+ project_id +'/');
				
				//$('tr.active_tr').html(active_row);
				//$('tr.active_tr').find('div#hover_box').remove();
				//$('tr.active_tr').removeClass('active_tr');
				//$(this).parent().parent().parent().addClass('active_tr');
				//active_row = $('tr.active_tr').html();
				//$(this).parent().parent().parent().html('<td id="extended" colspan="7" onclick="$(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove();"><div id="extended_box"><span id="ext_title">Need 1000 Facebook Fans</span><span id="ext_new"><img src="images/elements/new_arrow.png" alt="" title="" /></span><br /><span id="ext_operator_logo"><img src="images/logos/recent_logo_odesk.png" alt="" title="" /></span><p id="ext_description_title">Description</p><p id="ext_description_text">Our company will be placing an advertisement in a major architectural magazine in the next couple of onths. The goal of this project is to develop that ad. The advertisement will be a 1/6th page ad. The ad will be application. I will have someone else code the website, I just need you to create the graphics.  This is just a 1-page layout, so it should be a very simple project.  Once the page is designed, I will need you to modify the color scheme so that we have the same layout with several different color schemes. These will be for a client of mine who is a lawyer, so the site needs to look professional and very similar to what you see at the link above.  In order to be considered for this project, please be sure to start your proposal off with the answer to the following math problem, so I know you read this entire posting. The math problem you should answer is (11-4).</p><div id="ext_tags_row"><span id="ext_tags_title">Tags:</span><a href="#" class="ext_tags_item">facebook</a><a href="#" class="ext_tags_item">SEM</a><a href="#" class="ext_tags_item">SEO</a></div><div id="ext_bottom_box"><div id="ext_bottom_left"><div class="ext_bottom_row"><span class="title_name">Posted:</span><span id="ext_bott_posted_value" class="value">4h, 1m ago</span></div><div class="ext_bottom_row"><span class="title_name">Time Left:</span><span id="ext_bott_time_left" class="value">14d, 19h</span></div></div><div id="ext_bottom_middle"><div class="ext_bottom_row"><span class="title_name">Fixed Price Job</span></div><div class="ext_bottom_row"><span class="title_name">Bids:</span><span id="ext_bott_bids_value" class="value">7</span></div><div class="ext_bottom_row"><span class="title_name">Average:</span><span id="ext_bott_average" class="value">200$</span></div></div><div id="ext_bottom_right"><p>Budget:&nbsp;<span id="ext_bott_budget">275$</span></p><input id="btn_apply" type="button" /></div><div class="clear"></div></div></div></td>');
				//$(this).parent().parent().parent().html('<td id="extended" colspan="7" onclick="$(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove();">'+ detaileInfoBlock +'</td>');
										}
									);*/
							  },
		  function () {
			$(this).find('td.hover_box_place').css('z-index','-10');
			$(this).find('div#hover_box').remove();
		  }
		);
		
		/*
		$('td.hover_box_place').click(
		  function () {
				var project_id = $(this).closest('tr').attr('prjid');
				$('tr.active_tr').html(active_row);
				$('tr.active_tr').find('div#hover_box').remove();
				//$('tr.active_tr').removeClass('active_tr');
				$(this).parent().addClass('active_tr');
				active_row = $('tr.active_tr').html();
				
				//$(this).parent().html('<td id="extended" colspan="7" onclick="$(this).remove();$(\'tr.active_tr\').html(active_row);$(\'tr.active_tr\').removeClass(\'active_tr\');active_row = undefined;$(\'div#hover_box\').remove();"><div id="extended_box"><span id="ext_title">Need 1000 Facebook Fans</span><span id="ext_new"><img src="images/elements/new_arrow.png" alt="" title="" /></span><br /><span id="ext_operator_logo"><img src="images/logos/recent_logo_odesk.png" alt="" title="" /></span><p id="ext_description_title">Description</p><p id="ext_description_text">Our company will be placing an advertisement in a major architectural magazine in the next couple of onths. The goal of this project is to develop that ad. The advertisement will be a 1/6th page ad. The ad will be application. I will have someone else code the website, I just need you to create the graphics.  This is just a 1-page layout, so it should be a very simple project.  Once the page is designed, I will need you to modify the color scheme so that we have the same layout with several different color schemes. These will be for a client of mine who is a lawyer, so the site needs to look professional and very similar to what you see at the link above.  In order to be considered for this project, please be sure to start your proposal off with the answer to the following math problem, so I know you read this entire posting. The math problem you should answer is (11-4).</p><div id="ext_tags_row"><span id="ext_tags_title">Tags:</span><a href="#" class="ext_tags_item">facebook</a><a href="#" class="ext_tags_item">SEM</a><a href="#" class="ext_tags_item">SEO</a></div><div id="ext_bottom_box"><div id="ext_bottom_left"><div class="ext_bottom_row"><span class="title_name">Posted:</span><span id="ext_bott_posted_value" class="value">4h, 1m ago</span></div><div class="ext_bottom_row"><span class="title_name">Time Left:</span><span id="ext_bott_time_left" class="value">14d, 19h</span></div></div><div id="ext_bottom_middle"><div class="ext_bottom_row"><span class="title_name">Fixed Price Job</span></div><div class="ext_bottom_row"><span class="title_name">Bids:</span><span id="ext_bott_bids_value" class="value">7</span></div><div class="ext_bottom_row"><span class="title_name">Average:</span><span id="ext_bott_average" class="value">200$</span></div></div><div id="ext_bottom_right"><p>Budget:&nbsp;<span id="ext_bott_budget">275$</span></p><a id="btn_apply" href="/proposal/index/projectid/'+ project_id +'/"> </a> </div><div class="clear"></div></div></div></td>');
										}
									);
									*/
									
									

////////////////////Sliders on right sidebar



///////////////////Enabling/Disabling sliders
$('input#chk_price_hourly').change(function () {    
	if ($(this).attr("checked")) {$("#slider_1").slider("enable");}
	else {$("#slider_1").slider("disable");
	
	}
	});
	
$('input#chk_price_fixed').change(function () {    
	if ($(this).attr("checked")) {$("#slider_2").slider("enable");}
	else {$("#slider_2").slider("disable");}
	});
	

/////////////////////TableSorter
$("#recent").tablesorter();


});