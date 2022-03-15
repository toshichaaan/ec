<?php
$log = date('Y-m-d H:i:s');
$comment=[];
$error=[];
$data=[];
$process_kind = "";
$do='';
$img_dir    = './img/';    // アップロードした画像ファイルの保存ディレクトリ
$host     = 'localhost';
$username = 'codecamp47616';   // MySQLのユーザ名
$password = 'codecamp47616';       // MySQLのパスワード
$dbname   = 'codecamp47616';   // MySQLのDB名(今回、MySQLのユーザ名を入力してください)
$charset  = 'utf8';   // データベースの文字コード



// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['process_kind']) === true) {
      $process_kind = $_POST['process_kind'];
    }
    
    //var_dump($process_kind);
    //新規商品追加処理
    if($process_kind === "append_item"){
      $name ='';
      $price='';
      $stock='';
      $status='';
      $img='';
      if (isset($_POST['price']) === true) {
        $price = $_POST['price'];
      }
      if($price === ''){
        $error[]='価格を入力してください';
      }else if(preg_match('/^[0-9]+$/',$price)!==1){
        $error[]="価格は0以上の整数値で入力してください";
      }
      
      if (isset($_POST['name']) === true) {
        //$name = $_POST['name'];
        $name = preg_replace('/\A[　\s]*|[　\s]*\z/u', '', $_POST['name']);
      }
    
      if($name === ''){
        $error[]='商品名を入力してください';
      }
      if (isset($_POST['stock']) === true) {
        $stock = $_POST['stock'];
      }
    
      if($stock === ''){
        $error[]='個数を入力してください';
      }else if(preg_match('/^[0-9]+$/',$stock)!==1){
        $error[]="個数は0以上の整数値で入力してください";
      }
      
      if ( isset($_POST['status']) === true ) {
        $status = $_POST['status'];
      }
      if( $status!=='0'  && $status!=='1'){
        $error[]="ステータス値が不正です";
      }
      
      if(count($error) === 0){
        // HTTP POST でファイルがアップロードされたかどうかチェック
        if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {
          // 画像の拡張子を取得
          $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
          // 指定の拡張子であるかどうかチェック
          
          if ($extension === 'jpg'||$extension === 'jpeg'|| $extension === 'png') {
            // 保存する新しいファイル名の生成（ユニークな値を設定する）
            $img = sha1(uniqid(mt_rand(), true)). '.' . $extension;
            // 同名ファイルが存在するかどうかチェック
            if (is_file( $img_dir.$img) !== TRUE) {
              // アップロードされたファイルを指定ディレクトリに移動して保存
              if (move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $img) !== TRUE) {
                $err_msg[] = 'ファイルアップロードに失敗しました';
              }
            } else {
              $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
            }
          } else {
            $error[] = 'ファイル形式が異なります。画像ファイルはJPEGとPNGのみ利用可能です。';
          }
        } else {
          $error[] = 'ファイルを選択してください';
        }
      }
  
    
      if(count($error) === 0){
        $dbh->beginTransaction();
        try{
          $sql = 'insert into ec_item_master (name,price,img,status,create_datetime) values (?,?,?,?,?)';
  //       // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
  //       // SQL文のプレースホルダに値をバインド
          $stmt->bindValue(1, $name,    PDO::PARAM_STR);
          $stmt->bindValue(2, $price, PDO::PARAM_INT);
          $stmt->bindValue(3, $img, PDO::PARAM_STR);
          $stmt->bindValue(4, $status, PDO::PARAM_INT);
          $stmt->bindValue(5, $log, PDO::PARAM_STR);
          $stmt->execute();
          
          $item_id = $dbh->lastInsertId('item_id');
  
          $sql = 'insert into ec_item_stock (stock,create_datetime,update_datetime,item_id) values (?,?,?,?)';
  //       // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
  //       // SQL文のプレースホルダに値をバインド
       
          $stmt->bindValue(1, $stock, PDO::PARAM_INT);
          $stmt->bindValue(2, $log, PDO::PARAM_STR);
          $stmt->bindValue(3, $log, PDO::PARAM_STR);
          $stmt->bindValue(4, $item_id, PDO::PARAM_INT);
        
  
  
          $stmt->execute();
          $dbh->commit();
          
          $comment='商品が登録できました';
          
        } catch (PDOException $e) {
          echo '理由：'.$e->getMessage();
          $dbh->rollback();
          $error[]='投稿に失敗しました';
        }
       //var_dump($price);
      }
      //ここから在庫変更処理
    } else if ($process_kind === 'update_stock') {
      //var_dump($_POST);
      $stock='';
      $item_id='';
      
      if (isset($_POST['item_id']) === true) {
        $item_id = $_POST['item_id'];
      }
      
      if($item_id === ''){
        $error[]='処理は実行できません';
      }else if(preg_match('/^[0-9]+$/',$item_id)!==1){
        $error[]="処理は実行できません";
      }
      
      if (isset($_POST['stock']) === true) {
        $stock = $_POST['stock'];
        $comment='在庫数が変更されました';
      }
    
      if($stock === ''){
        $error[]='個数を入力してください';
      }else if(preg_match('/^[0-9]+$/',$stock)!==1){
        $error[]="個数は0以上の整数値で入力してください";
      }
      

      if(count($error) === 0){
        
        try{

          $sql = 'update ec_item_stock set stock=? where item_id=?';
         // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
         // SQL文のプレースホルダに値をバインド
       
          $stmt->bindValue(1, $stock, PDO::PARAM_INT);
          $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
      
          $stmt->execute();
          
      
        } catch (PDOException $e) {
          echo '理由：'.$e->getMessage();
        
          $error[]='投稿に失敗しました';
        }
          
      }
      //在庫数変更処理
    } else if ($process_kind === 'change_status') {
      //ステータス変更処理
      $status='';
      $drink_id='';
      
      
      if (isset($_POST['item_id']) === true) {
        $drink_id = $_POST['item_id'];
      }
       if($drink_id === ''){
        $error[]='処理は実行できません';
      }else if(preg_match('/^[0-9]+$/',$drink_id)!==1){
        $error[]="処理は実行できません";
      }
      
      if (isset($_POST['status']) === true) {
        $status = $_POST['status'];
      }
   // var_dump($status);
      if($status === ''){
        $error[]='ステータスを入力してください';
      }else if(preg_match('/^[01]$/',$status)!==1){
        $error[]="ステータスが正しくないです";
      }
      
      
      if(count($error) === 0){
        
        try{
          //idとstatusを入れる
          $sql = 'update ec_item_master set status=? where item_id=?';
          
          $stmt = $dbh->prepare($sql);
          //-------------反転 if文　押すたびに変わる
      if (isset($_POST['status']) === true) {
        $status = $_POST['status'];
      }
      //var_dump($status);
          //status変更処理
          if($status === '0'){
        $status='1';
        $comment="ステータスが変更されました";
      }else if($status === '1'){
        $status="0";
        $comment="ステータスが変更されました";
      }
      
          $stmt->bindValue(1, $status, PDO::PARAM_INT);
          $stmt->bindValue(2, $drink_id, PDO::PARAM_INT);
          $stmt->execute();
        }catch (PDOException $e) {
          echo '理由：'.$e->getMessage();
        
          $error[]='投稿に失敗しました';
        }

      }
      

    }
      else if($process_kind === "delete"){
      //削除するか判断する処理
     if(isset($_POST['item_id']) === true){
      $item_id=$_POST['item_id'];   
     }
    
        $sql = 'DELETE FROM ec_item_stock
                where item_id=?';
        $stmt = $dbh->prepare($sql);
        $stmt-> bindValue(1, $item_id,PDO::PARAM_INT);
          // SQLを実行
        $stmt->execute();
    }
  } 
  

      
  // SQL文を作成
  $sql = 'select * from ec_item_master inner join ec_item_stock on ec_item_master.item_id = ec_item_stock.item_id';
    // SQL文を実行する準備
  $stmt = $dbh->prepare($sql);
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
  <title>管理ツール</title>
  <style>
  table{
    border-collapse:collapse;
  }
    th,td {
      border:solid 1px #000000;
    }
    .gray{background-color:#ddd}
  </style>
</head>
<body>
<?php foreach ($error as $read) { ?>
  <p><?php print $read;?></p>
<?php } ?>
  <h1>My Freezer管理ツール</h1>
  <p>新商品追加</p>
  <form method="post" enctype="multipart/form-data">
    <p>名前:
    <input type="text" name="name"></p>
    <p>値段:
    <input type="text" name="price"></p>
    <p>個数:
    <input type="text" name="stock"></p>
    <p><input type="file" name="new_img" value="ファイルを選択"></p>
    <select name="status">
      <option value="0">非公開</option>
      <option value="1">公開</option>
    </select>
    <p><input type="submit" name="submit" value="商品を追加"></p>
    <input type="hidden" name="process_kind" value="append_item">
  </form>
  
  <p>商品情報変更</p>
  <p>商品一覧</p>
  
   <?php foreach ((array)$comment as $read) { ?>
  <p><?php print $read;?></p>
  <?php } ?>
  
  <table>
    <tr>
      <th>商品画像</th>
      <th>商品名</th>
      <th>価格</th>
      <th>在庫数</th>
      <th>ステータス</th>
      
    </tr>
<?php foreach ($data as $value) { ?>
<?php if($value['status']===0){
  $status_label='非公開→公開';
  $row_style='gray';
}else{
  $status_label='公開→非公開';
  $row_style='';
}?>
<tr class="<?php print $row_style; ?>">
  <td><img src="./img/<?php print $value['img']; ?>"></td>
  <td><?php print htmlspecialchars($value['name'], ENT_QUOTES); ?></td>
  <td><?php print $value['price']; ?></td>
  <td>
    <form method="post">
      <input type="text" name="stock" value='<?php print $value['stock']; ?>'>個
      <input type="submit" name="submit" value="変更">
      <input type="hidden" name="process_kind" value="update_stock">
      <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
    </form>
  </td>
  <td>
    <form method="post">
      
      <input type="submit"  value="<?php print $status_label; ?>"> 
      <input type="hidden" name="process_kind" value="change_status">
      <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
      <input type="hidden" name="status" value="<?php print $value['status']; ?>">
    </form>
  </td>
  <td>
   <form method="post">
       <input type="hidden" name="process_kind" value="delete">
       <input type="submit" value="削除" method="post">
       <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
   </form>
  </td>
</tr>

<?php } ?>
<pre>
<?php


?>
    </pre>
</body>
</html>