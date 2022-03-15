<?php
$log = date('Y-m-d H:i:s');
$name ='';
$price='';
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
//データベースから公開状態のやつを表示する処理

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//ドリンクidと金額の受け取り処理

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//検索に一致した商品を表示する処理
  
  //送られてきたデータを受け取る
  
//カートに入れる処理
if(isset($_POST['item_id']) === true) {
    $item_id = $_POST['item_id'];
    //カートに同じ商品があるか調べる処理
    $sql="select item_id
          from ec_cart
          where item_id=? and user_id=?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $item_id,PDO::PARAM_INT);
    $stmt->bindValue(2, $user_id,PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
    //item_idが同じだと同じ商品

    if( count($data) > 0){
    $sql="UPDATE ec_cart
          SET amount = amount + 1
          WHERE item_id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $item_id,PDO::PARAM_INT);
    $stmt->execute();
    }else{
    $sql = "INSERT INTO ec_cart (item_id,user_id,amount,create_datetime) VALUES (?,?,?,?)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $item_id,    PDO::PARAM_INT);
    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(3, 1, PDO::PARAM_INT);
    $stmt->bindValue(4, $log, PDO::PARAM_STR);
      // SQLを実行
    $stmt->execute();
    }
}

if(isset($_POST['search']) === true){
  
  $name = $_POST['search'];
  //キーワードで検索
    $sql = "select name,ec_item_master.item_id,price,img,stock
            from ec_item_master join ec_item_stock
            on ec_item_master.item_id = ec_item_stock.item_id
            where name = ? ";
    //sql文を準備する
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $name,PDO::PARAM_STR);
    //sql文を実行する
    $stmt->execute();
    //キーワードに合致する商品一覧
    $data = $stmt->fetchAll();
    //商品表示
}else{
    $sql = 'select * from ec_item_master inner join ec_item_stock on ec_item_master.item_id = ec_item_stock.item_id where status=1 ';
      // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
      // SQLを実行
    $stmt->execute();
      // レコードの取得
    $data = $stmt->fetchAll();
 }
} catch (PDOException $e) {
  echo '接続できませんでした。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品ページ</title>
</head>
<link rel="stylesheet" href="top.css">
</head>

<body>
   <header>
   <div class="logo">My Freezer</div>
   <div class="box"><a href="./cart.php"><img src="img/kaimonokago.png" class="cart"></a></div>
   </header>
   <a class="nemu" href="./logout.php">ログアウト</a>
   <a href="cart.php" class="cart"></a>
   <p class="nemu">ユーザー名：<?php echo $user;?></p>
    </div>
    <form action="top.php" method="post">
 <input type="search" name="search" placeholder='キーワード入力'>
 <input type="submit" name="submit" value='検索'>
</form>

  
<?php foreach ($error as $read) { ?>
  <p><?php print $read;?></p>
<?php } ?>
  <h1>My Freezer</h1>

  
  
      
<?php foreach ($data as $value) { ?>
<div class="product"><a href="detail.php?item_id=<?php print $value['item_id'];?>">
  <div><img src="./img/<?php print $value['img']; ?>"></div></a>
  <div><?php print htmlspecialchars($value['name'],ENT_QUOTES); ?></div>
  <div><?php print $value['price']; ?>円</div>
</div>
  
  <?php if (($value['stock']) === 0) { ?>
      <p>売り切れ</p>
    <?php }else{ ?>
    <form method="post" action="#">
    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>" > 
    <input type="submit" value="カートに入れる">
    </form>
  <?php } ?>
    
<?php } ?>
　　
 


</body>
</html>
