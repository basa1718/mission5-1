<!DOCTYPE html>
<html lang="ja">
    
<head>
    <meta chaeset="UTF-8">
    <title>lesson</title>
</head>

<body>
    
    <?php
     //発生するエラーをtry-catch構文で検知し、エラーが起こった場合に所定の処理を行わせる  
    try{
        //4-1 DB設定
     $dsn = 'データベース名';
     $user = 'ユーザー名';
     $pass = 'パスワード';
     $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        //  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        //  →データベース操作でエラーが発生した場合に警告（Worning: ）として表示するために設定するオプション
        
        // $pdoが接続できなかったら
         if($pdo != true){
            echo"データベースの接続に失敗しました";
         }
    
        // 4-2  このデータベースサーバーに、データを登録するための「テーブル」を作成
        //  「 IF NOT EXISTS 」は「もしまだこのテーブルが存在しないなら」という意味
     $sql = "CREATE TABLE IF NOT EXISTS aoki2"
     ." ("
        //  以下"カラム名 型"
       //  id ・自動で登録されるナンバリング
     . "id INT AUTO_INCREMENT PRIMARY KEY,"
        //  name ・名前を入れる。文字列、半角英数で32文字まで
     . "name char(32),"
        //  comment ・コメントを入れる。文字列、長めの文章も入る
     . "comment TEXT,"
    // 日付
     . "date DATETIME,"
    // パスワード
     ."password varchar(8)"
     .");";
    // データベース管理システムに対する問合せ(＝クエリ)に$pdoを格納したものを$stmtとします.
    //$stmt = とすべきは、実行後にSQLの実行結果に関する情報を得たい場合であり、
    //ただSQLを実行するだけであれば$db->query($sql);のように書けばよい
     $stmt = $pdo->query($sql);
 
    // $eはException（例外）を受けるための任意の変数 
    } catch ( PDOException $e ) {
        print( "接続エラー:". $e->getMessage() );
        die();
    }
    // ①新規入力
    if(!empty($_POST['name']) && !empty($_POST['comment']) && empty($_POST["edit_num"] )){
        // 入力フォームからデータを取得
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $date = date("Y/m/d H:i:s");
        $post_pass = $_POST["post_pass"];
        
        // パスワードが入力されたら
        if(!empty($post_pass)){
            // データベースに書き込み
         $sql = $pdo -> prepare("INSERT INTO aoki2 (name, comment, date, password) 
                                    VALUES (:name, :comment, :date, :password)");
         $sql -> bindParam(':name', $name, PDO::PARAM_STR);
         $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
         $sql -> bindParam(':date', $date, PDO::PARAM_STR);
         $sql -> bindParam(':password', $post_pass, PDO::PARAM_STR);
         $sql -> execute();  
         
        }  
    // ②削除
    
    // 削除対象番号とパスワードが入力されたときにカラムを選択
    }elseif(!empty($_POST["deletenum"]) && (!empty($_POST["del_pass"]))){
        $deletenum = $_POST["deletenum"];
        $del_pass = $_POST["del_pass"];
        $id = $deletenum;
        
    // データを選択
	    $sql = 'SELECT * FROM aoki2 where id =:id';
        $stmt = $pdo->prepare($sql);     #変数を扱うときはprepare 定数のときはquery
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
    // テーブル内のデータからパスワードだけ引っ張ってくる
     foreach ($results as $row){
              $DEL_PASS = $row['password'];
     }
    //  echo $DEL_PASS
    
    // 削除パスが引っ張ってきたパスと等しいとき
    if($del_pass == $DEL_PASS){
    // データを削除
	    $sql = 'DELETE FROM aoki2 where id =:id';
	    $stmt = $pdo -> prepare($sql);
	    $stmt -> bindParam(':id', $id, PDO::PARAM_INT);
	    $stmt -> execute();
	
// 	削除パスが引っ張ってきたパスと等しくなかったら
    }elseif($del_pass != $DEL_PASS){
            print"エラー！パスワードが間違っています";
        }

    // ③編集
    
    // 編集対象番号とパスワードが入力されたときにカラムを選択
    }elseif(!empty($_POST["editnum"]) && (!empty($_POST["edit_pass"]))){
        $editnum = $_POST["editnum"];
        $edit_pass = $_POST["edit_pass"];
        $id = $editnum;
        
    // データを選択
	    $sql = 'SELECT * FROM aoki2 where id =:id';
        $stmt = $pdo->prepare($sql);     #変数を扱うときはprepare 定数のときはquery
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
    // テーブル内のデータからパスワードだけ引っ張ってくる
     foreach($results as $row){
             $EDIT_PASS = $row["password"];
             $EDIT_NUM = $row["id"];
             $EDIT_NAME = $row["name"];
             $EDIT_COMMENT = $row["comment"];
     }
    // echo $EDIT_PASS;
    
    // 編集パスが引っ張ってきたパスと等しいとき
    if($edit_pass == $EDIT_PASS){
    // 投稿フォームに内容を引っ張ってくる
        $edit_num = $EDIT_NUM;
        $edit_name = $EDIT_NAME;
        $edit_comment = $EDIT_COMMENT;
        $edit_PASS = $EDIT_PASS;#↑の条件分岐にある$edit_pass & $EDIT_PASSとは異なる
        
    // データを編集
	
// 	編集パスが引っ張ってきたパスと等しくなかったら
    }elseif($edit_pass != $EDIT_PASS){
            print"エラー！パスワードが間違っています";
    }
}
  
// // ④編集後の処理
// // 
// 名前とコメントと編集対象番号が空じゃないとき
if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["edit_num"])
                          && !empty($_POST["post_pass"])){
        $id = $_POST["edit_num"];
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $date = date("Y/m/d H:i:s");
        $post_pass = $_POST["post_pass"];
    
    // UPDATE文を変数に格納
	    $sql = 'UPDATE aoki2 SET name=:name,comment=:comment,date=:date,password=:password where id =:id';
	    $stmt = $pdo -> prepare($sql);
	    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
	    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    	$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    	$stmt->bindParam(':date', $date, PDO::PARAM_STR);
    	$stmt->bindParam(':password', $post_pass, PDO::PARAM_STR);
    	$stmt->execute();
}
   ?>
   
       <!-- 新規投稿フォーム -->
    <form action = "" method = "post">
【　投稿フォーム　】<br>
名前：　　　　　<input type = "text" name = "name" 
                 value = "<?php if(isset($edit_name)){echo $edit_name;} ?>" required><br>
コメント：　　　<input type="text" name = "comment" 
                 value = "<?php if(isset($edit_comment)){echo $edit_comment;}?>" required>
                <input type="hidden" name = "edit_num"
                 value = "<?php if(isset($edit_num)){echo $edit_num;}?>"><br>
パスワード：　　<input type="password" name = "post_pass" required><br>
                <input type = "submit" name = "submit">    
    </form>
    <!-- 投稿削除フォーム -->
    <form action = "" method = "post">
【　削除フォーム　】<br>
投稿番号：　　　<input type = "number" name = "deletenum" required><br>
パスワード：　　<input type = "password" name = "del_pass" required><br>
                <input type = "submit" name = "submit2" value = "削除">
    </form>
    <!--編集番号指定用フォーム-->
    <form action = "" method = "post">
【　編集フォーム　】<br>
投稿番号：　　　<input type = "number" name = "editnum" required><br>
パスワード：　　<input type = "password" name = "edit_pass" required><br>
                <input type = "submit" name = "submit3" value ="編集">
     </form>   
    

    <?php
  // ブラウザに表示するもの(mission4-6)
     $sql = 'SELECT * FROM aoki2';
     $stmt = $pdo->query($sql);
     $results = $stmt->fetchAll();
     foreach ($results as $row){
  //$rowの中にはテーブルのカラム名が入る
    echo "【投稿番号】:".$row['id'].'<br>';
    echo "【名前】:" .$row['name'].'<br>';
    echo "【コメント】:". $row['comment'].'<br>';
    echo "【投稿日時】:" .$row['date'].'<br>';
    // echo "【パスワード】:".$row['password'].'<br>';
    echo "<hr>";
     }
     
    
    ?>
 
 </body>
 </html>
