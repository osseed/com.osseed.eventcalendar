(function ($, _) {
	$(function() {
		var events_data = $(".full-calendar").data('events-data');
		$(".full-calendar").fullCalendar(events_data);		
	});
}(CRM.$, CRM._));
