<?php
$log = date('Y-m-d H:i:s');
$name ='';
$price='';
$error=[];
$data=[];
$do='';
$amount='';
$comment='';
$img_dir    = './img/';    // アップロードした画像ファイルの保存ディレクトリ
$host     = 'localhost';
$username = 'codecamp47616';   // MySQLのユーザ名
$password = 'codecamp47616';       // MySQLのパスワード
$dbname   = 'codecamp47616';   // MySQLのDB名(今回、MySQLのユーザ名を入力してください)
$charset  = 'utf8';   // データベースの文字コード
session_start();//セッションスタート
if(isset($_SESSION['user_id'])){
  $user=$_SESSION['user_name'];
  $user_id=$_SESSION['user_id'];
}else{
 header('Location: login.php');
}

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
//var_dump($_POST);
try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  if (isset($_POST['do']) === true) {
      $do = $_POST['do'];
    }
 
// // SQL文を作成
  
   if(isset($_POST['item_id']) === true){
      $item_id=$_POST['item_id'];   
     }
//     //削除するか判断する処理
   if($do === "delete"){
    
        $sql = 'DELETE FROM ec_cart
                where item_id=? and user_id=?';
        $stmt = $dbh->prepare($sql);
        $stmt-> bindValue(1, $item_id,PDO::PARAM_INT);
        $stmt-> bindValue(2,$user_id,PDO::PARAM_INT);
          // SQLを実行
        $stmt->execute();
    }
    
    if (isset($_POST['amount']) === true) {
        $amount = $_POST['amount'];
        $comment='個数が変更されました';
      }
    
      if($amount === ''){
        $error[]='個数を入力してください';
      }else if(preg_match('/^[0-9]+$/',$amount)!==1){
        $error[]="個数は0以上の整数値で入力してください";
      }
      if(count($error) === 0){
        
        try{

          $sql = 'update ec_cart set amount=? where item_id=?';
         // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
         // SQL文のプレースホルダに値をバインド
       
          $stmt->bindValue(1, $amount, PDO::PARAM_INT);
          $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
      
          $stmt->execute();
          
      
        } catch (PDOException $e) {
          echo '理由：'.$e->getMessage();
        
          $error[]='投稿に失敗しました';
        }
          
      }

  
$sql = 'select user_id,ec_cart.item_id,amount,name,price,img
    from ec_cart 
    join ec_item_master on
    ec_cart.item_id = ec_item_master.item_id
    where user_id=?';
      // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    $stmt-> bindValue(1, $user_id, PDO::PARAM_STR);

      // SQLを実行
    $stmt->execute();
      // レコードの取得
    $data = $stmt->fetchAll();
    

} catch (PDOException $e) {
  echo '接続できませんでした。理由：'.$e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
<title>商品カート</title>
<link rel="stylesheet" href="top.css">
</head>

<body>
   <header>
   <div class="logo">My Freezer</div>
   <div class="box"><a href="./cart.php"><img src="img/kaimonokago.png" class="cart"></a></div>
   </header>

<p class="nemu">ユーザー名：<?php echo $user;?></p>
<a href="top.php">トップに戻る</a>

<?php foreach ($data as $value) { ?>
   <div><img src="./img/<?php print $value['img']; ?>"></div>
   <div><?php print htmlspecialchars($value['name'],ENT_QUOTES); ?></div>
   <div><?php print $value['price']; ?>円</div>
    <?php foreach ((array)$comment as $read) { ?>
  <p><?php print $read;?></p>
  <?php } ?>

    <form method="post">
      <input type="text" name="amount" value='<?php print $value['amount']; ?>'>個
      <input type="submit" name="submit" value="変更">
      <input type="hidden" name="process_kind" value="update_stock">
      <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
    </form>
   <form method="post">
       <input type="hidden" name="do" value="delete">
       <input type="submit" value="削除" method="post">
       <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
   </form>
<?php }?>

<?php

//各item_idの金額を合計する処理
$sum = 0;
foreach ($data as $value) {
$sum = $sum + $value['price'] * $value['amount'];
 }?>
<div>合計<?php print $sum; ?>円</div>


<form action="finish.php" method="post">
<p><input type="hidden" name="do" value="buy">
   <input type="submit" value="購入する" method="post">
</p>
</form>