<?php
// public/my_orders.php: 로그인한 사용자의 주문내역 조회 페이지

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../includes/header.php";

if(!isLoggedIn()) {
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit;
}

$user_id=$_SESSION['user_id'];
// 사용자의 주문 목록 조회
$stmt=$pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$orders=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>나의 주문내역</h2>
<?php if(empty($orders)): ?>
<p>주문내역이 없습니다.</p>
<?php else: ?>
    <?php foreach($orders as $order): ?>
        <h3>주문번호: <?php echo h($order['id']); ?> | 총액: <?php echo h($order['total_price']); ?>원 | 주문일: <?php echo h($order['created_at']); ?></h3>
        <p>수령인: <?php echo h($order['receiver_name']); ?> (<?php echo h($order['receiver_phone']); ?>)<br>
        배송지: <?php echo h($order['receiver_address']); ?></p>
        <?php
        // 해당 주문의 아이템 목록
        $oi_stmt=$pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE order_id=?");
        $oi_stmt->execute([$order['id']]);
        $items=$oi_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>상품명</th>
                <th>수량</th>
                <th>단가</th>
                <th>합계</th>
            </tr>
            <?php foreach($items as $it): ?>
            <tr>
                <td><?php echo h($it['name']); ?></td>
                <td><?php echo h($it['quantity']); ?></td>
                <td><?php echo h($it['price']); ?>원</td>
                <td><?php echo ($it['price']*$it['quantity']); ?>원</td>
            </tr>
            <?php endforeach; ?>
        </table>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
