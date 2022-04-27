<?php
namespace application\models;
use application\lib;
use application\core\Model;

class Account extends Model
{
    public $error;

    public function validate($input) {
        $rules = [
            'login' => [
                'pattern' => '#^[a-z0-9]{3,15}$#',
                'message' => 'Логин указан неверно (разрешены только латинские буквы и цифры от 3 до 15 символов',
            ],
            'password' => [
                'pattern' => '#^[a-z0-9]{10,30}$#',
                'message' => 'Пароль указан неверно (разрешены только латинские буквы и цифры от 10 до 30 символов',

            ],
        ];
        foreach ($input as $val) {
            if (!isset($_POST[$val]) or !preg_match($rules[$val]['pattern'], $_POST[$val])) {
                $this->error = $rules[$val]['message'];
                return false;
            }
        }
        return true;
    }


    public function checkLoginExists($login) {
        $params = [
            'login' => $login,
        ];
        if ($this->db->column('SELECT id FROM `users` WHERE login = :login', $params)) {
            $this->error = 'Этот логин уже используется';
            return false;
        }
        return true;
    }
    public function register() {
        $params = [
            'login' => $_POST['login'],
            'password' => md5(md5($_POST['password'])),
            'status' => 'user'
        ];
        $this->db->query("INSERT INTO `users` (`login`, `password`,`status`) VALUES (:login, :password,:status)", $params);
        return $this->db->lastInsertId();
    }

    public function checkData($login, $password) {
        $params = [
            'login' => $login,
        ];
        $hash = $this->db->column('SELECT password FROM users WHERE login = :login', $params);

        if ($hash == md5(md5($_POST['password']))) {
            return true;
        } else{
            return false;
        }
    }

    public function login($login) {
        $params = [
            'login' => $login,
        ];
        $data = $this->db->row('SELECT * FROM users WHERE login = :login', $params);
        $_SESSION['account'] = $data[0];
    }
}