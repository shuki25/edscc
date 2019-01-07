<?php

include 'db.php';
//error_reporting(E_STRICT);
$api_token = "1ec3c2dad39b79f9b3ac0d7ee760d009";

try {
    $dbh = new DB();
}
catch (exception $e) {
    header("HTTP/1.1 500 Server Internal Error");
    exit;
}

$sql = "insert into debug (detail, posted_at) value (?, now())";
$rs= $dbh->prepare($sql);

ob_start();
var_dump($_SERVER);
var_dump($_POST);
$debug[] = ob_get_clean();

$rs->execute($debug);

if(isset($_POST)) {

    $sql = "select * from user where apikey=?";
    $params[] = trim($_POST['_token']);

    $rs = $dbh->prepare($sql);
    $rs->execute($params);
    $data = $rs->fetch(PDO::FETCH_ASSOC);

    if(!isset($data['id'])) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }

    var_dump(json_decode($_POST['data'],1));

    $test_data = json_decode($_POST['data']);

    $sql = "insert into edmc (user_id, entry, entered_at) values(?,?,now())";
    $params = array();
    $params[] = $data['id'];
    $params[] = json_encode($test_data);

    $rs = $dbh->prepare($sql);
    $rs->execute($params);
    $new_id = $dbh->lastInsertId();

    var_dump($new_id);
    var_dump($data);
    var_dump($_POST);
    var_dump($params);

}
?>
