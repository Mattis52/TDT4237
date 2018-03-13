<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Auth;
use \App\Models\UsersModel;
use App\Models\CategoriesModel;
use App\Models\Model;
use App\Models\ProductsModel;

class Controller {
    
    protected $auth;
    protected $userRep;
    protected $categoryRep;
    protected $productRep;
    
    public function __construct(){
        $this->auth = new Auth;
        $this->userRep = new UsersModel;
        $this->categoryRep = new CategoriesModel;
        $this->productRep = new ProductsModel;
        $this->rep = new Model;
    }
    
    public function render($template, $attributes) {
        
        $adminPage = $this->auth->isAdminPage($template);
        $isAdmin = $this->auth->isAdmin();
        //Removed code that sent username and passwordhash to tempaltes

        if ($isAdmin){
            $attributes['admin'] = 'true';
        }

        
        if ($adminPage && !($isAdmin)){
            App::error403();
        }else{
            echo App::getTwig()->render($template, $attributes);
        }
    }

    // Added
    public function isOwner($object) {
        $owner = $object->user;
        if ($owner === $_SESSION['auth']) {
            return true;
        }
        else {
            return false;
        }
    }
}
