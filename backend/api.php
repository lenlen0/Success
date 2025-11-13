<?php

include "modele/bdd.question.inc.php";
include "modele/bdd.quizz.inc.php";
include "modele/bdd.exam.inc.php";

$Question = new Question();
$Quizz = new Quizz();
$Exam = new Exam();

// En-t  tes pour le JSON et CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

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
