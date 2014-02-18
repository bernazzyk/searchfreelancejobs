$(document).ready(function() {
	$(function(){

	var str=$('#search_block_budget').attr('price_limits_hourly');

	var main_page = $('#search_block_budget').attr('main_page');
	
	var min_hourly;
	var max_hourly;
	if(str!='')
	{
		var price_limits_hourly=str.split("x"); 

		min_hourly = parseInt(price_limits_hourly[0]);
		max_hourly = parseInt(price_limits_hourly[1]);
	}
	
	var disable_slider_1;
	if(main_page=='1')
	{
		min_hourly = 0;
		max_hourly = 100;
	}
	else if( (isInt(min_hourly) && isInt(max_hourly) && min_hourly < max_hourly) || main_page=='1')
	{
		//alert('testq');
		//$('input#chk_price_hourly').attr('checked', true);
		disable_slider_1 = false;
	} 
	else {
		//alert('test');
		disable_slider_1 = true;
		$("#slider_1").attr('aria-disabled',true);
		$("#slider_1").addClass('undefined-disabled ui-state-disabled');	
		min_hourly = 0;
		max_hourly = 100;
	}

	var max_value = max_hourly;
	if (max_value == 100) {
	    max_value += '+';
	}
	$( "#slider_1_result_1" ).html( min_hourly );
	$( "#slider_1_result_2" ).html(max_value);

	$('#hidden_1_1').attr('value_1_1', min_hourly);
	$('#hidden_1_1').attr('value_1_2', max_hourly);

	$('#hidden_1_1').val(min_hourly);
	$('#hidden_1_2').val(max_hourly);


	$("#slider_1").slider({
		disabled : disable_slider_1,
		range: true,
		min: 0,
		max: 100,
		values: [min_hourly,max_hourly],
		step: 5,
    slide: function( event, ui ) {
        $( "#slider_1_result_1" ).html( ui.values[0] );
        var value = ui.values[1];
        if (value == 100) {
            value += '+';
        }
        $( "#slider_1_result_2" ).html(value);
    },
	//this updates the hidden form field so we can submit the data using a form
	change: function(event, ui) {
	$('#hidden_1_1').attr('value_1_1', ui.values[0]);
	$('#hidden_1_1').attr('value_1_2', ui.values[1]);

	$('#hidden_1_1').val(ui.values[0]);
	$('#hidden_1_2').val(ui.values[1]);
	}
	});

	var min_fixed;
	var max_fixed;
	var str_fix=$('#search_block_budget').attr('price_limits_fixed');
	if(str_fix!='')
	{
		var price_limits_fixed=str_fix.split("x"); 
		min_fixed = parseInt(price_limits_fixed[0]);
		max_fixed = parseInt(price_limits_fixed[1]);
	}
	var disable_slider_2;
	if(main_page=='1')
	{
		min_fixed = 0;
		max_fixed = 10000;
	}
	if((isInt(min_fixed) && isInt(max_fixed) && min_fixed < max_fixed) || main_page=='1')
	{
		//$('input#chk_price_fixed').attr('checked', true);
		disable_slider_2 = false;
	}
	else {
		disable_slider_2 = true;
		$("#slider_2").attr('aria-disabled',true);
		$("#slider_2").addClass('undefined-disabled ui-state-disabled');
		min_fixed = 0;
		max_fixed = 10000;
	}

	var max_value = max_fixed;
	if (max_value == 10000) {
	    max_value += '+';
	}
	$( "#slider_2_result_1" ).html( min_fixed );
	$( "#slider_2_result_2" ).html(max_value);

	$('#hidden_2_1').attr('value_2_1', min_fixed);
	$('#hidden_2_1').attr('value_2_2', max_fixed);

	$('#hidden_2_1').val(min_fixed);
	$('#hidden_2_2').val(max_fixed);
	//alert(fix_priced_disabled);
	$("#slider_2").slider({
		disabled : disable_slider_2,
		range: true,
		min: 0,
		max: 10000,
		values: [min_fixed,max_fixed],
		step: 50,
    slide: function( event, ui ) {
        $( "#slider_2_result_1" ).html( ui.values[0] );
        var value = ui.values[1];
        
        if (value == 10000) {
            value += '+';
        }
        $( "#slider_2_result_2" ).html(value);
    },
	//this updates the hidden form field so we can submit the data using a form
	change: function(event, ui) {
	$('#hidden_2_1').attr('value_2_1', ui.values[0]);
	$('#hidden_2_1').attr('value_2_2', ui.values[1]);

	$('#hidden_2_1').val(ui.values[0]);
	$('#hidden_2_2').val(ui.values[1]);
	}
	});

	//slider_1_dis_ena();
	//slider_2_dis_ena();

	});
});