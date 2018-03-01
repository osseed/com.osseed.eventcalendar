cj(function() {
  cj('input[id^=event_]').each(function(){
    var event_id = cj(this).prop('id').replace('event_', '');
    showhidecolorbox(event_id);
  });

  /*if(!cj("#eventcalendar_event_month").is( ':checked')) {
    cj('.crm-event-extension-show_event_from_month').hide();
  }
  cj('.crm-event-extension-events_event_month').bind('click', function() {
    if(cj("#events_event_month").is( ':checked')) {
      cj('.crm-event-extension-show_event_from_month').show();
      cj('#show_event_from_month').val('');
    } else {
      cj('.crm-event-extension-show_event_from_month').hide();
    }
  });*/
});

function updatecolor(label, color) {
  cj('input[name="'+label+'"]').val( color );
}

function showhidecolorbox(event_id) {
  var n = "eventcolorid_" + event_id;
  var m = "event_" + event_id;
  if(!cj("#"+m).is( ':checked')) {
    cj("#"+n).parents('.crm-section').hide();
  }
  else {
    cj("#"+n).parents('.crm-section').show();
  }
}
