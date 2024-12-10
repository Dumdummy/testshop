<?php
// public/checkout.php: 주문 결제 페이지
// 배송 정보 입력 후 order_process.php로 넘어가서 주문 처리

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../includes/header.php";

if(!isLoggedIn()) {
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit;
}

$cart=$_SESSION['cart']??[];
if(empty($cart)) {
    echo "<p>장바구니가 비어있습니다.</p>";
    include_once __DIR__ . "/../includes/footer.php";
    exit;
}
?>
<h2>주문 결제</h2>
<form action="order_process.php" method="post">
    <p>수령인 이름: <input type="text" name="receiver_name"></p>
    <p>수령인 주소: <input type="text" name="receiver_address"></p>
    <p>연락처: <input type="text" name="receiver_phone"></p>
    <p><input type="submit" value="주문하기"></p>
</form>
<?php include_once __DIR__ . "/../includes/footer.php"; ?>
