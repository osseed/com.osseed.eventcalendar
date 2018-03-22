cj(function() {
  cj('input[id^=event_]').each(function(){
    var event_id = cj(this).prop('id').replace('event_', '');
    showhidecolorbox(event_id);
  });
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
