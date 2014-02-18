/* jQuery.defaultvalue
 * 
 * Purpose: Sets a default value on a form input. Value is removed on focus and
 *          restored on blur, if no text is entered. The value is removed on form
 *          submission if an alternate value has not been specified
 *
 * Usage: $('input#field_id').defaultvalue('This is the default value')
 *        $("input[name='inputname']").defaultvalue("My Default Value");
 *
 * Morgan Massena - 2008-12-11
 * Brad Kohlenberg - added validatevalue 2008-12-15
 */

(function($) {
	
	$.fn.defaultvalue = function() {
		
		// Scope
		var elements = this;
		var args = arguments;
		var c = 0;
		
		return(
			elements.each(function() {				
				
				// Default values within scope
				var el = $(this);
				var def = args[c++];
				var input = (el[0].tagName == 'INPUT');
				
				// set default value, if empty
				if (el.val() == "") {
					input ? el.val(def) : el.text(def);
				}
				
				el.focus(function() {
					if (el.val() == def) {
						el.val("");
					}
				});
				
				el.blur(function() {
					if(el.val() == "") {
						el.val(def);
					}
				});
				
				el.parents('form').bind('submit', function() {
					if (el.val() == def) {
						el.val("");
					}
				});
				
			})	//endof each
		);	//endof return
	//return this;
	};
})(jQuery);