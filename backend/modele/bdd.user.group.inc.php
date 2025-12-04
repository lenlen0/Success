<?php

include_once "bdd.inc.php";

class UserGroup extends ConnexionPDO {

    public function getUserByGroup($idGroup) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT u.id_s11, u.lastname, u.firstname, u.role 
                FROM User u 
                INNER JOIN BelongGroup bg ON u.id_s11 = bg.id_s11 
                WHERE bg.idGroup = :idGroup 
                ORDER BY u.lastname, u.firstname");
            $req->bindValue(':idGroup', $idGroup, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function addUserToGroup($id_s11, $idGroup) {
        try {
            $req = $this->conn->prepare("INSERT INTO BelongGroup (id_s11, idGroup) 
                VALUES (:id_s11, :idGroup)");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $req->bindValue(':idGroup', $idGroup, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function removeUserFromGroup($id_s11, $idGroup) {
        try {
            $req = $this->conn->prepare("DELETE FROM BelongGroup 
                WHERE id_s11 = :id_s11 AND idGroup = :idGroup");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $req->bindValue(':idGroup', $idGroup, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>