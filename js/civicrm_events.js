(function ($, _) {
	$(function() {
		$(".full-calendar").each(function(){
			if ($(this).data('fullCalendar')) return;
			$(this).fullCalendar($(this).data('events-data'));	
		});	
	});
}(CRM.$, CRM._));
