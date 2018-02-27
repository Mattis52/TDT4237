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

                App::redirect('dashboard');
            }

            else {
              echo "before: " . $_SESSION['failed_attempts'];
              $_SESSION['failed_attempts'] = ++$_SESSION['failed_attempts']; // = $_SESSION['failed_attempts'] + 1;
              echo $_SESSION['failed_attempts'];
              $errors = [
                  "Your username and your password don't match."
              ];
              if( $_SESSION['failed_attempts'] < 3) {
                array_push($errors, "You have had " . $_SESSION['failed_attempts'] . " failed logins. After 3, your account will be locked for an increasing timeinvertal, and the admin will be informed.");
              }
              else if( $_SESSION['failed_attempts'] == 3) {
                // Lock account
                $_SESSION['time_lockout'] = true;
                array_push($errors, "You have had 3 failed logins. Your account is now locked for 30 seconds, and the admin is informed.");
                // Start time interval
                $this->increase_session_lockout();
                $time_lockout = true;

              } else if ( $_SESSION['failed_attempts'] >3) {
                // Increase time interval
                $this->increase_session_lockout();
                $time_lockout = true;
              }
            }
        }

        $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : '',
            'time_lockout' => isset($time_lockout) ? $time_lockout : false,
        ]);
    }

    public function logout() { // TODO: is this every used?
      echo "Calling logout in SessionsController";
        session_destroy(); // Added
        setcookie('user', '', time()-3600); // Added
        //App::redirect();
    }

    // Added NOT WORKING
    function setInterval($sec) {
      echo "Setting interval: " .  $sec;
      while (true) {
        //sleep($sec);
        $this->open_account();
      }
    }
    // Added
    private function increase_session_lockout() {
      $new_time;
      if ($_SESSION['time_lockout'] == 0) {
        $new_time = 60;
        $_SESSION['time_lockout'] = 60;
      } else {
        $new_time = $_SESSION['time_lockout']**2;
      }
      //$timeout = $this->setInterval($new_time);
    }

    // Added
    private function open_account() {
      $_SESSION['failed_attempts'] = 0;
      $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : ''
        ]);

    }
}
