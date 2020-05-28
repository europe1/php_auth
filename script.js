$(function() {
  $.get('users.php', function(data) {
    if (data.code === 200) {
      setWelcomeText(data.userName);
    } else {
      $('#actionBlock').show();

      // So that form cannot be sent without JS (at least right away)
      $('#loginForm').append('<input type="hidden" name="action" value="get">');
      $('#loginForm').append('<input type="submit" class="btnStyle" value="Login">');

      $('#loginForm').submit(function(e) {
        // Form doesn't redirect
        e.preventDefault();
        $.post('users.php', $('#loginForm').serialize(), function(data) {
          if (data.code === 200) {
            setWelcomeText(data.userName);
            $('#actionBlock').hide();
          } else {
            setErrorText(data.text);
          }
        }, 'json');
      });
    }
  }, 'json');
});

function setWelcomeText(name) {
  $('#welcomeText').text('Hello, ' + name + '.');
}

function setErrorText(text) {
  $('#errorText').text(text);
}
