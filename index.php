<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['dbal'] = function() {
    $dbconfig = require './config/database.php';
    $connection = DriverManager::getConnection(array(
        'dbname' => $dbconfig['name'],
        'user' => $dbconfig['username'],
        'password' => $dbconfig['password'],
        'host' => $dbconfig['host'],
        'driver' => 'pdo_mysql',
        'charset' => 'utf8',
        'driverOptions' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
    ), $config = new Configuration);
    return $connection;
};

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('templates', [
        //'cache' => 'cache'
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->get('/', function (Request $request, Response $response, array $args) {

    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $user = array('isLogged' => 'true', 'login' => $_SESSION['login'], 'role' => $_SESSION['role']);
    } else {
        $user = array('isLogged' => 'false');
    } 

    return $this->view->render($response, 'Main.html.twig', ['user' => $user]); 

});

$app->get('/subject', function (Request $request, Response $response, array $args) {

    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $user = array('isLogged' => 'true', 'login' => $_SESSION['login'], 'role' => $_SESSION['role']);
    } else {
        $user = array('isLogged' => 'false');
    } 

    return $this->view->render($response, 'Subject.html.twig', ['user' => $user]); 

});

$app->get('/group', function (Request $request, Response $response, array $args) {

    $allGroups = \Entity\Group::getAllGroups($this->dbal);

    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $user = array('isLogged' => 'true', 'login' => $_SESSION['login'], 'role' => $_SESSION['role']);
    } else {
        $user = array('isLogged' => 'false');
    } 

    $subjects = array();
    $unassignedSubjects = array();
    foreach($allGroups as $key => $group) {
        if (!is_null($group)) {
            $subjects[$group->getId()][] = \Entity\Subject::getAllSubjectsByGroupId($this->dbal, $group->getId());
            $unassignedSubjects[$group->getId()][] = \Entity\Subject::getUnassignedSubjects($this->dbal, $group->getId());
        }
    }

    // var_dump($unassignedSubjects[1]);exit;
    // var_dump($subjects);exit;
    return $this->view->render($response, 'group.html.twig', ['user' => $user, 'allGroups' => $allGroups, 'subjects' => $subjects, 'unassignedSubjects' => $unassignedSubjects]); 

});

$app->get('/employees', function (Request $request, Response $response, array $args) {

    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $user = array('isLogged' => 'true', 'login' => $_SESSION['login'], 'role' => $_SESSION['role']);
    } else {
        $user = array('isLogged' => 'false');
    } 

    return $this->view->render($response, 'employee.html.twig', ['user' => $user]); 

});


$app->get('/generate-tags', function (Request $request, Response $response, array $args) {

    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $user = array('isLogged' => 'true', 'login' => $_SESSION['login'], 'role' => $_SESSION['role']);
    } else {
        $user = array('isLogged' => 'false');
    } 

    $groups = \Entity\Group::getAllGroups($this->dbal);

    foreach($groups as $group) {
        if (!is_null($group)) {
            $subjects = \Entity\Subject::getAllSubjectsByGroupId($this->dbal, $group->getId());
            \Entity\Tag::generateTags($this->dbal, $group, $subjects);
        }
    }
    
    return $this->view->render($response, 'Main.html.twig', ['user' => $user]); 

});

// ----- AJAX -----

$app->post('/signup', function ($request, $response, $args) {
    
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $pwdconfirm = $_POST['pwdconfirm'];
    $role = $_POST['role'];

    if ($role == null) {
        $role = 'Tajemník';
    }

    if ($pwd == $pwdconfirm) {
        $signUp = \Entity\Login::SignUp($this->dbal, $email , $pwd, $role);
        $result = 1;
    } else {
        $result = 0;
    }

    echo json_encode(array(
        'result' => $result
    ));

});

$app->post('/login', function (Request $request, Response $response, array $args) {

    $email = $_POST['email'];
    $pwd = $_POST['pwd'];

    if ($login = \Entity\Login::Login($this->dbal, $email, $pwd)) {
        $_SESSION["login"] = $login['name'];
        $_SESSION["id"] = $login['id'];
        $_SESSION["role"] = $login['role'];
        $result = 1;
    } else {
        $_SESSION["name"] = null;
        $_SESSION["id"] = null;
        $_SESSION["role"] = null;
        $result = 0;
    }

    echo json_encode(array(
        'result' => $result
    ));
});

$app->post('/logout', function ($request, $response, $args) {
    
    session_destroy();

    if (session_status() === PHP_SESSION_NONE) {
        $result = 1;
    } else {
        $result = 0;
    }

    echo json_encode(array(
        'result' => $result
    ));

});

$app->post('/create-subject', function ($request, $response, $args) {
    
    echo json_encode(array(
        'result' => \Entity\Subject::createSubject($this->dbal, $_POST['shortcut'], $_POST['weekCount'], $_POST['lectureCount'], $_POST['exerciseCount'], $_POST['seminarCount'], $_POST['endType'], $_POST['language'], $_POST['classCount'])
    ));

});

$app->post('/create-group', function ($request, $response, $args) {

    echo json_encode(array(
        'result' => \Entity\Group::createGroup($this->dbal, $_POST['shortcut'], $_POST['grade'], $_POST['semester'], $_POST['studentCount'], $_POST['studyForm'], $_POST['studyType'], $_POST['language'])
    ));

});

$app->post('/create-employee', function ($request, $response, $args) {

    echo json_encode(array(
        'result' => \Entity\Employee::createEmployee($this->dbal, $_POST['surname'], $_POST['lastname'], $_POST['privatemail'], $_POST['publicmail'], $_POST['worktype'], $_POST['doctor'])
    ));

});

$app->post('/delete-subject-group-relation', function ($request, $response, $args) {
    
    \Entity\SubjectGroupRel::deleteSubjectGroupRelation($this->dbal, $_POST['idGroup'], $_POST['idSubject']);

    $unassignedSubjects = \Entity\Subject::getUnassignedSubjects($this->dbal, $_POST['idGroup']);
    $result = null;
    if (!is_null($unassignedSubjects)) {
        foreach($unassignedSubjects as $unassignedSubject) {
            $result[] = array('subjectName' => $unassignedSubject->getShortcut(), 'idSubject' => $unassignedSubject->getId());
        }
    }

    echo json_encode(array(
        'result' => array(
            'unassignedSubjects' => $result
        )
    ));

});

$app->post('/add-subject-group-relation', function ($request, $response, $args) {

    \Entity\SubjectGroupRel::createSubjectGroupRel($this->dbal, $_POST['idSubject'], $_POST['idGroup']);

    $subject = \Entity\Subject::getSubjectById($this->dbal, $_POST['idSubject']);
    $unassignedSubjects = \Entity\Subject::getUnassignedSubjects($this->dbal, $_POST['idGroup']);
    $result = null;
    if (!is_null($unassignedSubjects)) {
        foreach($unassignedSubjects as $unassignedSubject) {
            $result[] = array('subjectName' => $unassignedSubject->getShortcut(), 'idSubject' => $unassignedSubject->getId());
        }
    }

    echo json_encode(array(
        'result' => array(
            'subjectName' => $subject->getShortcut(),
            'unassignedSubjects' => $result
        )
    ));

});





$app->run();
?>