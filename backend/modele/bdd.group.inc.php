<?php

include_once "bdd.inc.php";

class Group extends ConnexionPDO {

    public function getAllGroup() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT g.name, COALESCE(COUNT(bg.id_s11), 0) AS nb_user FROM `Group` g
            LEFT JOIN BelongGroup bg ON g.idGroup = bg.idGroup
            GROUP BY g.name ORDER BY g.idGroup ASC;");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getGroupByUser($id_user) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT g.name, COALESCE(COUNT(bg.id_s11), 0) AS nb_user FROM `Group` g
            LEFT JOIN BelongGroup bg ON g.idGroup = bg.idGroup
            WHERE g.id_s11 = :id_user
            GROUP BY g.name ORDER BY g.idGroup ASC;");
            $req->bindValue(':id_user', $id_user, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
