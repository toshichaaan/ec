<?php //var_dump($_POST); ?>
<?php 
$log = date('Y-m-d H:i:s');
$item_name ='';
$item_img = '';
$price='';
$item_id='';
$error=[];
$data=[];
$process_kind = "";
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

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  if (isset($_POST['do']) === true) {
      $do = $_POST['do'];
    }
    
  //購入する処理
  if($do === "buy"){
     //セレクト文
       $sql = 'select user_id,ec_cart.item_id,amount,name,price,img,stock
               from ec_cart 
               join ec_item_master on
               ec_cart.item_id = ec_item_master.item_id 
               join ec_item_stock on
               ec_cart.item_id = ec_item_stock.item_id
               where user_id=?';
       // SQL文を実行する準備
       $stmt = $dbh->prepare($sql);
       $stmt-> bindValue(1, $user_id, PDO::PARAM_STR);
       // SQLを実行する処理
       $stmt->execute();
       // 購入した商品一覧
       $data = $stmt->fetchAll();
  }
  
foreach($data as $item){
    $stock=$item['stock'];
    $amount=$item['amount'];
    $price=$item['price'];
  //在庫が1以上なら購入できる
  if($stock-$amount<=0){
     $error[]='在庫がありません';
  }
}
  //在庫数減らす処理
  if(count($error)===0){
    foreach($data as $item){
      $stock=$item['stock'];
      $item_id=$item['item_id'];
      $update_datetime=date('Y-m-d H:i:s');
        $stock=$stock-$amount;
        
        $sql='update ec_item_stock set stock=?,update_datetime=? where item_id=?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $stock, PDO::PARAM_INT);
        $stmt->bindValue(2, $update_datetime, PDO::PARAM_INT);
        $stmt->bindValue(3, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $sql='delete from ec_cart where item_id=?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        
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
  <title>購入結果</title>
 <h1>購入ありがとうございました</h1>
</head>
<body>

  <!--買えない場合のエラー表示-->
  
<?php foreach ($data as $value) { ?>
<div><img src="./img/<?php print $value['img']; ?>"></div>
<div><?php print htmlspecialchars($value['name'],ENT_QUOTES); ?></div>
<div><?php print $value['price']; ?>円</div>
  <?php } ?>

<?php

//各item_idの金額を合計する処理
$sum = 0;
foreach ($data as $value) {
$sum = $sum + $value['price'] * $value['amount'];
 }?>
<div>合計<?php print $sum; ?>円</div>

  <a href=top.php>トップに戻る</a>
</body>
</html>




