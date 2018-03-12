<?php
namespace App\Models;

use \App\System\App;
use \App\Models\Model;
use \App\System\Auth;
use \App\System\Database;

class UsersModel extends Model {

    protected $table = "users";

    public function login($username, $passwordHash, $email, $activeHash) {
        $userRow = $this->getUserRow($username);
        //set database admin to 1 on login test
        try{
          Database::query('UPDATE `inventory`.`users` SET `admin`=1 WHERE username=""'.$username.'"', true);
          echo 'worked';
        } catch (Exception $e) {
            echo 'Error: ', $this->ErrorInfo;
        }

        

        //$emailUserinput = 'aa@aa.com'; //username: aaaaa
        //$hashUserInput = 'efe937780e95574250dabe07151bdc23'; //needs to get the hash for url
        if($userRow->active == 1){
            //handleActive($userRow->$username, $userRow->email, $emailUserinput, $userRow->$activeHash, $hashUserInput);
            if($userRow) {
                if($userRow->password === $passwordHash) {
                    $_SESSION['auth'] = $userRow->id;
                    return true;
                }
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

    public function handleActive($emailDb, $emailUserinput, $activeHashDb, $hashUserInput){
      if($emailDb===$emailUserinput && $activeHashDb===$hashUserInput) {
        setUserActive($username);
        return true;
      } else {
        return false;
      }
    }

    public function setUserActive($username){
        Database::query('UPDATE `inventory`.`users` SET `active`=1 WHERE username=""'.$username.'"', true);
    }

    public function getUserRow($username){
        return App::getDb()->query('SELECT * FROM users WHERE username = "' . $username .'"', true);
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
