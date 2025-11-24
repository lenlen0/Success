<?php

include_once "bdd.inc.php";

class Exam extends ConnexionPDO {

    public function getExamByIDUser($id_user) {
        $resultat = array();
        try {
            $this->conn->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            $req = $this->conn->prepare("SELECT e.idExam, e.name AS exam_name, e.dateExam, e.status, e.code, QU.name AS quizz_name, g.name AS group_name, ROUND(AVG(te.grade), 2) AS avg_grade
            FROM Exam e
            INNER JOIN Quizz QU ON e.idQuizz = QU.idQuizz
            INNER JOIN `Group` g ON e.idGroup = g.idGroup
            INNER JOIN User u ON QU.id_s11 = u.id_s11
            INNER JOIN TakeExam te ON e.idExam = te.idExam
            WHERE u.id_s11 = :id_user
            GROUP BY e.idExam, e.name, e.dateExam, e.status, e.code, QU.name, g.name
            ORDER BY te.date_exam DESC");
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
            $req = $this->conn->prepare("SELECT e.idExam, e.name AS exam_name, e.dateExam, e.status, e.code, QU.name AS quizz_name, g.name AS group_name, ROUND(AVG(te.grade), 2) AS avg_grade
                FROM Exam e INNER JOIN Quizz QU ON e.idQuizz = QU.idQuizz INNER JOIN `Group` g ON e.idGroup = g.idGroup
                LEFT JOIN TakeExam te ON e.idExam = te.idExam
                GROUP BY e.idExam, e.name, e.dateExam, e.status, e.code, QU.name, g.name
                ORDER BY e.idExam");
            $req->execute();

            $resultat = $req->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultat as &$row) {
                if ($row['avg_grade'] === null) {
                    $row['avg_grade'] = 0;
                }
            }
            unset($row);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function addExam($name, $status, $code, $scale, $hasMalus, $time, $idQuizz, $idGroup) {
        try {
            $req = $this->conn->prepare("INSERT INTO Exam (name, status, code, scale, hasMalus, time, idQuizz, idGroup)
                VALUES (:name, :status,  :code, :scale, :hasMalus, :time, :idQuizz, :idGroup)");
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':status', $status, PDO::PARAM_STR);
            $req->bindValue(':code', $code, PDO::PARAM_STR);
            $req->bindValue(':scale', $scale, PDO::PARAM_INT);
            $req->bindValue(':hasMalus', $hasMalus, PDO::PARAM_INT);
            $req->bindValue(':time', $time, PDO::PARAM_INT);
            $req->bindValue(':idQuizz', $idQuizz, PDO::PARAM_INT);
            $req->bindValue(':idGroup', $idGroup, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function deleteExam($idExam) {
        try {
            $req = $this->conn->prepare("DELETE FROM Exam WHERE idExam = :idExam");
            $req->bindValue(':idExam', $idExam, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }

    public function editExam($idExam, $name, $status, $scale, $hasMalus, $time, $idQuizz, $idGroup) {
        try {
            $req = $this->conn->prepare("UPDATE Exam
            SET name = :name, status = :status, scale = :scale, hasMalus = :hasMalus, time = :time, idQuizz = :idQuizz, idGroup = :idGroup
            WHERE idExam = :idExam");
            $req->bindValue(':idExam', $idExam, PDO::PARAM_INT);
            $req->bindValue(':name', $name, PDO::PARAM_STR);
            $req->bindValue(':status', $status, PDO::PARAM_STR);
            $req->bindValue(':scale', $scale, PDO::PARAM_INT);
            $req->bindValue(':hasMalus', $hasMalus, PDO::PARAM_INT);
            $req->bindValue(':time', $time, PDO::PARAM_INT);
            $req->bindValue(':idQuizz', $idQuizz, PDO::PARAM_INT);
            $req->bindValue(':idGroup', $idGroup, PDO::PARAM_INT);
            $resultat = $req->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $resultat;
    }
}

?>
