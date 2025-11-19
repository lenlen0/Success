<?php

include_once "bdd.inc.php";

class User extends ConnexionPDO {

    public function getAllUser() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT id_s11, lastname, firstname, role, pwd FROM User");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getUserByID($id_s11) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT id_s11, lastname, firstname, role, pwd FROM User WHERE id_s11 = :id_s11");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function addUser($pwd, $role, $firstname, $lastname) {
        try {
            $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);

            $req = $this->conn->prepare("INSERT INTO User (pwd, role, firstname, lastname)
                VALUES (:pwd, :role, :firstname, :lastname)");
            $req->bindValue(':pwd', $hashed_pwd, PDO::PARAM_STR);
            $req->bindValue(':role', $role, PDO::PARAM_STR);
            $req->bindValue(':firstname', $firstname, PDO::PARAM_STR);
            $req->bindValue(':lastname', $lastname, PDO::PARAM_STR);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function deleteUserByID($id_s11) {
        try {
            $req = $this->conn->prepare("DELETE FROM User WHERE id_s11 = :id_s11");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>