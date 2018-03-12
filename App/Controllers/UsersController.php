<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Mailer;
use \App\System\Auth;

class UsersController extends Controller {

    public function all() {
        $model = new UsersModel();
        $data  = $model->all();

        $this->render('pages/admin/users.twig', [
            'title'       => 'Users',
            'description' => 'Users - Just a simple inventory management system.',
            'page'        => 'users',
            'users'    => $data
        ]);
    }

    /*
    This function is used when the administrator adds a user from the administrator dashboard
    */
    public function add() {
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) {
            $username              = isset($_POST['username']) ? $_POST['username'] : '';
            $email                 = isset($_POST['email']) ? $_POST['email'] : '';
            $password              = isset($_POST['password']) ? $_POST['password'] : '';
            $password_verification = isset($_POST['password_verification']) ? $_POST['password_verification'] : '';

            $validator = new FormValidator();
            $validator->validUsername('username', $username, "Your username is not valid (no spaces, uppercase, special character)");
            $validator->availableUsername('username', $username, "Your username is not available");
            $validator->validEmail('email', $email, "Your email is not valid");
            $validator->validPassword('password', $username, $password, $password_verification); // Changed, by removing the message

            if($validator->isValid()) {
                $model = new UsersModel();
                $model->create([
                    'username'   => $username,
                    'email'      => $email,
                    'password'   => hash('sha256', Settings::getConfig()['salt'] . $password),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                App::redirect('admin/users');
            }

            else {
                $this->render('pages/admin/users_add.twig', [
                    'title'       => 'Add user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ]
                ]);
            }
        }

        else {
            $this->render('pages/admin/users_add.twig', [
                'title'       => 'Add user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users'
            ]);
        }
    }

    public function registrationIsValid($validator, $username, $email, $password, $password_verification): bool {

            if ($validator->notEmpty('username',$username, "Your username can't be empty")){
                $validator->validUsername('username2', $username, "Your username is not valid (no spaces, uppercase, special character)");
            }

            $validator->availableUsername('username', $username, "Your username is not available");

            if ($validator->notEmpty('email', $email, "Your email can't be empty")){
              $validator->validEmail('email', $email, "Your email is not valid");
            }

            if ($validator->notEmpty('password',$password, "Your password can't be empty")){
                $validator->validPassword('password2', $username, $password, $password_verification); // Changed, added so it sends username too
            }

            if($validator->isValid()) {
                return true;
            }else{
                return false;
            }
    }

    public function createNewUser($username, $email, $password, $password_verification){
        $model = new UsersModel();

                $model->create([
                    'username'   => $username,
                    'password'   => hash('sha256', Settings::getConfig()['salt'] . $password),
                    'created_at' => date('Y-m-d H:i:s'),
                    'admin'      => 0,
                    'email'      => $email, // Added because of error when there is no default for email, can maybe be removed after email is implemented
                    'active'     => 0,
                    'active_hash'=> md5(rand(0,1000))

                ]);
    }

    /* This function is used when a non-administrator registers a new user*/
    public function registrateUser() {
        $mail=new Mailer;
        $validator = New FormValidator;
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) {
            $username              = isset($_POST['username']) ? $_POST['username'] : '';
            $email                 = isset($_POST['email']) ? $_POST['email'] : '';
            $password              = isset($_POST['password']) ? $_POST['password'] : '';
            $password_verification = isset($_POST['password_verification']) ? $_POST['password_verification'] : '';

            if($this->registrationIsValid($validator, $username, $email, $password, $password_verification)) {

                $this->createNewUser($username, $email, $password, $password_verification);

                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors(),
                'message'     => ('Registration successful!')
                ]);
            }

            else {
                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors()
        ]);
            }
        }

        else {
            $this->render('pages/registration.twig', [
            'title'       => 'Registrate',
            'description' => 'Registrate a new user',
            'errors'      => $validator->getErrors(),
        ]);
        }
    }

    public function edit($id) {
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $email    = isset($_POST['email']) ? $_POST['email'] : '';

            $validator = new FormValidator();
            $validator->validUsername('username', $username, "Your username is not valid (no spaces, uppercase, special character)");
            $validator->validEmail('email', $email, "Your email is not valid");

            if($validator->isValid()) {
                $model = new UsersModel();
                $model->update($id, [
                    'username' => $username,
                    'email'    => $email
                ]);

                if($_SESSION['id'] == $id) {
                    $this->logout();
                    App::redirect('signin');
                }

                else {
                    App::redirect('admin/users');
                }
            }

            else {
                $this->render('pages/admin/users_edit.twig', [
                    'title'       => 'Edit user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ]
                ]);
            }
        }

        else {
            $model = new UsersModel();
            $data = $model->find($id);

            $this->render('pages/admin/users_edit.twig', [
                'title'       => 'Edit user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data
            ]);
        }
    }

    public function delete($id) {
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"])) {
            $model = new UsersModel();
            $model->delete($id);

            App::redirect('admin/users');
        }

        else {
            $model = new UsersModel();
            $data = $model->find($id);
            $this->render('pages/admin/users_delete.twig', [
                'title'       => 'Delete user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data
            ]);
        }
    }

    public function viewSQL($id) {
        echo var_dump($this->userRep->find($id)); die;
    }

    public function logout() {
        setcookie('user', '', time()-3600, '/', null, false, 1); // Added
        //setcookie('admin', '', time()-3600, '/', null, false, 1); // Added
        setcookie('password', '', time()-3600, '/', null, false, 1); // Added
        session_unset(); // Added
        session_destroy();
        App::redirect();
    }

}
