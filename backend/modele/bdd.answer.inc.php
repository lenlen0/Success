<?php

include_once "bdd.inc.php";

class Answer extends ConnexionPDO {

    public function addAnswer($name, $isCorrect, $idQuestion) {
        try {
            $req = $this->conn->prepare("INSERT INTO Answer (name, isCorrect, idQuestion)
                VALUES (:name, :isCorrect, :idQuestion)");
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':isCorrect', $isCorrect, PDO::PARAM_INT);
            $req->bindValue(':idQuestion', $idQuestion, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getAnswerByIDQuestion($idQuestion) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT * FROM Answer WHERE idQuestion = :idQuestion");
            $req->bindValue(':idQuestion', $idQuestion, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function deleteAnswer($idAnswer) {
        try {
            $req = $this->conn->prepare("DELETE FROM Answer WHERE idAnswer = :idAnswer");
            $req->bindValue(':idAnswer', $idAnswer, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function editAnswer($idAnswer, $name, $isCorrect) {
        try {
            $req = $this->conn->prepare("UPDATE Answer SET name = :name, isCorrect = :isCorrect WHERE idAnswer = :idAnswer");
            $req->bindValue(':idAnswer', $idAnswer, PDO::PARAM_INT);
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':isCorrect', $isCorrect, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function deleteAnswerByidQuestion($idQuestion) {
        try {
            $req = $this->conn->prepare("DELETE FROM Answer WHERE idQuestion = :idQuestion");
            $req->bindValue(':idQuestion', $idQuestion, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
