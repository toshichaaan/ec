<?php
$log = date('Y-m-d H:i:s');
$name ='';
$price='';
$error=[];
$data=[];
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
if(isset($_GET['item_id'])){
    $item_id=$_GET['item_id'];
}else{
    echo 'セッションに失敗しました';
}
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//ドリンクidと金額の受け取り処理

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
 
// SQL文を作成
    $sql = 'select * 
    from ec_item_master 
    inner join ec_item_stock on ec_item_master.item_id = ec_item_stock.item_id 
    where status=1 and ec_item_master.item_id=?';
      // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    $stmt-> bindValue(1, $item_id,    PDO::PARAM_STR);

      // SQLを実行
    $stmt->execute();
      // レコードの取得
    $data = $stmt->fetchAll();
    //var_dump($data);
    
    
    
} catch (PDOException $e) {
  echo '接続できませんでした。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
<title>商品詳細ページ</title>
<link rel="stylesheet" href="top.css">
</head>

<body>
   <header>
   <div class="logo">My Freezer</div>
   <div class="box"><a href="./cart.php"><img src="img/kaimonokago.png" class="cart"></a></div>
   </header>
   <a href="top.php">トップに戻る</a>
   <a class="nemu" href="./logout.php">ログアウト</a>
   <a href="cart.php" class="cart"></a>
   <p class="nemu">ユーザー名：<?php echo $user;?></p>

<?php foreach ($data as $value) { ?>
   <?php print $value['item_id'];?>
   <div><img src="./img/<?php print $value['img']; ?>"></div>
   <div><?php print htmlspecialchars($value['name'],ENT_QUOTES); ?></div>
   <div><?php print $value['price']; ?>円</div>
   <?php if (($value['stock']) >= 1) { ?>
      <p>在庫あり</p>
    <?php }else{ 
    ?>
    <form method="post" action="#">
    <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>" > 
    <input type="submit" value="カートに入れる">
    </form>
  <?php } ?>
  
<?php }?>

</body>
</html>
