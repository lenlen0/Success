<?php

include_once "bdd.inc.php";

class Question extends ConnexionPDO {

    public function getTableQuestion() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT * FROM Question");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getQuestionByID($id_question) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT * FROM Question WHERE idQuestion = :id_question");
            $req->bindValue(':id_question', $id_question, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getQuestionByIDQuizz($id_quizz) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT Q.idQuestion, Q.name FROM Question Q
                JOIN Quizz QU ON Q.idQuizz = QU.idQuizz
                WHERE QU.idQuizz = :id_quizz");
            $req->bindValue(':id_quizz', $id_quizz, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function addQuestion($name, $idQuizz) {
        try {
            $req = $this->conn->prepare("INSERT INTO Question (name, idQuizz)
                VALUES (:name, :idQuizz)");
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':idQuizz', $idQuizz, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
