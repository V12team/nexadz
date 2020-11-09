// Wait for the DOM to be ready
$(function() {
  // Initialize form validation on the registration form.
  // It has the name attribute "registration"
  $("form[name='new_campaign']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
    'name': "required",
    'type[]': "required",
    'networks[]': "required",
    'languages[]': "required",
    'weekly_budget': {
        required: true,
        number: true,
      }
    },
    // Specify validation error messages
    messages: {
      'name': "Please enter your campaign name",
      'type[]': "Please select at least one campaign type",
      'networks[]': "Please select at least one network",
      'languages[]': "Please select at least one language",
      'weekly_budget': "Please enter a valid weekly budget"
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      $('#load').show();
      form.submit();
    }
  });
});

$("#button").click(function(e) {
  
  e.preventDefault();
    if ($("#new_campaign").valid()) {
        $("#new_campaign").submit();
    }
});

$('#cancel').click(function(e){
  e.preventDefault();
  location.reload();
});
