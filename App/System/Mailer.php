<?php
namespace App\System;

use \PHPMailer;
use \App\Models\UsersModel;

class Mailer extends PHPMailer {
    public function __construct($username) { //gets stuff from settings.php and config.yml
        //take in user in function
        parent::__construct();      //takes from dev environment under mail:
        try{
        $this->isSMTP();
        $this->SMTPAuth    = true;
        $this->Host        = 'smtp.gmail.com';   //Settings::getConfig()['mail']['host'];
        $this->Username    = 'tdt4237mailer@gmail.com';  //Settings::getConfig()['mail']['username'];
        $this->Password    = 'YF96YtnqvoPt';  //Settings::getConfig()['mail']['password'];
        $this->SMTPSecure  = 'tls';
        $this->Port        = '587';  //Settings::getConfig()['mail']['port'];

        //get user row
        $userRow = UsersModel::getUserRow($username);

        $this->setFrom('noreply@pokedex.com', 'Pokedex');
        $this->addAddress($userRow->email, $userRow->username);     // Add a recipient
        $this->addBCC('tordsta@stud.ntnu.no');
        $this->isHTML(true);                                  // Set email format to HTML
        $this->Subject = 'Validate your account - Pokedex';
        $this->Body    ='Welcome to Pokedex. <br/><br/>Please activate your account with the activation code.<br/>    Activation Code: '.$userRow->active_hash.' ';
          if($this->send()){
            echo 'Message has been sent';
          }
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->ErrorInfo;
        }
    }
}
?>
