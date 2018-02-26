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
        if(!empty($_POST)) {
            
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            //$password = isset($_POST['password']) ? hash('sha1', Settings::getConfig()['salt'] . $_POST['password']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if($this->auth->checkCredentials($username, $password) and !$_SESSION[time_lockout] = 0) {
                $failed_attempts = 0; // Added
                setcookie("user", $username);
                setcookie("password",  $_POST['password']);
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
              $_SESSION['failed_attempts'] += 1;
              $errors = [
                  "Your username and your password don't match."
              ];
              if( $_SESSION['failed_attempts'] < 3) {
                array_push($errors, "You have had", $_SESSION['failed_attempts'], " failed logins. After 3, your account will be locked for an increasing timeinvertal, and the admin will be informed.");
              }
              else if( $_SESSION['failed_attempts'] == 3) {
                // Warn admin
                // Lock account
                $_SESSION['time_lockout'] = true;
                array_push($errors, "You have had 3 failed logins. Your account is now locked for 30 seconds, and the admin is informed.");
                // Start time interval
                increase_session_lockout();
                $time_lockout = true;

              } else if ( $_SESSION['failed_attempts'] >3) {
                // Increase time interval
                increase_session_lockout();
                $time_lockout = true;
              }
            }
        }

        $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : ''
        ]);
    }

    public function logout() {
        App::redirect();
    }

    private function increase_session_lockout() {
      $new_time;
      if ($_SESSION['time_lockout'] == 0) {
        $new_time = 60;
        $_SESSION['time_lockout'] = 60;
      } else {
        $new_time = $_SESSION['time_lockout']**2;
      }
      $timeout = setInterval($open_account(), $new_time);
    }

    private function open_account() {
      $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : ''
        ]);
    }

}
