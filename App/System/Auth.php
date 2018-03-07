<?php
namespace App\System;

use \App\Models\UsersModel;

class Auth{

    protected $userRep;

    public function __construct(){
        $this->userRep = new UsersModel;
    }

    public function checkCredentials($username, $passwordHash)
    {
        $user = $this->userRep->getUserRow($username);

        if ($user === false) {
            return false;
        }

        if ($passwordHash === $this->userRep->getPasswordhash($username)){
            return true;
        }else{
            return false;
        }
    }

    public function isAdmin(){
        if ($this->isLoggedIn()){
            if ($_COOKIE['admin'] === 'yes'){
                return true;
            }else{
                return false;
            }
        }
    }

    public function isLoggedIn(){
        if (isset($_COOKIE['user'])){
            return true;
        }
    }

    public function isAdminPage($template){
        if (strpos($template, 'admin') == '6'){
            return true;;
        }else{
            return false;
        }

    }

    public static function checkCSRF($token){
        if($token == $_SESSION['token']){
            return true;
        }
        return false;
        }
}
