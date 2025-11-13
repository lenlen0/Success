<?php

include_once "bdd.inc.php";

class Exam extends ConnexionPDO {

    public function getExamByIDUser($id_user) {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT e.idExam, e.name AS exam_name, e.dateExam, e.status, e.code, QU.name AS quizz_name, g.name AS group_name, ROUND(AVG(te.grade), 2) AS avg_grade
            FROM Exam e
            INNER JOIN Quizz QU ON e.idQuizz = QU.idQuizz
            INNER JOIN `Group` g ON e.idGroup = g.idGroup
            INNER JOIN User u ON QU.id_s11 = u.id_s11
            INNER JOIN TakeExam te ON e.idExam = te.idExam
            WHERE u.id_s11 = :id_user
            GROUP BY e.idExam, e.name, e.dateExam, e.status, e.code, QU.name, g.name
            ORDER BY e.dateExam DESC;");
            $req->bindValue(':id_user', $id_user, PDO::PARAM_INT);
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function getAllExam() {
        $resultat = array();
        try {
            $req = $this->conn->prepare("SELECT e.idExam, e.name AS exam_name, e.dateExam, e.status, QU.name AS quizz_name, g.name AS group_name, ROUND(AVG(te.grade), 2) AS avg_grade
            FROM Exam e
            INNER JOIN Quizz QU ON e.idQuizz = QU.idQuizz
            INNER JOIN `Group` g ON e.idGroup = g.idGroup
            INNER JOIN TakeExam te ON e.idExam = te.idExam
            GROUP BY e.idExam, e.name, e.dateExam, e.status, QU.name, g.name
            ORDER BY e.dateExam DESC;");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
