<?php
// public/add_to_cart.php: 상품을 장바구니에 추가하는 처리

require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";

$product_id=(int)($_POST['product_id']??0);
$quantity=max((int)($_POST['quantity']??1),1);

// 유효성 검사
if($product_id<=0) {
    echo "<script>alert('잘못된 요청');history.back();</script>";
    exit;
}

// 장바구니 세션 초기화
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart']=[];
}

$found=false;
// 이미 장바구니에 있는 상품이면 수량 추가
foreach($_SESSION['cart'] as $key=>$item) {
    if($item['product_id']==$product_id) {
        $_SESSION['cart'][$key]['quantity']+=$quantity;
        $found=true;
        break;
    }
}

// 없으면 새로 추가
if(!$found) {
    $_SESSION['cart'][]=['product_id'=>$product_id,'quantity'=>$quantity];
}

echo "<script>alert('장바구니에 담았습니다.');location.href='cart.php';</script>";
exit;
