$(function() {
  // So that form cannot be sent without JS (at least right away)
  $('#form').append('<input type="hidden" name="action" value="create">');
  $('#form').append('<input class="btnStyle" type="submit" value="Register">');

  $('#form').submit(function(e) {
    // Form doesn't redirect
    e.preventDefault();

    $.post('users.php', $('#form').serialize(), function(data) {
      if (data.code === 200) {
        window.location.replace('index.html');
      } else {
        setErrorText(data.text);
      }
    }, 'json');
  });
});

function setErrorText(text) {
  $('#errorText').text(text);
}
