<?php
// public/cart.php: 장바구니 페이지
// 이 페이지에서는 세션에 담긴 장바구니 상품을 보여주고, 수량 변경 및 상품 삭제(제거) 기능을 제공한다.

require_once __DIR__ . "/../config/db.php";       // DB 연결
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수 (isLoggedIn, h 등)
include_once __DIR__ . "/../includes/header.php"; // 공용 헤더

$cart = $_SESSION['cart'] ?? [];

// POST 요청 처리 (수량 변경 및 삭제)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 수량 변경 처리
    if(isset($_POST['product_id'], $_POST['quantity'])) {
        $pid = (int)$_POST['product_id'];
        $qty = max((int)$_POST['quantity'], 1);
        foreach($cart as $key => $item) {
            if($item['product_id'] == $pid) {
                $cart[$key]['quantity'] = $qty;
            }
        }
        $_SESSION['cart'] = $cart;
    }

    // 상품 제거 처리
    if(isset($_POST['remove_id'])) {
        $remove_id = (int)$_POST['remove_id'];
        foreach($cart as $key=>$item) {
            if($item['product_id'] == $remove_id) {
                // 해당 상품 제거
                unset($cart[$key]);
                // 배열 재정렬
                $cart = array_values($cart);
                break;
            }
        }
        $_SESSION['cart'] = $cart;
    }
}

// 장바구니 비어있을 경우
if(empty($cart)) {
    echo "<p>장바구니가 비어있습니다.</p>";
} else {
    // 상품 정보 DB에서 가져오기
    $ids = array_column($cart, 'product_id');
    $inClause = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($inClause)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // product_id를 key로 하여 상품 참조
    $productMap = [];
    foreach($products as $p) {
        $productMap[$p['id']] = $p;
    }

    $total = 0;
    echo "<table border='1' cellpadding='5'>
          <tr><th>상품명</th><th>가격</th><th>수량</th><th>합계</th><th>삭제</th></tr>";
    foreach($cart as $item) {
        $p = $productMap[$item['product_id']];
        $lineTotal = $p['price'] * $item['quantity'];
        $total += $lineTotal;
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
              <td>
                <!-- 상품 제거 폼 -->
                <form method='post' style='display:inline;' onsubmit=\"return confirm('상품을 장바구니에서 제거하시겠습니까?');\">
                  <input type='hidden' name='remove_id' value='".h($p['id'])."'>
                  <input type='submit' value='제거'>
                </form>
              </td>
              </tr>";
    }
    echo "<tr><td colspan='3' align='right'>총합계:</td><td colspan='2'>".$total."원</td></tr>";
    echo "</table>";

    // 주문 페이지로 이동 링크
    echo "<p><a href='checkout.php' style='background:#333; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px;'>주문하기</a></p>";
}

include_once __DIR__ . "/../includes/footer.php";
