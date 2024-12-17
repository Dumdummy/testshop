<?php
// admin/tatistics.php: 매출 통계 페이지
// 이 페이지에서는 최근 7일간의 일별 매출액, 총 주문수, 평균 주문 금액, 그리고 인기 상품 TOP 5 등을 표시한다.
// 추가로 최근 7일간 일별 주문 건수와 평균 주문금액(=총매출/주문수)을 계산하여 좀 더 자세한 통계를 제공한다.

require_once __DIR__ . "/../config/db.php";       // DB 연결 설정
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수 (isAdmin 등)

// 관리자 공용 헤더 포함(상단 메뉴 표시)
include_once __DIR__ . "/admin_header.php";

// 최근 7일간 일별 매출, 주문 건수, 평균 주문 금액 조회
// orders 테이블에서 날짜별 total_price 합, 주문 건수, 평균값 추출
$revenue_stmt=$pdo->query("
    SELECT DATE(created_at) as order_date, 
           SUM(total_price) as daily_revenue,
           COUNT(*) as order_count,
           AVG(total_price) as avg_order_value
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY order_date DESC
");
$revenues=$revenue_stmt->fetchAll(PDO::FETCH_ASSOC);

// 인기 상품 TOP 5 (전체 기간)
$popular_stmt=$pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$popular_products=$popular_stmt->fetchAll(PDO::FETCH_ASSOC);

// 추가로 최근 7일간 총매출, 총 주문 건수, 총 평균 주문금액 등을 계산할 수도 있다.
// 하지만 여기서는 이미 daily로 구하므로 별도 합산은 생략 가능.
// 필요하면 아래와 같이 전체 합계를 구할 수 있다.
/*
$total_revenue = 0;
$total_orders = 0;
foreach($revenues as $rev) {
    $total_revenue += $rev['daily_revenue'];
    $total_orders += $rev['order_count'];
}
$overall_avg = $total_orders > 0 ? ($total_revenue / $total_orders) : 0;
*/

// HTML 출력 시작
?>
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>매출 통계</title></head>
<body>
<h2>매출 통계</h2>
<h3>최근 7일간 일별 매출 상세</h3>
<!-- 일별로 매출액, 주문 건수, 평균 주문금액 표시 -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>날짜</th>
        <th>매출액</th>
        <th>주문 건수</th>
        <th>평균 주문 금액</th>
    </tr>
    <?php foreach($revenues as $rev): ?>
    <tr>
        <td><?php echo htmlspecialchars($rev['order_date']); ?></td>
        <td><?php echo htmlspecialchars($rev['daily_revenue']); ?>원</td>
        <td><?php echo htmlspecialchars($rev['order_count']); ?>건</td>
        <td><?php echo number_format($rev['avg_order_value'], 0); ?>원</td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>인기 상품 TOP 5 (전체기간)</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr><th>상품명</th><th>판매수량</th></tr>
    <?php foreach($popular_products as $pop): ?>
    <tr>
        <td><?php echo htmlspecialchars($pop['name']); ?></td>
        <td><?php echo htmlspecialchars($pop['total_sold']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
</body>
</html>
