<?php

class ChatUser extends ChatBase
{

    public $name = '';
    public $gravatar = '';
    public $email = '';
    public $password = '';
    public $login = false;
    public $is_admin = false;
    public $is_active = false;

    public static function getAll()
    {
        $result = DB::query("SELECT * FROM webchat_users;");

        $users = array();
        while ($user = $result->fetch_object()) {
            $user->gravatar = Chat::gravatarFromHash($user->gravatar, 30);
            $users[] = $user;
        }

        return $users;
    }

    public static function get($name)
    {
        $result = DB::query("SELECT * FROM webchat_users WHERE name = '" . DB::esc($name) . "'");

        $object = $result->fetch_object();

        $user = new ChatUser(array(
            'name' => $object->name,
            'gravatar' => $object->gravatar,
            'email' => $object->email,
            'password' => $object->password,
            'login' => $object->login,
            'is_admin' => $object->is_admin,
            'is_active' => $object->is_active
        ));

        return $user;
    }

    public function login()
    {
        DB::query("UPDATE webchat_users SET login=TRUE WHERE name = '" . DB::esc($this->name) . "'");
    }

    public function logout()
    {
        DB::query("UPDATE webchat_users SET login=FALSE WHERE name = '" . DB::esc($this->name) . "'");
    }

    public function save()
    {
        DB::query("
			INSERT INTO webchat_users (name, gravatar, email, password)
			VALUES (
				'" . DB::esc($this->name) . "',
				'" . DB::esc($this->gravatar) . "',
				'" . DB::esc($this->email) . "',
				'" . DB::esc($this->password) . "'
		)");

        return DB::getMySQLiObject();
    }

    public function update()
    {
        DB::query("
			UPDATE webchat_users SET
			name = '" . DB::esc($this->name) . "',
			gravatar = '" . DB::esc($this->gravatar) . "',
            email = '" . DB::esc($this->email) . "',
            password = '" . DB::esc($this->password) . "',
            is_active = '" . DB::esc($this->is_active) . "'
            WHERE name = '" . DB::esc($this->name) . "'");
    }

    public function delete()
    {
        DB::query("DELETE FROM webchat_users WHERE name = '" . DB::esc($this->name) . "'");
    }

}

?>