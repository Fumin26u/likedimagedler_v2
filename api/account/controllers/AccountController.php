<?php

class AccountController {
    private $response;

    public function __construct() {
        $this->response = [
            'error' => false,
            'content' => '',
            'user_name' => '',
        ];
    }

    // 不正なデータ送信が行われた場合のエラーログ出力処理
    public function sendErrorLog($content = '不正な送信が行われました。') {
        $this->response['error'] = true;
        $this->response['content'] = $content;
        return $this->response;
    }

    // 送られてきたメアドとユーザーIDが既存かどうか調べる
    private function confirmIsExistSameData($email, $user_name) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT email, user_name FROM user WHERE user_name = :user_name OR email = :email');
            $st->bindValue(':email', $email, PDO::PARAM_STR);
            $st->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            $st->execute();
            
            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
            return !empty($rows) ? '既に使用されているメールアドレスまたはユーザーIDです。' : '';
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // アカウントの登録処理
    public function register($post) {
        $error = $this->confirmIsExistSameData(h($post['email']), h($post['user_name']));

        if ($error !== '') {
            $this->response['error'] = true;
            $this->response['content'] = $error;
            return $this->response;
        }; 

        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
    
            $sql = <<<SQL
            INSERT INTO user 
            (email, user_name, password, premium, is_auth, dl_count, images_count, created_at, updated_at) 
            VALUES 
            (:email, :user_name, :password, 'N', 1, 0, 0, NOW(), NOW())
            SQL;

            $st = $pdo->prepare($sql);
            $st->bindValue(':email', h($post['email']), PDO::PARAM_STR);
            $st->bindValue(':user_name', h($post['user_name']), PDO::PARAM_STR);
            $st->bindValue(':password', password_hash(h($post['password']), PASSWORD_DEFAULT), PDO::PARAM_STR);
            $st->execute();
    
            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }

        $this->response['content'] = 'アカウント登録が完了しました。';
        return $this->response;
    }

    // アカウントのログイン処理
    public function login($post) {
        $error = '';

        $rows = [];
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
    
            $st = $pdo->prepare('SELECT * FROM user WHERE user_name = :user_name');
            $st->bindValue(':user_name', h($post['user_name']), PDO::PARAM_STR);
            $st->execute();
    
            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }

        // 返された配列が空の場合、ユーザ名が存在しない
        if (empty($rows)) {
            $error = '入力されたユーザー名は存在しません。';
            // パスワードの照合
        } else if(!password_verify(h($post['password']), $rows['password'])) {
            $error = '入力されたパスワードが間違っています。';
        }

        if ($error !== '') {
            $this->response['error'] = true;
            $this->response['content'] = $error;
        } else {
            session_start();
            $_SESSION['user_id'] = (int) $rows['user_id'];
            $_SESSION['user_name'] = h($post['user_name']);
            $this->response['content'] = 'ログイン認証が完了しました。';
        }
        return $this->response;
    }

    // ユーザーIDを取得する
    public function getUserData() {
        if ($_SERVER['HTTP_HOST'] === 'localhost') {
            $this->response['user_name'] = 'Fumiya0719';
            $this->response['content'] = 'ユーザーIDを取得しました。';
        } else {
            if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] === '') {
                $this->response['error'] = true;
                $this->response['content'] = 'ユーザーIDの取得に失敗しました。';
            } else {
                $this->response['user_name'] = h($_SESSION['user_name']);
                $this->response['content'] = 'ユーザーIDを取得しました。';
            }
        }
        return $this->response;
    }
    
    // ログアウト処理
    public function logout() {
        $_SESSION = [];
        $this->response['content'] = 'ログアウトしました。';
        return $this->response;
    }
}