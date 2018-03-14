<?php
namespace App\Models;

use \App\System\App;
use \App\Models\Model;
use \App\System\Auth;
use \App\System\Database;

class UsersModel extends Model {

    protected $table = "users";

    public function login($username, $passwordHash, $email, $activeHash) {
            if($userRow) {
                if($userRow->password === $passwordHash) {
                    $_SESSION['auth'] = $userRow->id;
                    return true;
                }
        } else {
          echo 'account not activated';
          return false;
        }
        return false;
    }

    public static function logged(){
        if(!isset($_SESSION['auth'])) {
            App::redirect('signin');
            exit;
        }
    }

    public function getUserRow($username){
        return parent::query('SELECT * FROM users WHERE username = ?', [$username] , true);
    }

    public function getPasswordHash($username){
        $userRow = $this->getUserRow($username);
        return $userRow->password;
    }

    public function getId($username){
        $userRow = $this->getUserRow($username);
        return $userRow->id;
    }

    public function getEmail($username){
        $userRow = $this->getUserRow($username);
        return $userRow->email;
    }

    public function getAdmin($username){
        $userRow = $this->getUserRow($username);
        return $userRow->admin;
    }

    public function getActive($username){
        $userRow = $this->getUserRow($username);
        return $userRow->active;
    }

    public function getActiveHash($username){
        $userRow = $this->getUserRow($username);
        return $userRow->active;
    }

}
