(function ($, undefined) {

$.widget("ui.gridito", {

	options: {},

	_create: function () {
		var _this = this;
		
		// buttons
		this.element.find("a.gridito-button").each(function () {
			var el = $(this);
			
			// window button
			if (el.hasClass("gridito-window-button")) {
				el.click(function (e) {
					e.stopImmediatePropagation();
					e.preventDefault();
			
					var win = $('<div></div>').appendTo('body');
					win.attr("title", $(this).attr("data-gridito-window-title"));
					win.load(this.href, function () {
						win.dialog({
							modal: true
						});
						win.find("input:first").focus();
					});
				});
			}
			
			if (el.attr("data-gridito-question")) {
				el.click(function (e) {					
					if (!confirm($(this).attr("data-gridito-question"))) {
						e.stopImmediatePropagation();
						e.preventDefault();
					}
				});
			}
		});
	}
	
});

})(jQuery);


$.extend({
    stopedit : function(who) {
        var span = who.data('span');
        span.text(who.val());
        span.attr('data-value', who.val());
        var data = new Object();
        data[span.attr('data-name')] = who.val();
        data['id'] = span.attr('data-id');
        $.post(span.attr('data-url'), data);
        who.replaceWith(span);
               },
    startedit: function(who) {
        var input = $('<input type="text"></input>');
        input.data('span', who);
        input.val(who.attr('data-value'));
        input.addClass('editable');
        input.blur(function(){$.stopedit($(this))});
        input.keyup(function(event)
            {if (event.keyCode == 27) {input.blur()}}
            );
        who.replaceWith(input);
        input.focus();
          },
});

$('span.editable').live('click', function(event) {
    $.startedit($(this));
});
