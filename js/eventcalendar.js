cj(function() {
  cj('input[id^=event_]').each(function(){
    var event_id = cj(this).prop('id').replace('event_', '');
    showhidecolorbox(event_id);
  });

  cj('.toggle').hide();

  var checkboxes = cj('input[type=checkbox]');
  var deleteboxes = [];
  for (var i = 0; i < checkboxes.length; i++) {
    var split = cj(checkboxes[i]).attr('id').split('_');
    if (split[0] == 'delete' && split[1] == 'calendar') {
      deleteboxes.push(checkboxes[i]);
      cj(checkboxes[i]).parent().parent().removeClass('toggle').addClass('deleteboxes');
    }
  }
  var deleteSection = cj('#delete_current_calendars').parent().parent();
  cj(deleteboxes).parent().parent().insertAfter(deleteSection);

  cj('#create_new_calendar').click(function(){
    if (cj('#create_new_calendar').is(':checked')) {
      cj('.toggle').show("slow");
      cj('#edit_existing_calendar').attr('checked', false);
      cj('#delete_current_calendars').attr('checked', false);
      cj('#update_id').add('label[for="update_id"]').add('#edit_existing_calendar').add('label[for="edit_existing_calendar"]').add('#edit_description').hide();
      cj('#delete_current_calendars').add('label[for="delete_current_calendars"]').add('#delete_description').hide();
    } else {
      cj('.toggle').hide();
      cj('#edit_existing_calendar').add('label[for="edit_existing_calendar"]').add('#delete_current_calendars').add('label[for="delete_current_calendars"]').add('#delete_description').add('#edit_description').show();
    }
  });

  cj('#edit_existing_calendar').click(function(){
    if (cj('#edit_existing_calendar').is(':checked')) {
      cj('.toggle').add('#update_id').add('label[for="update_id"]').show("slow");
      cj('#create_new_calendar').attr('checked', false);
      cj('#delete_current_calendars').attr('checked', false);
      cj('#create_new_calendar').add('label[for="create_new_calendar"]').add('#create_description').hide();
      cj('#delete_current_calendars').add('label[for="delete_current_calendars"]').add('#delete_description').hide();
    } else {
      cj('.toggle').hide();
      cj('#create_new_calendar').add('label[for="create_new_calendar"]').add('#delete_current_calendars').add('label[for="delete_current_calendars"]').add('#delete_description').add('#create_description').show();
    }
  });

  cj('#delete_current_calendars').click(function(){
    if (cj('#delete_current_calendars').is(':checked')) {
      cj('#create_new_calendar').attr('checked', false);
      cj('#edit_existing_calendar').attr('checked', false);
      cj('#create_new_calendar').add('label[for="create_new_calendar"]').add('#create_description').hide();
      cj('#edit_existing_calendar').add('label[for="edit_existing_calendar"]').add('#edit_description').hide();
      cj(deleteboxes).parent().parent().show("slow");
    } else {
      cj(deleteboxes).parent().parent().hide();
      cj('#create_new_calendar').add('label[for="create_new_calendar"]').add('#edit_existing_calendar').add('label[for="edit_existing_calendar"]').add('#create_description').add('#edit_description').show();
    }
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
