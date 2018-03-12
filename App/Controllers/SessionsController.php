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
      if (!isset($_SESSION['locked_until'])) { // Added
        $_SESSION['locked_until'] = time() -3600; // Added
      }
      if (!isset($_SESSION['last_password'])) { // Added
        $_SESSION['last_password'] = ""; // Added
      }

      if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) { // Added
          $username = isset($_POST['username']) ? $_POST['username'] : '';

          $password = isset($_POST['password']) ? hash('sha256', Settings::getConfig()['salt'] . $_POST['password']) : '';

          //add

          $refresh = $_SESSION['last_password']  === $password;
          $locked_out = $_SESSION['locked_until'] > time();

        if ($refresh) { // Added
          if ($locked_out) {
            $errors = [
              "You have had " . $_SESSION['failed_attempts'] . " failed logins. Your account is now locked for " . $_SESSION['time_lockout_sec'] . " seconds."
            ];
          }
        }
        else {
          if($this->auth->checkCredentials($username, $password)) {
              $_SESSION['failed_attempts'] = 0; // Added
              session_regenerate_id(); // Added
              //setcookie("user", $username, '/', null, false, 1); // Changed
              //setcookie("password",  $_POST['password'], '/', null, false, 1); // Changed
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
                "Your username and your password don't match or your account is not activated"
            ];
            if (!$refresh) {
              $_SESSION['failed_attempts'] = ++$_SESSION['failed_attempts'];  // Added
            }
            if( $_SESSION['failed_attempts'] < 3 and !$refresh) {
              array_push($errors, "You have had " . $_SESSION['failed_attempts'] . " failed logins. After 3, your account will be locked for an increasing timeinterval.");
            }
            else if( $_SESSION['failed_attempts'] == 3 and !$refresh) {
              $this->increase_session_lockout();
              array_push($errors, "You have had 3 failed logins. Your account is now locked for " . $_SESSION['time_lockout_sec'] . " seconds.");
            }
            else if ( $_SESSION['failed_attempts'] >3) {
              // Increase time interval
              $this->increase_session_lockout();
                            }
            //App::redirect('signin'); // Added, to empty POST form TODO not done
          }
          $_SESSION['last_password'] = $_POST['password'];
        }
      }

      // Update sec left showed to user
      $_SESSION['time_lockout_sec'] = $_SESSION['locked_until'] - time();

      $this->render('pages/signin.twig', [
          'title'       => 'Sign in',
          'description' => 'Sign in to the dashboard',
          'errors'      => isset($errors) ? $errors : '',
          'time_lockout_sec' => $_SESSION['time_lockout_sec'],
      ]);
    }

    public function logout() { // TODO: is this every used?
      echo "Logout in SessionsController"; die;
      session_destroy(); // Added
      setcookie('user', '', time()-3600, '/', null, false, 1); // Added
      App::redirect();
    }

    // Added
    private function calculate_time() {
      $attempts = $_SESSION['failed_attempts'] - 3;
      $time = 10;
      for ($x = 1; $x <= $attempts; $x++) {
        $time = $time * 2;
      }
      return $time;
    }
    // Added
    private function increase_session_lockout() {
      $new_time = 0;
      if ($_SESSION['failed_attempts'] == 3) {
        $new_time = 10;
        $_SESSION['time_lockout_sec'] = 10;
      } else {
        $new_time = $this->calculate_time();
      }
      $_SESSION['locked_until'] = time() + $new_time;
    }
}
