<?php
namespace App\System;

use \PHPMailer;

class Mailer extends PHPMailer {

    public function __construct() { //gets stuff from settings.php and config.yml
        parent::__construct();      //takes from dev environment under mail:
        $this->isSMTP();
        $this->SMTPAuth = true;
        $this->Host        = Settings::getConfig()['mail']['host'];
        $this->Username    = Settings::getConfig()['mail']['username'];
        $this->Password    = Settings::getConfig()['mail']['password'];
        $this->Port        = Settings::getConfig()['mail']['port'];
        
        $this->addAddress('tordsta@stud.ntnu.no', 'Joe User');     // Add a recipient
        $this->isHTML(true);                                  // Set email format to HTML
        $this->Subject = 'Here is the subject';
        $this->Body    = 'This is the HTML message body <b>in bold!</b>';

        $this->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );
        $this->send();

        echo 'Message has been sent';

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
}
