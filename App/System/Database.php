<?php
namespace App\System;

use \PDO;

class Database {

    private $db_name;
    private $db_user;
    private $db_password;
    private $db_host;
    private $pdo;

    public function __construct($db_name, $db_user, $db_password, $db_host) {
        $this->db_name     = $db_name;
        $this->db_user     = $db_user;
        $this->db_password = $db_password;
        $this->db_host     = $db_host;
        $this->db_name     = $db_name;
    }

    private function getPDO() {
        if($this->pdo === null) {
            $this->pdo = new PDO("mysql:dbname={$this->db_name};host={$this->db_host}", $this->db_user, $this->db_password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }

        return $this->pdo;
    }

    public function query($statement, $one = false) {
        $req  = $this->getPDO()->query($statement);
        echo "In query";
        echo "Req in query: " . print_r($req);

        if($one) {
            $data = $req->fetch();
        }

        else {
            $data = $req->fetchAll();
        }
        echo "Data: " . print_r($data);


        return $data;
    }

    public function prepare($statement, $attributes, $one = false) {
        echo "IN PREPARE";
        $req = $this->getPDO()->prepare($statement);
        // echo $statement; echo print_r($attributes) ; die;
        $req->execute($attributes);

        if($one) {
            $data = $req->fetch();
        }

        else {
            $data = $req->fetchAll();
        }

        //echo "Data: " . print_r($data);
        return $data;
    }

    public function execute($statement, $attributes = false) {
        // echo "statement in execute: " . $statement; die;
        //echo $statement . "\n";
        //echo print_r($attributes) . "\n";
        if(!$attributes) {
            echo "Execute with no attributes";

            $this->getPDO()->query($statement);
        }

        else {
            echo "Execute with attributes";
            //echo "Going into prepare";
            $req = $this->getPDO()->prepare($statement);
            //echo "After prepare: ". print_r($req);
            echo "Attributes: " . print_r($attributes); die;
            $req->execute($attributes);
        }
    }

}
