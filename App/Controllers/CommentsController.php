<?php
namespace App\Controllers;

use \App\System\Auth;
use App\Models\CommentsModel;
use \App\Controllers\Controller;
use \DateTime;
use App\System\App;

class CommentsController extends Controller {
    
    protected $table = "comments";

    public function add() {
        if(!empty($_POST) && Auth::checkCSRF($_POST["token"]) ){
            $text  = isset($_POST['comment']) ? $_POST['comment'] : '';
            $model = new CommentsModel;
            $model->create([
                'created_at' => date('Y-m-d H:i:s'),
                'user'       => $_SESSION['auth'], // Changed from COOKIE['user']
                'text'       => htmlspecialchars($text)
            ]);
        }
    App::redirect('dashboard');
   }
}