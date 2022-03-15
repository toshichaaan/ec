<?php
$log = date('Y-m-d H:i:s');
$user_name=[];
$password=[];
$error=[];
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
    }else if(preg_match('/[a-z0-9]{6,}/',$user_name)!==1){
        $error[]="使用可能文字は半角英数字」かつ「文字数は 6 文字以上」です";
     }
     
    
    if (isset($_POST['password']) === true) {
      $password = $_POST['password'];
    }
    if($password === ''){
        $error[]='パスワードを入力してください';
    }else if(preg_match('/[a-z0-9]{6,}/',$password)!==1){
        $error[]="使用可能文字は半角英数字」かつ「文字数は 6 文字以上」です";
     }
    
    
    if(count($error) === 0){
       $dbh->beginTransaction();
       try{
          $sql = 'insert into ec_user (user_name,password,create_datetime) values (?,?,?)';
          $stmt = $dbh->prepare($sql);
  //       // SQL文のプレースホルダに値をバインド
          $stmt->bindValue(1, $user_name,    PDO::PARAM_STR);
          $stmt->bindValue(2, $password, PDO::PARAM_INT);
          $stmt->bindValue(3, $log, PDO::PARAM_STR);
          $stmt->execute();
          $dbh->commit();
          header('Location: login.php');
          exit;
          
    
       }catch (PDOException $e) {
          $dbh->rollback();
         echo '登録できませんでした。理由：'.$e->getMessage();
        }
    }  
    }
      }catch (PDOException $e) {
         echo '接続できませんでした。理由：'.$e->getMessage();
      }
?>

<!DOCTYPE html>
<html lang="ja">
<title>新規登録画面</title>
<form action="new_login.php" method="post">
<h2>新規会員登録</h2>
<?php foreach ($error as $read) { ?>
  <p><?php print $read;?></p>
<?php } ?>
<p>名前：<input type="text" name="user_name"></p>
<p>パスワード：<input type="password" name="password"></p>
<input type="submit" name="submit" value='登録' > 
</from>