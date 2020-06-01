<?php
include 'database.php';

session_start();
if (isset($_SESSION['name'])) {
  ok_response();
  die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
  error_response(403, 'Forbidden.');
  die();
}

switch ($_POST['action']) {
  case 'create':
    if (empty($_POST['login']) || empty($_POST['password']) ||
      empty($_POST['confirm_password']) || empty($_POST['email']) ||
      empty($_POST['name'])) {
      error_response(400, 'All fields must be filled.');
    } else if ($_POST['password'] !== $_POST['confirm_password']) {
      error_response(400, 'Password must be identical.');
    } else {
      $login = addslashes($_POST['login']);
      $email = addslashes($_POST['email']);
      $name = addslashes($_POST['name']);

      $db = new Database();
      $db->connect();

      if (!$db->is_unique('login', $login)) {
        error_response(403, 'There is a user with such login.');
        break;
      }

      if (!$db->is_unique('email', $email)) {
        error_response(403, 'There is a user with such email.');
        break;
      }

      # Personally I would use password_hash() for hashing passwords
      # and password_verify() for their verification.
      # But the task specifies using salt + md5 or sha1, OK then
      $salt = random_bytes(8);
      $password_hash = hash('sha1', $salt . $_POST['password']);

      # Using bin2hex() for salt so it can be saved in text
      $db->insert_user($login, $password_hash, bin2hex($salt), $email, $name);
      ok_response();
    }
    break;
  case 'get':
    if (empty($_POST['login']) || empty($_POST['password'])) {
      error_response(400, 'All fields must be filled.');
      break;
    }

    $db = new Database();
    $db->connect();
    # Escaping characters that needs to be escaped
    # As the database is an XML file, using only addslashes()
    $user = $db->get_user(addslashes($_POST['login']));
    if (is_null($user)) {
      error_response(403, 'User not found.');
    } else {
      $password_hash = (string)$user->password;
      # Converting salt back to binary
      $input_hash = hash('sha1', hex2bin($user->salt) . $_POST['password']);
      if (hash_equals($password_hash, $input_hash)) {
        $_SESSION['name'] = (string)$user->name;

        # Remove previous user session if exists in DB
        # Doesn't work for now
        $session = $db->get_session($user->id);
        if (!is_null($session)) {
          $db->remove_session($session);
        }

        $db->insert_session($user->id, session_id());
        ok_response();
      } else {
        error_response(403, 'Username or password are incorrect.');
      }
    }
    break;
  default:
    error_response(400, 'Bad request.');
}

function json_response($obj) {
  header('Content-Type: application/json');
  $json_response = json_encode($obj);
  echo $json_response;
}

function error_response($code, $text) {
  $response = new stdClass();
  $response->code = $code;
  $response->text = $text;
  json_response($response);
}

function ok_response() {
  $response = new stdClass();
  $response->code = 200;
  $response->text = 'OK.';
  if (isset($_SESSION['name'])) {
    $response->userName = $_SESSION['name'];
  }
  json_response($response);
}
?>
