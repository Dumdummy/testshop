<?php
// public/cart.php: 장바구니 페이지
// 세션에 담긴 상품을 보여주고 수량 변경 가능, 주문으로 진행 가능

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../includes/header.php";

$cart=$_SESSION['cart']??[];

// POST 요청(수량 변경)
if($_SERVER['REQUEST_METHOD']==='POST') {
    if(isset($_POST['product_id'], $_POST['quantity'])) {
        $pid=(int)$_POST['product_id'];
        $qty=max((int)$_POST['quantity'],1);
        foreach($cart as $key=>$item) {
            if($item['product_id']==$pid) {
                $cart[$key]['quantity']=$qty;
            }
        }
        $_SESSION['cart']=$cart;
    }
}

// 장바구니 비어있을 경우
if(empty($cart)) {
    echo "<p>장바구니가 비어있습니다.</p>";
} else {
    // 상품 정보 DB 조회
    $ids=array_column($cart,'product_id');
    $inClause=implode(',',array_fill(0,count($ids),'?'));
    $stmt=$pdo->prepare("SELECT * FROM products WHERE id IN ($inClause)");
    $stmt->execute($ids);
    $products=$stmt->fetchAll(PDO::FETCH_ASSOC);

    $productMap=[];
    foreach($products as $p) {
        $productMap[$p['id']]=$p;
    }

    $total=0;
    echo "<table border='1' cellpadding='5'>
          <tr><th>상품명</th><th>가격</th><th>수량</th><th>합계</th></tr>";
    foreach($cart as $item) {
        $p=$productMap[$item['product_id']];
        $lineTotal=$p['price']*$item['quantity'];
        $total+=$lineTotal;
        echo "<tr>
              <td>".h($p['name'])."</td>
              <td>".h($p['price'])."원</td>
              <td>
                <form method='post' style='display:inline;'>
                  <input type='hidden' name='product_id' value='".h($p['id'])."'>
                  <input type='number' name='quantity' value='".h($item['quantity'])."' min='1'>
                  <input type='submit' value='변경'>
                </form>
              </td>
              <td>".$lineTotal."원</td>
              </tr>";
    }
    echo "<tr><td colspan='3' align='right'>총합계:</td><td>".$total."원</td></tr>";
    echo "</table>";

    // 주문 페이지 링크
    echo "<p><a href='checkout.php'>주문하기</a></p>";
}

include_once __DIR__ . "/../includes/footer.php";
