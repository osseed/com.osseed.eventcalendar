(function ($, _) {
	$(function() {
		$(".full-calendar").each(function(){
			if ($(this).data('fullCalendar')) return;
			$(this).fullCalendar($.extend({
				eventRender: function(event, element, view) {
					//~ console.log(event, element, view);
					element.on('click',function(e){
						e.preventDefault();
					});					
					element.find('.fc-event-inner').prepend(
						'<span class="fc-multiple-add pull-right pointer hidden-print" style="padding: 0 3px; cursor: pointer;" title="Register participants"><i class="fa fa-plus-square-o"></i></span>'+
						'<span class="fc-view-event pull-right pointer hidden-print" style="padding: 0 3px; cursor: pointer;" title="View event details"><i class="fa fa-share"></i></span>'
					);
					element.find('.fc-multiple-add').on('click',function(){
						document.location.href = CRM.url('civicrm/lalgbt/multipleregister', {
							eventids: [event.event_id],
							reset: 1,
						});
					});
					element.find('.fc-view-event').on('click',function(){
						document.location.href = event.url;
					});
				},
			},$(this).data('events-data')));	
		});	
	});
}(CRM.$, CRM._));
