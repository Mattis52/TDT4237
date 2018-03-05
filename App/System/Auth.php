<?php
namespace App\System;

use \App\Models\UsersModel;

class Auth{
    
    protected $userRep;
    
    public function __construct(){
        $this->userRep = new UsersModel;
    }
    
    public function checkCredentials($username, $password)
    {
        $user = $this->userRep->getUserRow($username);
        
        if ($user === false) {
            return false;
        }
        
        $passwordHash = hash('sha1', Settings::getConfig()['salt'] . $password);
        
        if ($passwordHash === $this->userRep->getPasswordhash($username)){
            return true;
        }else{
            return false;
        }
    }
    
    public function isAdmin(){
        if ($this->isLoggedIn()){
            $logged_in_username = $_SESSION['auth']; // Changed
            $is_admin = $this->userRep->getAdmin($logged_in_username); // Changed
            if ($is_admin === '1'){ // Changed
                return true;
            }else{
                return false;
            }
        }
    }
    
    public function isLoggedIn(){
        if (isset($_SESSION['auth'])){ // Changed from COOKIE['user']
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
}
