<?php
// admin/statistics.php: 관리자 매출 통계 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
include_once __DIR__ . "/admin_header.php";

// 최근 7일 매출 조회
$revenue_stmt = $pdo->query("
    SELECT DATE(created_at) as order_date, SUM(total_price) as daily_revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY order_date DESC
");
$revenues = $revenue_stmt->fetchAll(PDO::FETCH_ASSOC);

// 인기 상품 TOP 5 조회
$popular_stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$popular_products = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 매출 통계 페이지 제목 -->
<h2>매출 통계</h2>

<!-- 최근 7일간 일별 매출 테이블 -->
<h3>최근 7일간 일별 매출</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>날짜</th>
        <th>매출액</th>
    </tr>
    <?php foreach ($revenues as $rev): ?>
    <tr>
        <td><?php echo htmlspecialchars($rev['order_date']); ?></td>
        <td><?php echo htmlspecialchars($rev['daily_revenue']); ?>원</td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- 인기 상품 TOP 5 테이블 -->
<h3>인기 상품 TOP 5</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>상품명</th>
        <th>판매수량</th>
    </tr>
    <?php foreach ($popular_products as $pop): ?>
    <tr>
        <td><?php echo htmlspecialchars($pop['name']); ?></td>
        <td><?php echo htmlspecialchars($pop['total_sold']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
