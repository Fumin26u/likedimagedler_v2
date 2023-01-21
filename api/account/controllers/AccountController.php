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

    // 送られてきたメアドが既存かどうか調べる
    private function isExistEmail($email) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT email FROM user WHERE email = :email');
            $st->bindValue(':email', $email, PDO::PARAM_STR);
            $st->execute();
            
            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();

            return !empty($rows);
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // 送られてきたメアドとユーザーIDが既存かどうか調べる
    private function isExistAccount($email, $user_name) {
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

    // 仮登録メールの送信 
    private function sendPreSignupMail($email, $url) {
        $mail_content = <<<EOM

        ＝＝＝＝＝＝＝＝＝＝仮登録通知＝＝＝＝＝＝＝＝＝＝

        TwimageDLerのご利用ありがとうございます。
        1時間以内に、以下のリンクから本登録をお願いします。

        $url

        本メールは送信専用です。返信は受付できませんのでご了承ください。

        EOM;
        
        // メール送信の実行
        $to = $email;
        $from = 'no-reply@twimagedler.com';
    
        // メールヘッダ
        $header = 'From: ' . mb_encode_mimeheader('TwimageDLer', 'UTF-8') . '<' . $from . '>';
    
        // タイトル
        $title = '【仮登録通知】| TwimageDLer';
    
        // 本文
        $message = '';
        $message .= brReplace(periodReplace($mail_content));
    
        // 送信＋判定
        return mb_send_mail($to, $title, $message, $header);
    }

    // アカウントの仮登録処理 
    public function preRegister($email) {
        // アカウントが既存かどうか判定
        $isExistEmail = $this->isExistEmail($email);
        if ($isExistEmail) {
            $this->response['error'] = true;
            $this->response['content'] = '既に使用されているメールアドレスです。';
            return $this->response;
        }

        // ワンタイムトークンの作成
        $token = bin2hex(random_bytes(16));
        $url = 'http://localhost/likedimagedler_v2/#/register?t=' . $token;

        // DBに仮登録を行う
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
            
            $st = $pdo->prepare('SELECT user_id, is_submitted FROM user_pre WHERE email = :email');
            $st->bindValue(':email', $email, PDO::PARAM_STR);
            $st->execute();
            
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $isPreSubmitted = isset($row['user_id']) ? true : false;

            // 既に仮登録を行っているかつ、本登録が行われていないメアドで登録された場合、更新を行う
            if ($isPreSubmitted) {
                $sql = "UPDATE user_pre SET token = :token, req_time = NOW() WHERE email = :email";
            } else {
                $sql = "INSERT INTO user_pre (token, email, req_time, is_submitted) VALUES (:token, :email, NOW(), 0)";
            }

            $st = $pdo->prepare($sql);
            $st->bindValue(':token', $token, PDO::PARAM_STR);
            $st->bindValue(':email', $email, PDO::PARAM_STR);
            $st->execute();
    
            $pdo->commit();

        } catch(PDOException $e) {
            if (DEBUG) echo $e;
            $this->response['error'] = true;
            $this->response['content'] = '仮登録に失敗しました。お手数ですが、時間を置いて再度お試しいただけますようよろしくお願いします。';
            return $this->response;
        }

        // DB登録に成功した場合、仮登録通知メールを送信
        $isSentMail = $this->sendPreSignupMail($email, $url);
        if ($isSentMail) {
            $this->response['content'] = 'メール送信に成功しました。';
        } else {
            $this->response['error'] = true;
            $this->response['content'] = 'メール送信に失敗しました。お手数ですが、時間を置いて再度お試しいただけますようよろしくお願いします。';
        }
        return $this->response;
    }

    // ワンタイムトークンの認証
    public function verifyToken($token) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
    
            $st = $pdo->prepare('SELECT email, req_time FROM user_pre WHERE token = :token');
            $st->bindValue(':token', $token, PDO::PARAM_STR);
            $st->execute();

            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }

        if (empty($rows)) {
            $this->response['error'] = true;
            $this->response['content'] = '入力されたトークンは正しくありません。';
            return $this->response;
        }

        $date = $rows['req_time'];
        // 現在時刻とDBの登録時刻を比較し1時間以内なら認証完了
        $now = new DateTime();
        $now = $now->modify('-1 Hour')->format('Y-m-d H:i:s');
        if (!($now < $date)) {
            $this->response['error'] = true;
            $this->response['content'] = 'トークンの有効期限が切れました。';
            return $this->response;
        }

        $this->response['content'] = $rows['email'];
        return $this->response;
    }

    // アカウントの登録処理
    public function register($post) {
        $error = $this->isExistAccount(h($post['email']), h($post['user_name']));

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

            // 仮ユーザーテーブルの更新
            $st = $pdo->prepare('UPDATE user_pre SET is_submitted = TRUE WHERE email = :email');
            $st->bindValue(':email', h($post['email']), PDO::PARAM_STR);
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