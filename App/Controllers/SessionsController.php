<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Auth;

class SessionsController extends Controller {

    public function login() {

        $_SESSION['failed_attempts'] = 0; // Added, maybe move to where session is created
        $_SESSION['time_lockout'] = 0;
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) {            
            $username = isset($_POST['username']) ? $_POST['username'] : '';

            $password = isset($_POST['password']) ? hash('sha256', Settings::getConfig()['salt'] . $_POST['password']) : '';
            
            $refresh = $_SESSION['last_password']  === $password;
            $locked_out = $_SESSION['locked_until'] > time();

            if ($locked_out or $refresh) {
              if ($locked_out) {
                $errors = [
                  "You have had " . $_SESSION['failed_attempts'] . " failed logins. Your account is now locked for " . $_SESSION['time_lockout_sec'] . " seconds, and the admin is informed."
                ];
              }
              else if (isset($_SESSION['locked_until']) and $_SESSION['locked_until'] < time()) {
                echo "Should lockup now";
              }
            } 
            else { 
              if($this->auth->checkCredentials($username, $password) and !$_SESSION['time_lockout'] = 0) {
                $_SESSION['failed_attempts'] = 0; // Added
                session_regenerate_id(); // Added
                setcookie("user", $username);
                setcookie("password",  $_POST['password']);
                // TODO: Could maybe also just delete this?
                if ($this->userRep->getAdmin($username)){
                    setcookie("admin", 'yes');
                }else{
                    setcookie("admin", 'no');
                }
                $_SESSION['auth']       = $username;
                $_SESSION['id']         = $this->userRep->getId($username);
                $_SESSION['email']      = $this->userRep->getEmail($username);
                $_SESSION['password']   = $password;

                App::redirects('dashoard')
              }
              else {
                $errors = [
                    "Your username and your password don't match."
                ];
                if (!$refresh) {
                  $_SESSION['failed_attempts'] = ++$_SESSION['failed_attempts'];  // Added
                }
                if( $_SESSION['failed_attempts'] < 3 and !$refresh) {
                  array_push($errors, "You have had " . $_SESSION['failed_attempts'] . " failed logins. After 3, your account will be locked for an increasing timeinterval, and the admin will be informed.");
                }
                else if( $_SESSION['failed_attempts'] == 3 and !$refresh) {
                  // Warn admin
                  $model = new UsersModel();
                  $email_addr = $model->getEmail('root'); // Assumes admin is named "root" 
                  $message = 'Somebody from are trying to log in and have spent ' . $_SESSION['failed_attempts'] . ' attempts.';
                  // TODO: don't know how to get IP adress or something similar
                  mail($email_addr, 'To many attempts at login', $message);
                  // Start time interval
                  $this->increase_session_lockout();
                  array_push($errors, "You have had 3 failed logins. Your account is now locked for " . $_SESSION['time_lockout_sec'] . " seconds, and the admin is informed.");
                } 
                else if ( $_SESSION['failed_attempts'] >3) {
                  // Increase time interval
                  $this->increase_session_lockout();
                }
                //App::redirect('signin'); // Added, to empty POST form
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
      echo "Calling logout in SessionsController";
        session_destroy(); // Added
        setcookie('user', '', time()-3600); // Added
        App::redirect();
    }
    
    // Added
    private function increase_session_lockout() {
      $last_time = $_SESSION['failed_attempts'] ** 2; // TODO: not correct algorithm
      $new_time = 0;
      if ($_SESSION['failed_attempts'] == 3) {
        $new_time = 60;
        $_SESSION['time_lockout_sec'] = 60;
      } else {
        $new_time = $last_time**2;
      }
      $_SESSION['locked_until'] = time() + $new_time;
    }
}
