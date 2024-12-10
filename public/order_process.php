<?php
// public/order_process.php: 실제 주문 처리 로직
// orders, order_items 테이블에 데이터 삽입

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";

if(!isLoggedIn()) {
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit;
}

$receiver_name=trim($_POST['receiver_name']??'');
$receiver_address=trim($_POST['receiver_address']??'');
$receiver_phone=trim($_POST['receiver_phone']??'');

if($receiver_name==''||$receiver_address==''||$receiver_phone=='') {
    echo "<script>alert('모든 배송정보 필요');history.back();</script>";
    exit;
}

$cart=$_SESSION['cart']??[];
if(empty($cart)) {
    echo "<script>alert('장바구니가 비어있습니다.');location.href='index.php';</script>";
    exit;
}

// 상품 정보 조회
$ids=array_column($cart,'product_id');
$inClause=implode(',',array_fill(0,count($ids),'?'));
$stmt=$pdo->prepare("SELECT * FROM products WHERE id IN ($inClause)");
$stmt->execute($ids);
$products=$stmt->fetchAll(PDO::FETCH_ASSOC);

$productMap=[];
foreach($products as $p) {
    $productMap[$p['id']]=$p;
}

// 총 가격 계산
$total_price=0;
foreach($cart as $item) {
    $p=$productMap[$item['product_id']];
    $total_price+=$p['price']*$item['quantity'];
}

// 트랜잭션 시작
$pdo->beginTransaction();
try {
    // orders 삽입
    $stmt=$pdo->prepare("INSERT INTO orders (user_id,receiver_name,receiver_address,receiver_phone,total_price,created_at) VALUES(?,?,?,?,?,NOW())");
    $stmt->execute([$_SESSION['user_id'],$receiver_name,$receiver_address,$receiver_phone,$total_price]);
    $order_id=$pdo->lastInsertId();

    // order_items 삽입
    $stmt_items=$pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)");
    foreach($cart as $item) {
        $p=$productMap[$item['product_id']];
        $stmt_items->execute([$order_id,$p['id'],$item['quantity'],$p['price']]);
    }

    // 커밋
    $pdo->commit();

    // 장바구니 비움
    unset($_SESSION['cart']);

    echo "<script>alert('주문 완료 (주문번호: $order_id)');location.href='index.php';</script>";
    exit;
} catch(Exception $e) {
    // 롤백
    $pdo->rollBack();
    echo "<script>alert('주문 처리 중 오류');history.back();</script>";
    exit;
}
