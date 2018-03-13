<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Auth;

class SessionsController extends Controller {

    // Most of the code here is changed (everything related to locking of account and numbers of tries)
    public function login() {

      $ip = $_SERVER['REMOTE_ADDR'];
      $this->create_row_if_not_exists($ip);
      
      if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) { // Added
          $username = isset($_POST['username']) ? $_POST['username'] : '';

          $password = isset($_POST['password']) ? hash('sha256', Settings::getConfig()['salt'] . $_POST['password']) : '';
          
          $last_password = isset($_SESSION['last_password']) ? $_SESSION['last_password'] : '';
          $this_password = $_POST['password'];
          
          $refresh = $this->is_refresh($last_password, $this_password);
          $locked_out = $this->is_locked_out($ip);

        if ($locked_out) {
          $errors = [
            "You have had " . $this->get_existing_attempts($ip) . " failed logins. Your account is now locked for " . $this->get_locked_sec($ip) . " seconds."
          ];
        }
        else {
          if($this->auth->checkCredentials($username, $password)) {
              $this->reset_failed_attempts($ip); // Added
              session_regenerate_id(); // Added
              // Have removed this
              /*
              if ($this->userRep->getAdmin($username)){
                  setcookie("admin", 'yes', '/', null, false, 1); // Changed
              }else{
                  setcookie("admin", 'no', '/', null, false, 1); // Changed
              }
              */
              $_SESSION['auth']       = $username;
              $_SESSION['id']         = $this->userRep->getId($username);
              $_SESSION['email']      = $this->userRep->getEmail($username);
              $_SESSION['password']   = $password;

              App::redirect('dashboard');
          } 
          else {
            $errors = [
                "Your username and your password don't match."
            ];
            if (!$refresh) {
              $this->register_failed_attempt($ip); // Added
            }
            if( $this->get_existing_attempts($ip) < 3 and !$refresh) {
              array_push($errors, "You have had " . $this->get_existing_attempts($ip) . " failed logins. After 3, your account will be locked for an increasing timeinterval.");
            }
            else if( $this->get_existing_attempts($ip) === 3 and !$refresh) {
              $this->increase_session_lockout();
              array_push($errors, "You have had 3 failed logins. Your account is now locked for " . $this->get_locked_sec($ip) . " seconds.");
            } 
            
          }
          
          $_SESSION['last_password'] = $_POST['password'];
        }
      }

      $this->render('pages/signin.twig', [
          'title'       => 'Sign in',
          'description' => 'Sign in to the dashboard',
          'errors'      => isset($errors) ? $errors : '',
          'time_lockout_sec' => $this->get_locked_sec($ip),
      ]);
    }

    public function logout() {
      session_destroy(); // Added
      setcookie('user', '', time()-3600, '/', null, false, 1); // Added
      App::redirect();
    }

    // Added
    public function is_refresh($last_password, $this_password) {
      $refresh = strcmp($last_password, $this_password);

      if ($refresh == 0) {
        return true;
      } else {
        return false;
      }
    }

    // Added
    public function is_locked_out($ip) {
      $locked_until = $this->get_locked_until($ip);

      if ($locked_until > time()) {
        return true;
      } else {
        return false;
      }
    }
    
    // Added
    private function calculate_time($failed_attempts) {
      $attempts = $failed_attempts - 3;
      $time = 10;
      for ($x = 1; $x <= $attempts; $x++) {
        $time = $time * 2;
      }
      return $time;
    }
  
    // Added
    private function register_failed_attempt($ip) {
      $this->create_row_if_not_exists($ip);

      $existing_attempts = $this->get_existing_attempts($ip);
      $failed_attempts = $existing_attempts + 1;

      $query = "";
      if ($failed_attempts >= 3) {
        $new_time_sec = $this->calculate_time($failed_attempts);
        $new_locked_until = time() + $new_time_sec;

        $locked_until_timestamp = date('Y-m-d H:i:s', $new_locked_until);

        $query = "UPDATE lockout SET failed_attempts=$failed_attempts, locked_until='$locked_until_timestamp' WHERE ip='{$ip}'";
      } else {
        $query = "UPDATE lockout SET failed_attempts=$failed_attempts WHERE ip='{$ip}'";
      }
      
      App::getDb()->execute($query);
    }

    // Added
    private function get_existing_attempts($ip) {
      $query = "SELECT failed_attempts FROM lockout WHERE ip = '{$ip}'";

      $res = App::getDb()->query($query, true);
      $failed_attempts = $res->failed_attempts;

      return $failed_attempts;
    }

    // Added
    private function get_locked_until($ip) {    
      $query = "SELECT locked_until FROM lockout WHERE ip = '{$ip}'";

      $res = App::getDb()->query($query, true);
      $date = $res->locked_until;

      return strtotime($date);
    }

    // Added
    private function get_locked_sec($ip) {
      $locked_until = $this->get_locked_until($ip);

      $difference = $locked_until - time();
      return $difference;
    }

    // Added
    private function reset_failed_attempts($ip) {
      $query = "UPDATE lockout SET failed_attempts=0 WHERE ip= '{$ip}'";

      App::getDb()->execute($query);
    }

    // Added
    private function create_row_if_not_exists($ip) {
      $query = "SELECT * FROM lockout WHERE ip = '{$ip}'";
  
      $result = App::getDb()->query($query, true);
      if ($result == "" ) {
        $this->create_lockout_row($ip);
      }
    }

    // Added
    private function create_lockout_row($ip) {
      $locked_until = date('Y-m-d H:i:s', time() - 3600);

      $query = "INSERT IGNORE INTO lockout (ip, failed_attempts, locked_until) VALUES ('{$ip}', ". 0 . ", '{$locked_until}')";

      App::getDb()->execute($query, false);
    }

}
