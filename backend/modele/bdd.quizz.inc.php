<?php

include_once "bdd.inc.php";

class Quizz extends ConnexionPDO {

    public function getTableQuizz() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT * FROM Quizz");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getQuizzByID($id_quizz) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT * FROM Quizz WHERE idQuizz = :id_quizz");
            $req->bindValue(':id_quizz', $id_quizz, PDO::PARAM_STR);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getQuizzByUser($id_user) {
        $resultat = array();
        try {
	    $this->conn->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            $req = $this->conn->prepare("SELECT QU.idQuizz, QU.name, COALESCE(COUNT(Q.idQuestion), 0) AS nb_question
            FROM Quizz QU
            LEFT JOIN Question Q ON Q.idQuizz = QU.idQuizz
            WHERE QU.id_s11 = :id_user
            GROUP BY QU.idQuizz, QU.name, QU.isEnable
            ORDER BY QU.idQuizz ASC;");
            $req->bindValue(':id_user', $id_user, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getAllQuizz() {
        $resultat = array();
        try {
            $this->conn->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            $req = $this->conn->prepare("SELECT QU.idQuizz, QU.name, QU.isEnable, COALESCE(COUNT(Q.idQuestion), 0) AS nb_question
            FROM Quizz QU
            LEFT JOIN Question Q ON Q.idQuizz = QU.idQuizz
            GROUP BY QU.idQuizz, QU.name, QU.isEnable
            ORDER BY QU.idQuizz ASC;");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function addQuizz($name, $isEnable, $id_s11) {
        try {
            $req = $this->conn->prepare("INSERT INTO Quizz (name, isEnable, id_s11)
                VALUES (:name, :isEnable,  :id_s11)");
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':isEnable', $isEnable, PDO::PARAM_INT);
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function editQuizz($idQuizz, $name, $isEnable) {
        try {
            $req = $this->conn->prepare("UPDATE Quizz SET name = :name, isEnable = :isEnable WHERE idQuizz = :idQuizz");
            $req->bindValue(':idQuizz', $idQuizz, PDO::PARAM_INT);
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':isEnable', $isEnable, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function deleteQuizzByID($idQuizz) {
        try {
            $req = $this->conn->prepare("DELETE FROM Quizz WHERE idQuizz = :idQuizz");
            $req->bindValue(':idQuizz', $idQuizz, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
