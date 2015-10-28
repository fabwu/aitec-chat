<?php

/* The Chat class exploses public static methods, used by ajax.php */

class Chat
{

    public static function login($name, $password)
    {
        if (empty($name) || empty($password)) {
            throw new Exception('Fill in all the required fields.');
        }

        $user = ChatUser::get($name);

        if ($user == null || $password != $user->getPassword()) {
            throw new Exception('Your nickname or password is invalid.');
        }

        $user->login();

        $_SESSION['user'] = array(
            'name' => $user->getName(),
            'gravatar' => $user->getName(),
            'is_admin' => $user->isAdmin()
        );

        return array(
            'status' => 1,
            'name' => $user->getName(),
            'gravatar' => Chat::gravatarFromHash($user->getGravatar()),
            'is_admin' => $user->isAdmin()
        );
    }

    public static function gravatarFromHash($hash, $size = 23)
    {
        return 'http://www.gravatar.com/avatar/' . $hash . '?size=' . $size . '&amp;default=' .
        urlencode('http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?size=' . $size);
    }

    public static function register($name, $email, $password, $confirmPassword)
    {
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            throw new Exception('Fill in all the required fields.');
        }

        if(!ctype_alnum($name)) {
            throw new Exception("Nickname not alphanumeric.");
        }

        if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Your email is invalid.');
        }

        if(strcmp($password, $confirmPassword) != 0) {
            throw new Exception("Passwords don't match.");
        }

        $gravatar = md5(strtolower(trim($email)));

        $user = new ChatUser(array(
            'name' => $name,
            'email' => $email,
            //TODO hash PW
            'password' => $password,
            'gravatar' => $gravatar
        ));

        // The save method returns a MySQLi object
        $mysqli = $user->save();
        if ($mysqli->affected_rows != 1) {
            throw new Exception($mysqli->error);
        }

        return array(
            'status' => 1
        );
    }

    public static function checkLogged()
    {
        $response = array('logged' => false);

        if ($_SESSION['user']['name']) {
            $response['logged'] = true;
            $response['loggedAs'] = array(
                'name' => $_SESSION['user']['name'],
                'gravatar' => Chat::gravatarFromHash($_SESSION['user']['gravatar']),
                'is_admin' => $_SESSION['user']['is_admin']
            );
        }

        return $response;
    }

    public static function logout()
    {
        $name = $_SESSION['user']['name'];
        $user = new ChatUser(array(
            'name' => $name
        ));
        $user->logout();

        $_SESSION = array();
        unset($_SESSION);

        return array('status' => 1);
    }

    public static function submitChat($chatText)
    {
        if (!$_SESSION['user']) {
            throw new Exception('You are not logged in');
        }

        if (!$chatText) {
            throw new Exception('You haven\' entered a chat message.');
        }

        $chat = new ChatLine(array(
            'author' => $_SESSION['user']['name'],
            'gravatar' => $_SESSION['user']['gravatar'],
            'text' => $chatText
        ));

        // The save method returns a MySQLi object
        $insertID = $chat->save()->insert_id;

        return array(
            'status' => 1,
            'insertID' => $insertID
        );
    }

    public static function getUsers()
    {
        if ($_SESSION['user']['name']) {
            $user = new ChatUser(array('name' => $_SESSION['user']['name']));
            $user->update();
        }

        // Deleting chats older than 5 minutes
        DB::query("DELETE FROM webchat_lines WHERE ts < SUBTIME(NOW(),'0:5:0')");

        $result = DB::query('SELECT * FROM webchat_users WHERE login = TRUE ORDER BY name ASC LIMIT 18');

        $users = array();
        while ($user = $result->fetch_object()) {
            $user->gravatar = Chat::gravatarFromHash($user->gravatar, 30);
            $users[] = $user;
        }

        return array(
            'users' => $users,
            'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users WHERE login = TRUE')->fetch_object()->cnt
        );
    }

    public static function getChats($lastID)
    {
        $lastID = (int)$lastID;

        $result = DB::query('SELECT * FROM webchat_lines WHERE id > ' . $lastID . ' ORDER BY id ASC');

        $chats = array();
        while ($chat = $result->fetch_object()) {

            // Returning the GMT (UTC) time of the chat creation:

            $chat->time = array(
                'hours' => gmdate('H', strtotime($chat->ts)),
                'minutes' => gmdate('i', strtotime($chat->ts))
            );

            $chat->gravatar = Chat::gravatarFromHash($chat->gravatar);

            $chats[] = $chat;
        }

        return array('chats' => $chats);
    }
}


?>