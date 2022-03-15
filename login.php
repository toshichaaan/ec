<?php
$log = date('Y-m-d H:i:s');
$user_name=[];
$password=[];
$error=[];
$comment=[];
$host     = 'localhost';
$username = 'codecamp47616';   // MySQLのユーザ名
$pass = 'codecamp47616';       // MySQLのパスワード
$dbname   = 'codecamp47616';   // MySQLのDB名(今回、MySQLのユーザ名を入力してください)
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['user_name']) === true) {
      $user_name = $_POST['user_name'];
    }
    if($user_name === ''){
        $error[]='名前を入力してください';
    }
    if (isset($_POST['password']) === true) {
      $password = $_POST['password'];
    }
    if($password === ''){
        $error[]='パスワードを入力してください';
    }
    
    
    if(count($error) === 0){
     $sql = 'select * from ec_user where user_name=? and password=?';
    // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
          $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
          $stmt->bindValue(2, $password, PDO::PARAM_STR);
      
    // SQLを実行
     $stmt->execute();
    // レコードの取得
     $data = $stmt->fetchAll();
     //var_dump($data);
     if(count($data) > 0  ){
       //トップページに行く権限の処理
       //セッション変数にユーザーIDとユーザーの名前を入れておく処理
       session_start();//セッションスタート
       $_SESSION['user_id'] = $data[0]['user_id'];
       $_SESSION['user_name'] = $data[0]['user_name'];//セッション変数に登録
      
       //リダイレクトでトップページに行く処理
       header('Location: top.php');
     }else{
       //失敗したときの処理
       //header('Location: login.php');
       echo "ログインできませんでした";
     }
     }
          
  }
     } catch (PDOException $e) {
  echo '接続できませんでした。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン画面</title>
<link rel="stylesheet" href="login.css">
</head>

<body>
  <header>
  <div class="logo">My Freezer</div>
  <div class="box"><a href="./cart.php"><img src="img/kaimonokago.png" class="cart"></a></div>
  </header>

  <form action="./login.php" method="post">
    <div class="content"><label for="user_name">ユーザ名</label>
    <input type="text" class="block" id="user_name" name="user_name" value=""></div>
    <div class="content"><label for="passwd">パスワード</label>
    <input type="password" class="block" id="passwd" name="password" value=""></div>
      <div class="content"><a href="./new_login.php">ユーザーの新規作成</a></div>
    
    <div class="content"><input type="submit" value="ログイン"></div>
  </form>
</body>
</html>