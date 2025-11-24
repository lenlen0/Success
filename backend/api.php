<?php

include "modele/bdd.question.inc.php";
include "modele/bdd.quizz.inc.php";
include "modele/bdd.exam.inc.php";
include "modele/bdd.group.inc.php";
include "modele/bdd.user.inc.php";
include "modele/bdd.answer.inc.php";

$Question = new Question();
$Quizz = new Quizz();
$Exam = new Exam();
$Group = new Group();
$User = new User();
$Answer = new Answer();

// En-t  tes pour le JSON et CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// R  pertoire o   sont stock  s les fichiers JSON
//$basePath = __DIR__ . '/data/';

// R  cup  re le chemin de la requ  te apr  s api.php
// Exemple : /api.php/produits/3  ^f^r $pathParts = ['produits', '3']
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$path = trim(str_replace($scriptName, '', parse_url($requestUri, PHP_URL_PATH)), '/');
$pathParts = explode('/', $path);

// Retire "api.php" du chemin si pr  sent
if (isset($pathParts[0]) && $pathParts[0] === basename(__FILE__)) {
    array_shift($pathParts);
}

// V  rifie la route
if (count($pathParts) === 0 || empty($pathParts[0])) {
    http_response_code(400);
    echo json_encode(["error" => "Erreur chemin API incorrect."]);
    exit;
}

$resource = basename($pathParts[0] ?? '');
$id = $pathParts[1] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "JSON invalide."]);
        exit;
    }

    switch ($resource) {
        case 'add_user':
            if (empty($data['pwd']) || empty($data['role']) || empty($data['firstname']) || empty($data['lastname'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'pwd', 'role', 'firstname' et 'lastname' requis."]);
                exit;
            }

            $newUser = $User->addUser($data['pwd'], $data['role'], $data['firstname'], $data['lastname']);

            if (!$newUser) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'add_group':
            if (empty($data['name']) || empty($data['id_s11'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'name' et 'id_s11' requis."]);
                exit;
            }

            $newGroup = $Group->addGroup($data['name'], $data['id_s11']);

            if (!$newGroup) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'add_quizz':
            if (empty($data['name']) || empty($data['id_s11'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'name', 'isEnable' et 'id_s11' requis."]);
                exit;
            }

            $newQuizz = $Quizz->addQuizz($data['name'], $data['isEnable'], $data['id_s11']);

            if (!$newQuizz) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'add_exam':
            if (empty($data['name']) || empty($data['status']) || empty($data['code'])
                || empty($data['time']) || empty($data['idQuizz']) || empty($data['idGroup'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'name', 'status', 'code', 'scale', 'hasMalus', 'time', 'idQuizz' et 'idGroup' requis."]);
                exit;
            }

            $newExam = $Exam->addExam($data['name'], $data['status'], $data['code'], $data['scale'], $data['hasMalus'], $data['time'], $data['idQuizz'], $data['idGroup']);

            if (!$newExam) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'add_question':
            if (empty($data['name']) || empty($data['idQuizz'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'name' et 'idQuizz' requis."]);
                exit;
            }

            $newQuestion = $Question->addQuestion($data['name'], $data['idQuizz']);

            if (!$newQuestion) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'add_answer':
            if (empty($data['name']) || empty($data['idQuestion'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'name', 'isCorrect' et 'idQuestion' requis."]);
                exit;
            }

            $newAnswer = $Answer->addAnswer($data['name'], $data['isCorrect'], $data['idQuestion']);

            if (!$newAnswer) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'modify_user_with_no_password_change':
            if (empty($data['id_s11']) || empty($data['role']) || empty($data['firstname']) || empty($data['lastname'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'id_s11', 'role', 'firstname' et 'lastname' requis."]);
                exit;
            }

            $ModifyUserNoPasswordChange = $User->modifyUserWithNoPasswordChange($data['id_s11'], $data['role'], $data['firstname'], $data['lastname']);

            if (!$ModifyUserNoPasswordChange) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'modify_user_with_password_change':
            if (empty($data['id_s11']) || empty($data['pwd']) || empty($data['role']) || empty($data['firstname']) || empty($data['lastname'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'id_s11', 'pwd', 'role', 'firstname' et 'lastname' requis."]);
                exit;
            }

            $ModifyUserPasswordChange = $User->modifyUserWithPasswordChange($data['id_s11'], $data['pwd'], $data['role'], $data['firstname'], $data['lastname']);

            if (!$ModifyUserPasswordChange) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'edit_group':
            if (empty($data['idGroup']) || empty($data['name'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idGroup' et 'name' requis."]);
                exit;
            }

            $EditGroup = $Group->editGroup($data['idGroup'], $data['name']);

            if (!$EditGroup) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'edit_quizz':
            if (empty($data['idQuizz']) || empty($data['name'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idQuizz', 'name' et 'isEnable' requis."]);
                exit;
            }

            $EditQuizz = $Quizz->editQuizz($data['idQuizz'], $data['name'], $data['isEnable']);

            if (!$EditQuizz) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'edit_question':
            if (empty($data['idQuestion']) || empty($data['name'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idQuestion' et 'name' requis."]);
                exit;
            }

            $EditQuestion = $Question->editQuestion($data['idQuestion'], $data['name']);

            if (!$EditQuestion) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'edit_exam':
            if (empty($data['idExam']) || empty($data['status']) || empty($data['time']) || empty($data['idQuizz']) || empty($data['idGroup'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idExam', 'name', 'scale', 'hasMalus', 'time', 'idQuizz' et 'idGroup' requis."]);
                exit;
            }

            $EditExam = $Exam->editExam($data['idExam'], $data['name'], $data['status'], $data['scale'], $data['hasMalus'], $data['time'], $data['idQuizz'], $data['idGroup']);

            if (!$EditExam) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'del_user':
            if (empty($data['id_s11'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'id_s11' requis pour la supression."]);
                exit;
            }

            $delUser = $User->deleteUserByID($data['id_s11']);

            if (!$delUser) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'del_group':
            if (empty($data['idGroup'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idGroup' requis pour la supression."]);
                exit;
            }

            $delGroup = $Group->deleteGroup($data['idGroup']);

            if (!$delGroup) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'del_quizz':
            if (empty($data['idQuizz'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idQuizz' requis pour la supression."]);
                exit;
            }

            $delQuizz = $Quizz->deleteQuizzByID($data['idQuizz']);

            if (!$delQuizz) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;


        case 'del_exam':
            if (empty($data['idExam'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idExam' requis pour la supression."]);
                exit;
            }

            $delExam = $Exam->deleteExam($data['idExam']);

            if (!$delExam) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        case 'del_question':
            if (empty($data['idQuestion'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Champs 'idQuestion' requis pour la supression."]);
                exit;
            }

            $delQuestion = $Question->deleteQuestion($data['idQuestion']);

            if (!$delQuestion) {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
                exit;
            }

            echo json_encode(["status" => "success"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;

        default:
            http_response_code(404);
            echo json_encode(["error" => "Ressource '$resource' introuvable"]);
            break;
    }
}

$tab_resource = [
    'tablequestion' => [
        'model' => $Question,
        'requeteByID' => 'getQuestionByID',
        'requete' => 'getTableQuestion'
    ],
    'show_question' => [
        'model' => $Question,
        'requeteByID' => 'getQuestionByIDQuizz',
        'requete' => ''
    ],
    'tablequizz' => [
        'model' => $Quizz,
        'requeteByID' => 'getQuizzByID',
        'requete' => 'getTableQuizz'
    ],
    'show_quizz' => [
        'model' => $Quizz,
        'requeteByID' => 'getQuizzByUser',
        'requete' => 'getAllQuizz'
    ],
    'show_exam' => [
        'model' => $Exam,
        'requeteByID' => 'getExamByIDUser',
        'requete' => 'getAllExam'
    ],
    'show_group' => [
        'model' => $Group,
        'requeteByID' => 'getGroupByUser',
        'requete' => 'getAllGroup'
    ],
    'show_user' => [
        'model' => $User,
        'requeteByID' => 'getUserByID',
        'requete' => 'getAllUser'
    ],
    'show_answer' => [
        'model' => $Answer,
        'requeteByID' => 'getAnswerByIDQuestion',
        'requete' => ''
    ]
];

if (!array_key_exists($resource, $tab_resource)) {
    http_response_code(404);
    echo json_encode(["error" => "Ressource '$resource' introuvable"]);
    exit;
}

$modelInfo = $tab_resource[$resource];
$model = $modelInfo['model'];

// Récupère les données selon présence d'un ID
if (!empty($id)) {
    $data = $model->{$modelInfo['requeteByID']}($id);

    if (!empty($data)) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "L'id : '$id' est introuvable dans '$resource'"]);
    }
} else {
    if (!empty($modelInfo['requete'])) {
        $data = $model->{$modelInfo['requete']}();
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Requête invalide"]);
    }
}

exit;
