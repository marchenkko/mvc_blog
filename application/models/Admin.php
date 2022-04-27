<?php

namespace application\models;

use application\core\Model;


class Admin extends Model {

    public $error;

    public function loginValidate($post) {
        $config = require 'application/config/admin.php';
        if ($config['login'] != $post['login'] or $config['password'] != $post['password']) {
            $this->error = 'Логин или пароль указан неверно';
            return false;
        }
        return true;
    }

    public function postValidate($post, $type) {
        $nameLen = iconv_strlen($post['name']);
        $descriptionLen = iconv_strlen($post['description']);
        $textLen = iconv_strlen($post['text']);
        if ($nameLen < 3 or $nameLen > 100) {
            $this->error = 'Название должно содержать от 3 до 100 символов';
            return false;
        } elseif ($descriptionLen < 3 or $descriptionLen > 100) {
            $this->error = 'Описание должно содержать от 3 до 100 символов';
            return false;
        } elseif ($textLen < 10 or $textLen > 5000) {
            $this->error = 'Текст должнен содержать от 10 до 5000 символов';
            return false;
        }
        return true;
    }

    public function postAdd() {
        $params = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'text' => $_POST['text'],
        ];
        $this->db->query("INSERT INTO `posts` (`name`, `description`, `text`) VALUES (:name, :description, :text)", $params);
        return $this->db->lastInsertId();
    }

    public function postEdit($post, $id) {
        $params = [
            'id' => $id,
            'name' => $post['name'],
            'description' => $post['description'],
            'text' => $post['text'],
        ];
        $this->db->query('UPDATE posts SET name = :name, description = :description, text = :text WHERE id = :id', $params);
    }

    public function filesAdd($id)
    {
        $input_name = 'filename';

// Разрешенные расширения файлов.
        $allow = array(
            'png' , 'jpg'
        );

// Запрещенные расширения файлов.
        $deny = array(
            'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp',
            'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html',
            'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi'
        );

// Директория куда будут загружаться файлы.

        $root = $_SERVER["DOCUMENT_ROOT"];
        $path = $root . '/public/materials/';

        if (isset($_FILES[$input_name])) {
            // Проверим директорию для загрузки.
            if (!is_dir($path)) {
                mkdir($path, 0644, true);
            }

            // Преобразуем массив $_FILES в удобный вид для перебора в foreach.
            $files = array();
            $diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
            if ($diff == 0) {
                $files = array($_FILES[$input_name]);
            } else {
                foreach ($_FILES[$input_name] as $k => $l) {
                    foreach ($l as $i => $v) {
                        $files[$i][$k] = $v;
                    }
                }
            }

            foreach ($files as $file) {
                $error = $success = '';

                // Проверим на ошибки загрузки.
                if (!empty($file['error']) || empty($file['tmp_name'])) {
                    switch (@$file['error']) {
                        case 1:
                        case 2:
                            $error = 'Превышен размер загружаемого файла.';
                            break;
                        case 3:
                            $error = 'Файл был получен только частично.';
                            break;
                        case 4:
                            $error = 'Файл не был загружен.';
                            break;
                        case 6:
                            $error = 'Файл не загружен - отсутствует временная директория.';
                            break;
                        case 7:
                            $error = 'Не удалось записать файл на диск.';
                            break;
                        case 8:
                            $error = 'PHP-расширение остановило загрузку файла.';
                            break;
                        case 9:
                            $error = 'Файл не был загружен - директория не существует.';
                            break;
                        case 10:
                            $error = 'Превышен максимально допустимый размер файла.';
                            break;
                        case 11:
                            $error = 'Данный тип файла запрещен.';
                            break;
                        case 12:
                            $error = 'Ошибка при копировании файла.';
                            break;
                        default:
                            $error = 'Файл не был загружен - неизвестная ошибка.';
                            break;
                    }
                } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
                    $error = 'Не удалось загрузить файл.';
                } else {
                    // Оставляем в имени файла только буквы, цифры и некоторые символы.
                    $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
                    $name = mb_eregi_replace($pattern, '-', $file['name']);
                    $name = mb_ereg_replace('[-]+', '-', $name);

                    // Т.к. есть проблема с кириллицей в названиях файлов (файлы становятся недоступны).
                    // Сделаем их транслит:
                    $converter = array(
                        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
                        'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
                        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
                        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
                        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

                        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
                        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K',
                        'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
                        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
                        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
                    );

                    $name = strtr($name, $converter);
                    $parts = pathinfo($name);

                    if (empty($name) || empty($parts['extension'])) {
                        $error = 'Недопустимое тип файла';
                    } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
                        $error = 'Недопустимый тип файла';
                    } elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
                        $error = 'Недопустимый тип файла';
                    } else {
                        // Чтобы не затереть файл с таким же названием, добавим префикс.
                        $i = 0;
                        $prefix = '';
                        while (is_file($path . $parts['filename'] . $prefix . '.' . $parts['extension'])) {
                            $prefix = '(' . ++$i . ')';
                        }
                        $name = $parts['filename'] . $prefix . '.' . $parts['extension'];

                        // Перемещаем файл в директорию.
                        move_uploaded_file($file['tmp_name'], $path . $name);
                        $params = [
                            'id' => $id,
                            'files' => $name,
                        ];
                        $this->db->query('UPDATE posts SET files = :files  WHERE id = :id', $params);
                    }
                }
            }
        }
    }

    public function isPostExists($id) {
        $params = [
            'id' => $id,
        ];
        return $this->db->column('SELECT id FROM posts WHERE id = :id', $params);
    }

    public function postDelete($id) {
        $params = [
            'id' => $id,
        ];
        $this->db->query('DELETE FROM posts WHERE id = :id', $params);
        unlink('public/materials/'.$id.'.jpg');
    }

    public function postData($id) {
        $params = [
            'id' => $id,
        ];
        return $this->db->row('SELECT * FROM posts WHERE id = :id', $params);
    }

}