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

$app->run();
?>