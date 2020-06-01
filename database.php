<?php
# Class for working with an XML database representation
# Has a limited number of functions for tasks from specification
# The functional can be later expanded if needed
class Database {
  private $db;
  private $is_connected = False;

  # Load an XML database to a variable
  function connect() {
    if ($this->is_connected) return True;

    $file = simplexml_load_file('db.xml');
    if ($file) {
      $this->db = $file;
      $this->is_connected = True;
      return True;
    } else {
      return $file;
    }
  }

  # Creates a new row in the users table and sets column values to passed args
  # Assigns a unique ID to the new row
  # Does not check any constraints. Does not prevent injections
  # Saves the database to a file
  function insert_user($login, $password, $salt, $email, $name) {
    if (!$this->is_connected) return False;

    $last_row = $this->db->users->addChild('row');
    $last_id = $this->next_id($this->db->users);

    $last_row->addChild('id', $last_id);
    $last_row->addChild('login', $login);
    $last_row->addChild('password', $password);
    $last_row->addChild('salt', $salt);
    $last_row->addChild('email', $email);
    $last_row->addChild('name', $name);
    return $this->save();
  }

  # Same as insert_user but for sessions table
  function insert_session($user_id, $session_id) {
    if (!$this->is_connected) return False;

    $last_row = $this->db->sessions->addChild('row');
    $last_id = $this->next_id($this->db->sessions);

    $last_row->addChild('id', $last_id);
    $last_row->addChild('userID', $user_id);
    $last_row->addChild('sessionID', $session_id);
    return $this->save();
  }

  # Gets user by login
  # Returns an object representation of XML row
  # Returns null if no user is found
  function get_user($login) {
    if (!$this->is_connected) return False;

    $users = $this->db->users->row;
    foreach ($users as $user) {
      if ($user->login == $login) {
        return $user;
      }
    }
    return null;
  }

  # Gets session by user ID
  # Same as get_user()
  function get_session($user_id) {
    if (!$this->is_connected) return False;

    $sessions = $this->db->sessions->row;
    foreach ($sessions as $session) {
      if ($session->userID == (string)$user_id) {
        return $session;
      }
    }
    return null;
  }

  # Doesn't do anything for now, can fix later
  function remove_session($row) {
    if (!$this->is_connected) return False;

    $this->save();
  }

  # Checks field for uniqueness only in user table scope
  # Method is a simple loop, which will work OK for the test environment
  function is_unique($field_name, $value) {
    $rows = $this->db->users->row;
    foreach ($rows as $row) {
      if ($row->$field_name == $value) return False;
    }
    return True;
  }

  # Save the database as an XML file
  private function save() {
    if ($this->is_connected) {
      return $this->db->asXML('db.xml');
    } else {
      return False;
    }
  }

  # Increases the lastID of the given table (assuming it has such node)
  # Returns its value
  private function next_id($table) {
    $new_id = (int)$table->lastID + 1;
    $table->lastID = $new_id;
    return $new_id;
  }
}
?>
