<?php

class ImageController {
    use ImagePDOController;
    private $userID;

    public function __construct() {
        $this->userID = $_SERVER['HTTP_HOST'] === 'localhost' ? 2 : (int) h($_SESSION['user_id']);
    }

    // 連想配列(辞書)型のkeyとvalueをイコールで連結した文字列を返す
    private function convertDictToString($key, $value): string {
        return h($key).'='.h($value);
    }

    // 画像とその情報の取得
    public function get($post) {
        $queryArray = [];
        foreach ($post as $key => $param) {
            if ($param === '' || is_null($param)) continue;
            $queryArray[] = $this->convertDictToString($key, $param);
        }

        // 最新取得画像IDが存在する場合クエリに追加
        $latestPostId = $this->getLatestDl($this->userID, $post['userID']);
        if (count($queryArray) < 6 && $latestPostId !== false) {
            $queryArray[] = "suspendID=$latestPostId";
        }

        $query = implode(',', $queryArray);
        exec("python getImage.py $query", $output);
        return json_decode($output[0]);
        // return ['content' => $query];
    }

    // pythonを実行してpixivpyで画像をDLする
    private function downloadImages(string $query) {
        exec("python dlImage.py $query", $output);
        return json_decode($output[0]);
    }

    // 画像を保存しているディレクトリの圧縮
    private function convertDirectoryToZip(string $fileName, string $dirPath) {
        exec("zip -r $fileName $dirPath");
    }

    // URLから画像をDLして圧縮
    public function download($urls, $zipFileName, $imagesDirPath) {
        // URL一覧からクエリを生成し画像をDL
        $queue = [];
        for ($i = 0; $i < count($urls); $i++) {
            $queue[] = $urls[$i];
            // 20枚ごとにurlを分割してdlを行う(リンク数が多いと正常にDLできない為)
            if ($i % 20 === 0 || $i === count($urls) - 1) {
                $query = h(implode(',', $queue));
                $result = $this->downloadImages($query);
                if ($result->error) break;

                $queue = [];
            }
        } 

        $response = [
            'isSuccessDownload' => true,
            'content' => '',
        ];
        // 正常にDL出来なかった場合エラーを返却して終了
        if ($result->error) {
            $response['isSuccessDownload'] = false;
            $response['content'] = '画像のダウンロードに失敗しました。';
            return $response;
        }

        // DLが完了した場合zip変換
        $this->convertDirectoryToZip($zipFileName, $imagesDirPath);

        if (file_exists($zipFileName)) {
            $response['content'] = 'zipファイルを作成しました。';
        } else {
            $response['isSuccessDownload'] = false;
            $response['content'] = '指定されたzipファイルは存在しません。';
        }
        return $response;
    }

    // 指定したファイルまたはフォルダの消去
    public function remove(string $filePath) {
        // ファイルが存在しない場合
        if (!file_exists($filePath)) return;

        // ファイルが存在する場合
        if (is_file($filePath)) {
            unlink($filePath);
            return;
        }

        // ディレクトリの場合
        if ($handle = opendir($filePath)) {
            while (($file = readdir($handle)) !== false) {
                // ディレクトリの場合無操作
                if ($file === '.' || $file === '..') continue;
                // ファイルの場合再起呼び出しをして削除
                $this->remove($filePath . $file);
            }
            // ディレクトリを閉じて削除
            closedir($handle);
            rmdir($filePath);
        }

        return file_exists($filePath)
            ? ['isSuccessRemove' => false, 'content' => 'ファイルの削除に失敗しました。']
            : ['isSuccessRemove' => true, 'content' => 'ファイルを削除しました。'];
    }

    // 画像DL回数・枚数、最新取得画像の投稿IDの更新
    public function updateInfo($post) {
        list($dlCount, $imagesCount) = $this->getCounterValue($this->userID);
        if (is_null($dlCount)) $dlCount = 0;
        if (is_null($imagesCount)) $imagesCount = 0;

        // カウンタのインクリメント
        $dlCount += 1;
        $imagesCount += (int) h($post['imageCount']);
        // カウンタを更新
        $this->updateCounter($this->userID, $dlCount, $imagesCount);

        // 最新取得画像IDの存在可否に応じてSQLを作成
        if ($this->getLatestDl($this->userID, $post['pixUserID']) !== false) {
            $sql = <<<SQL
                UPDATE latest_dl_pix SET
                post_id = :post_id,
                created_at = NOW()
                WHERE
                user_id = :user_id AND pix_id = :pix_id
            SQL;
        } else {
            $sql = <<<SQL
                INSERT INTO latest_dl_pix
                (user_id, post_id, pix_id, created_at)
                VALUES
                (:user_id, :post_id, :pix_id, NOW())
            SQL;
        }
        // 最新取得画像IDの更新
        $this->updateLatestDl($sql, $this->userID, h($post['latestID']), h($post['pixUserID']));

        return [
            'isSuccessUpdate' => true,
            'content' => '画像情報の更新に成功しました。'
        ];
    }
}

trait ImagePDOController {
    // userテーブルの画像DL回数・枚数カウンタの数値を取得
    protected function getCounterValue(int $userID) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT dl_count, images_count FROM user WHERE user_id = :user_id');
            $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetch(PDO::FETCH_ASSOC);

            $pdo->commit();

            return [$rows['dl_count'], $rows['images_count']];
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // カウンタの更新
    protected function updateCounter($userID, $dlCount, $imagesCount) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $sql = <<<SQL
                UPDATE user SET
                dl_count = :dl_count,
                images_count = :images_count
                WHERE user_id = :user_id
            SQL;
            $st = $pdo->prepare($sql);
            $st->bindValue(':dl_count', $dlCount, PDO::PARAM_INT);
            $st->bindValue(':images_count', $imagesCount, PDO::PARAM_INT);
            $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
            $st->execute();

            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // 最新取得画像IDの存在可否を取得
    protected function getLatestDl($userID, $pixUserID) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT * FROM latest_dl_pix WHERE user_id = :user_id AND pix_id = :pix_id');
            $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
            $st->bindValue(':pix_id', $pixUserID, PDO::PARAM_STR);
            $st->execute();
            $rows = $st->fetch(PDO::FETCH_ASSOC);

            $pdo->commit();
            return !empty($rows) ? $rows['post_id'] : false;
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    protected function updateLatestDl($sql, $userID, $latestID, $pixUserID) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare($sql);
            $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
            // ツイートIDは数値だが、桁数が12以上なのでVARCHAR型で保存
            $st->bindValue(':post_id', $latestID, PDO::PARAM_STR);
            $st->bindValue(':pix_id', $pixUserID, PDO::PARAM_STR);
            $st->execute();

            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}
