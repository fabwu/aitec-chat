<?php

class ChatUser extends ChatBase
{

    protected $name = '';
    protected $gravatar = '';
    protected $email = '';
    protected $password = '';

    public static function get($name)
    {
        $result = DB::query("SELECT * FROM webchat_users WHERE name = '" . DB::esc($name) . "'");

        $user = $result->fetch_object();

        return $user;
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
			INSERT INTO webchat_users (name, gravatar, email, password)
			VALUES (
	            '" . DB::esc($this->name) . "',
				'" . DB::esc($this->gravatar) . "',
				'" . DB::esc($this->email) . "',
				'" . DB::esc($this->password) . "'
			) ON DUPLICATE KEY UPDATE last_activity = NOW()");
    }

    public function delete()
    {
        DB::query("DELETE FROM webchat_users WHERE name = '" . DB::esc($this->name) . "'");
    }
}

?>