<?php
namespace App\System;

use \App\Models\CategoriesModel;
use \App\Models\UsersModel;

class FormValidator {

    private $errors = [];

    public function notEmpty($element, $value, $message) {
        if(empty($value)) {
            $this->errors[$element] = $message;
            return false;
        }
        return true;
    }

    public function validCategory($element, $value, $message) {
        $model    = new CategoriesModel();
        $category = $model->find($value);
        if(!$category) {
            $this->errors[$element] = $message;
        }
    }

    // Changed this one
    public function validPassword($element, $username, $value, $value_verification) {
        // Longer than 6 char
        if (strlen($value) >= 6) {
            $this->errors[$element] = "The password must be at least 6 characters";
          // Include number
        } else if (preg_match('~[0-9]~', $value)) {
            $this->errors[$element] = "The password must contain at least one number";
        // Include Uppercase
        }else if (preg_match('~[A-Z]~', $value)) {
            $this->errors[$element] = "The password must contain at least one character in uppercase";
          //Not a dictionary word
        // } else if () {

          // Numbers and letters are not in sequence
        //} else if () {

          // DOesn't contain username

        //} else if ($value contains $username) {
        //    $this->errors[$element] = "The password can't contain the username";
        } else if (empty($value) || ($value != $value_verification)) {
            echo "First password: " . $value;
            echo "Second password: " . $value_verification;
            echo "After second password";
            $this->errors[$element] = "You didn't write the same password twice";
        }
    }

    public function validUsername($element, $value, $message) {
        if(!preg_match('/[a-z0-9]+/', $value)) {
            $this->errors[$element] = $message;
        }
    }

    public function availableUsername($element, $value, $message) {
        $model  = new UsersModel();
        $result = $model->query("SELECT * FROM users WHERE username = ?", [
            $value
        ], true);

        if($result) {
            $this->errors[$element] = $message;
        }
    }

    public function validEmail($element, $value, $message) {
        if (!empty($value)){
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$element] = $message;
            }
        }
    }
    
    public function isNumeric($element, $value, $message) {
        if(!is_numeric($value)) {
            $this->errors[$element] = $message;
        }
    }

    public function isInteger($element, $value, $message) {
        if(!is_int(intval($value))) {
            $this->errors[$element] = $message;
        }
    }

    public function validImage($element, $value, $message) {
        if(empty($value)) {
            $this->errors[$element] = $message;
        }

        else {
            if(empty($value['type'])) {
                $this->errors[$element] = $message;
                return;
            }

            if($value['size'] > 1000000) {
                $this->errors[$element] = "Your media is too big (> 1Mo)";
                return;
            }
        }
    }

    public function isValid() {
        if(empty($this->errors)) return true;
        else return false;
    }

    public function getErrors() {
        return $this->errors;
    }

}
