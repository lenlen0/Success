<?php

include_once "bdd.inc.php";

class User extends ConnexionPDO {

    public function getAllUser() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT id_s11, lastname, firstname FROM User");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>