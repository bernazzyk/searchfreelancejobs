/** jQuery.validvalue
 * 
 *	Alerts the user of missing or malformed <input><textarea> elements, 
 * 		subsequently focusing on the bad element.
 *  Also supports checkbox checking (only validates boxes that are unchecked checked).
 *	Automatically adds onsubmit validation for forms that try to submit 
 * 		values set with valid value.  If values are empty or invalid:
		the form will not submit,
 *			 an error will be displayed
 *<p>
 * Usage: 
 *		$('input[name='foo']').validvalue('Change Me', 'You didn\'t change me!');
 *
 *		//if a form element with a default value does not need input validated:
 *				//second param is almost irrelevant as it will only de displayed on
 *				// an explicit 'blame' event.
 *      $('#foo').validvalue('Change Me', '', {required: false});
 *
 *		//to assign the same defaults to multiple elements:
 *			$("#foo1,#foo2").validvalue('Change Us', 'You didn\'t change us!');
 *
 *		//to add an additional requirement beyond; (input != default value)
 *		// function must be passed an element (usually the jQuery element to check) 
 *			// and must returns: 
 *					true - if element is valid
 *					false - if element is invalid and the default message is suitable
 *					string - same as returning false except this string is displayed insead
 *	  // the default message
 *			$("#foo").validvalue('Change Me', 
 *								'You didn\'t change me!',
 *								{check: function(value){
 *										//return true if value is valid
 *										return String(value).match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/);
 *									}
 *							 	});
 *
 * To overide defaults so the check or required paramater is set for multiple calls to validvalue
 * write something like this first:
 *			$.fn.validvalue.defaults.required = false;
 *			$.fn.validvalue.defaults.check = function(value)
 *
 *	TODO: Add/Test 'select' input validation
 *
 * @param def string that provides the default message that $this input will display initially
 * @param msg string to display if $this input is not the default message or is blank
 * @param options object used for optional parameters {required: <bool>, check: <function>} 
 * @param $this input, for jquery chainability
 * @author Brad Kohlenberg
 * @copyright 2008-12-15
 */
 
 (function($) {
	
	$.fn.validvalue = function(def, msg, options) {
		
		// Scope
		var settings = $.extend({}, $.fn.validvalue.defaults, options); //TODO (Brad): Add {def: def, msg: msg}
		
		return(
			this.each(function() {				
				// Default values within scope
				var el = $(this);
				
				//Add hidden value to form (if not already presnet), used to stop validating form prematurely
				if (el.parents('form').children('#stopValidation').length <= 0){
					el.parents('form').append('<input id="stopValidation" type="hidden" val="0"/>');
				}	
				// set default value, if empty
				if (el.val() == "") {
					el.val(def);
				}
				
				//on focus remove default text
				el.focus(function() {
					if (el.val() == def) {
						el.val("");
					}
				});
				
				//on blur, if no text was entered, replace default text
				el.blur(function() {
					el.parents('form').children('#stopValidation').val('0'); //reset stop mechanism, because this may take pace after a previous validation and now the user has corrected the problem
					if(el.val().replace(/^(\s*)$/, '') == "") { //trim whitespace and compare to empty string
						el.val(def); //reset to default
					}
				});
				
				//listener to alert user and focus elememt
				el.bind('blame', function(event, msg){
					// alert([msg]); //TODO replace with jquery.dialog
					$('#dialog').html(msg);
					$("#dialog").dialog({
						title: "WTF",
						modal:true,
						close: function(event, ui) { $(this).dialog('destroy'); }
					});
					el.focus();
				});
				
				el.parents('form').bind('submit', function() { 
				//has the stop mechnism been triggered
					if (el.parents('form').children('#stopValidation').val() == '1'){
						return false; //don't submmit form
					} 
					else {
						var checkval = true;
						if (typeof settings.check == 'function'){
							checkval = settings.check(el);
						}
						//is there potential for error (required or custom check funciton)
						if (settings.required || checkval != true){
							//check and store if val is default
							var isDefault = ((!el.is('input:checkbox') && el.val() == def) 
							|| (el.is('input:checkbox') && !el.attr('checked')));
							if ((isDefault && settings.required) || (!isDefault && checkval != true)){  //are we stopping
								el.parents('form').children('#stopValidation').val('1'); //trigger stop mechanism
								if((!isDefault || !settings.required) && checkval != false	&& typeof checkval == "string" ){
									el.trigger('blame', [checkval]); //the stop comes with a unique message
								}
								else{
									el.trigger('blame', [msg]); //just a regular stop: output the default message
								}
								return false; //do not submit form
							}
						}
						else if (el.val() == def){
								el.val(""); //pass this valid default value as an empty string
						}
					}
				});
				
			})	//endof each
		);	//endof return
	//return this;
	};
	
	/**
	*Default options
	*to overide for all calls to subsequent calls to validvalue:
	*		$.fn.validvalue.defaults.required = false;
	*/
	$.fn.validvalue.defaults = {
			required: true,
			check: null //function(el){return true;}
	};
})(jQuery);