<?php

class ConnexionPDO {
    private $user = "root";
    private $mdp = 'student';
    private $DSN = "mysql:host=localhost;dbname=Success;charset=UTF8";
    protected $conn = null;

    public function __construct() {
        try {
            $this->conn = new PDO($this->DSN, $this->user, $this->mdp);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}


?>
