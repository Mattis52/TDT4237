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
        //use if we get the link and page to work
        //http://localhost:8080/verify?email='.$userRow->email.'&hash='.$userRow->active_hash.'';

        /* removed this no dont know what it does
        $this->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );
        */
          if($this->send()){
            echo 'Message has been sent';
          }
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->ErrorInfo;
        }
    }

        /*
        $mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress('tordsta@stud.ntnu.no', 'Joe User');     // Add a recipient
        $mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';
        */
}
?>
