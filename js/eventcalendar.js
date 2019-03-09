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

CRM.$(function($) {
  function updatecolor(label, color) {
    $('input[name="'+label+'"]').val( color );
  }

  $('input[id^=event_]').each(function(){
    var event_id = $(this).prop('id').replace('event_', '');
    var n = "eventcolorid_" + event_id;
    var m = "event_" + event_id;
    if(!$("#"+m).is( ':checked')) {
      $("#"+n).parents('.crm-section').hide();
    }
    else {
      $("#"+n).parents('.crm-section').show();
    }
  });
});
