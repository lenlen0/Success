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

            $req = $this->conn->prepare("SELECT QU.idQuizz, QU.name, COALESCE(COUNT(Q.idQuestion), 0) AS nb_question
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
}

?>
