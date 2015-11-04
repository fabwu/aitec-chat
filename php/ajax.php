<?php

/* Database Configuration. Add your details below */

$dbOptions = array(
    'db_host' => 'localhost',
    'db_user' => 'aitec',
    'db_pass' => 'dachs',
    'db_name' => 'webchat'
);

/* Database Config End */

//report everything except notice
error_reporting(E_ALL ^ E_NOTICE);

require "classes/DB.class.php";
require "classes/Chat.class.php";
require "classes/ChatBase.class.php";
require "classes/ChatLine.class.php";
require "classes/ChatUser.class.php";

session_name('webchat');
session_start();

try {

    // Connecting to the database
    DB::init($dbOptions);

    $response = array();

    // Handling the supported actions:

    switch ($_GET['action']) {

        case 'login':
            $username = secureInput($_POST['name']);
            $password = secureInput($_POST['password']);

            $response = Chat::login($username, $password);
            break;

        case 'register':
            $username = secureInput($_POST['name']);
            $email = secureInput($_POST['email']);
            $password = secureInput($_POST['password']);
            $confirm_password = secureInput($_POST['confirmPassword']);

            $response = Chat::register($username, $email, $password, $confirm_password);
            break;

        case 'checkLogged':
            $response = Chat::checkLogged();
            break;

        case 'logout':
            $response = Chat::logout();
            break;

        case 'submitChat':
            $chat_text = secureInput($_POST['chatText']);

            $response = Chat::submitChat($chat_text);
            break;

        case 'getUsers':
            $response = Chat::getUsers();
            break;

        case 'activateUser':
            $username = secureInput($_POST['name']);
            $is_active = $_POST['isActive'] === 'true' ? true : false;

            $response = Chat::activateUser($username, $is_active);
            break;

        case 'deleteUser':
            $username = secureInput($_POST['name']);

            $response = Chat::deleteUser($username);
            break;

        case 'getLoggedInUsers':
            $response = Chat::getLoggedInUsers();
            break;

        case 'getChats':
            $lastID = (int)$lastID;

            $response = Chat::getChats($_GET['lastID']);
            break;

        default:
            throw new Exception('Wrong action');
    }

    echo json_encode($response);
} catch (Exception $e) {
    die(json_encode(array('error' => $e->getMessage())));
}


function secureInput($untrusted_input)
{
    $trimmed_input = trim($untrusted_input);
    return htmlspecialchars($trimmed_input, ENT_QUOTES);
}

?>
