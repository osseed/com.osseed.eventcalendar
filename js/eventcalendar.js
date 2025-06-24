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

  // Show/hide based on checkbox
  $('input[id^=event_]').each(function () {
    var event_id = this.id.replace('event_', '');
    var colorInput = $("#eventcolorid_" + event_id);
    var checkbox = $("#event_" + event_id);
    if (!checkbox.is(':checked')) {
      colorInput.parents('.crm-section').hide();
    } else {
      colorInput.parents('.crm-section').show();
    }
  });
});
