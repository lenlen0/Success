<?php

include_once "bdd.inc.php";

class TakeExam extends ConnexionPDO {

    public function userTakeExam($id_s11, $idExam, $answer, $grade) {
        try {
            $answer_json = json_encode($answer);

            $req = $this->conn->prepare("INSERT INTO TakeExam (id_s11, idExam, answer, grade)
                VALUES (:id_s11, :idExam, :answer, :grade)");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $req->bindValue(':idExam', $idExam, PDO::PARAM_INT);
            $req->bindValue(':answer', $answer_json, PDO::PARAM_STR);
            $req->bindValue(':grade', $grade, PDO::PARAM_STR);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getTakeExamByID($id_s11, $idExam) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT answer FROM TakeExam WHERE id_s11 = :id_s11 AND idExam = :idExam");
            $req->bindValue(':id_s11', $id_s11, PDO::PARAM_INT);
            $req->bindValue(':idExam', $idExam, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>