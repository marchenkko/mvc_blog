<?php
namespace application\controllers;

use application\core\Controller;
use application\lib\Pagination;
use application\models\admin;
use application\models\Main;
use application\models\Account;

class AccountController extends Controller
{
    public function registerAction()
    {
        if (!empty($_POST)) {
            if (!$this->model->validate(['login', 'password'], $_POST)) {
                $this->view->message('error', $this->model->error);
            } elseif (!$this->model->checkLoginExists($_POST['login'])) {
                $this->view->message('error', $this->model->error);
            }
            $this->model->register($_POST);
            $this->view->message('success', 'Регистрация завершена!');
        }
        $this->view->render( 'Регистрация');
    }


    public function loginAction() {
        if (!empty($_POST)) {
            if (!$this->model->checkData($_POST['login'], $_POST['password'])) {
                $this->view->message('error', 'Логин или пароль указан неверно');
            }
            $this->model->login($_POST['login']);
            $this->view->location('postinfo');
        }
        $this->view->render('Вход');
    }

    public function logoutAction() {
        session_start();
        unset($_SESSION['account']);
        session_destroy();
        session_write_close();
        $this->view->redirect('account/login');
    }
}