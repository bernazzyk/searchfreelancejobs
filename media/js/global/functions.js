var menu_open = 0;

function open_popup (social_auth) {
	var popupWidth=700;
	var popupHeight=500;
	var xPosition=($(window).width()-popupWidth)/2;
	var yPosition=($(window).height()-popupHeight)/2;
	//var loginUrl="http://freelancer.fm/" + social_auth; 
    var loginUrl= social_auth; 
	loginwindow = window.open(loginUrl, "LoginWindow",
											  "location=1,scrollbars=1,"+
											  "width="+popupWidth+",height="+popupHeight+","+
											  "left="+xPosition+",top="+yPosition);						  
}
var nr_items = 1;
$(document).ready(function() {

		$(function(){
			$('#slider_js').slides({
				preload: true,
				preloadImage: 'img/loading.gif',
				play: 3000,
				pause: 900,
				effect: 'fade',
				hoverPause: true
			});
		});

$("#milestone_check").click(function ()  
	{
		if ($(this).is(":checked")) 
		{
			$("#elance_milestone").slideDown();
			$("#elance_fix_amount").slideUp();
		} 
		else 
		{
			$("#elance_milestone").slideUp();
			$("#elance_fix_amount").slideDown();
		}
});

var categories_checks = $('#search_block_categories input.filter_chk_categories');

$('#filter_chk_0').click(function(){
	if($(this).is(':checked'))
	{
		categories_checks.each(function(){
			$(this).removeAttr('checked');
		});
	}
});

categories_checks.click(function(){
	if($('#filter_chk_0').is(':checked'))
	{
		$('#filter_chk_0').removeAttr('checked');
	}
}); 

var platform_checks = $('#search_block_platforms input.filter_chk_platforms');
$('#filter_platform_chk_0').click(function(){
	if($(this).is(':checked'))
	{
		platform_checks.each(function(){
			$(this).removeAttr('checked');
		});
	}
});

platform_checks.click(function(){
	if($('#filter_platform_chk_0').is(':checked'))
	{
		$('#filter_platform_chk_0').removeAttr('checked');
	}
});


	
	
	//$('.detail_delete').live('click',function () {
	
	$('#elance_form input.detail_amt_el').live('change',function() { 
		var amt_fields = $('#elance_form input.detail_amt_el');
		var total_summ = 0.00;
		amt_fields.each(function() {
			if($(this).val()!='')
			{
				total_summ += parseFloat($(this).val());
			}
		});
		$('#elance_form .elance_total').val(total_summ);
	});
	
	$('input.detail_amt').live('change',function() { 
		var amt_fields = $('#pph_form input.detail_amt');
		var total_summ = 0.00;
		amt_fields.each(function() {
			if($(this).val()!='')
			{
				total_summ += parseFloat($(this).val());
			}
		});
		$('#pph_form .pph_total').val(total_summ);
		//alert(total_summ);
	});

	$('#pph_form').submit(function() {
		var fields = $('#pph_form .alert_if_error');
		var error = false;
		
		fields.each(function() {
		
			fieldVal = $(this).val();

			if( fieldVal =='') 
			{
				$(this).css({'border':'1px solid #c00'});
			//	message.attr('class', 'message error');
			//	message.text(getMessage(1));
				error = true;
			} 
			else
			{
				$(this).css({'border':'1px solid #80A1C1'});
			}
		});
		
		if(error)
		{
			return false;
		}
});

	
	$('.detail_delete').live('click',function () {
		if($('.project_detail_fields .row_form.detail_inputs').length>1)
		{
			//$('.project_detail_fields .row_form.detail_inputs').last().remove();
			$(this).closest('.row_form').remove();
		}

		//$(this).closest('.row_form').remove();
		//$('.project_detail_fields').append('<div class="row_form"><input class="detail_txt" name="ProposalDetail['+ nr_items +'][description]" maxlength="80" type="text"><input class="detail_amt" name="ProposalDetail['+ nr_items +'][cost]" type="text"></div>');
		//nr_items++;
	});

	$("#add_new_item").click (function () {
		var valute_class = $('#pph_form').attr('currency');
		$('.project_detail_fields').append('<div class="row_form detail_inputs"><span class="detail_delete"></span><input placeholder="enter description" class="detail_txt alert_if_error" name="ProposalDetail['+ nr_items +'][description]" maxlength="80" type="text"><input class="detail_amt alert_if_error '+ valute_class +'" name="ProposalDetail['+ nr_items +'][cost]" type="text" placeholder="0.00"></div>');
		nr_items++;
	});
	
	
	$("#add_new_item_elance").click (function () {
		var valute_class = $('#elance_form').attr('currency'); 
		$('.project_detail_fields').append('<div class="row_form detail_inputs"><span class="detail_delete"></span><input placeholder="Description" class="detail_txt alert_if_error" name="ms['+ nr_items +'][desc]" type="text"><input placeholder="Date" class="detail_date_el alert_if_error date_text" name="ms['+ nr_items +'][date]" type="text"><input placeholder="Amount (including Elance Fee)" class="detail_amt_el alert_if_error '+ valute_class +'" name="ms['+ nr_items +'][amount]" type="text" placeholder="0.00"></div>');
		nr_items++;
	});

	$(".head_lang").click (function () {
			
		if(menu_open!=1) {  $("#head_lang_menu").show();  menu_open = 1;  }
		else {$("#head_lang_menu").hide();  menu_open = 0; }
	});
	
	
	$(".language_item").click (function () {
		
		 $(".head_lang .head_lang_menu_text").html($(this).attr('lang'));  
					
		 $('#head_lang_menu').css('display', 'none');
	 	 menu_open = 0;  
	
	});
	
$('.date').datepicker({dateFormat: "yy-mm-dd" });


$('.date_text').live('click',function (){
        $(this).datepicker('destroy').datepicker({dateFormat: "M dd, yy"}).focus();
    });



});