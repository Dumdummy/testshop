<?php
// admin/product_list.php: 관리자 상품 관리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
include_once __DIR__ . "/admin_header.php";

// 상품 삭제 처리 로직
if (isset($_GET['delete_id'])) {
    // 삭제할 상품 ID 가져오기
    $delete_id = (int)$_GET['delete_id'];
    
    // 유효한 상품 ID인지 확인
    if ($delete_id > 0) {
        // 상품 존재 여부 확인
        $check_stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $check_stmt->execute([$delete_id]);
        $product = $check_stmt->fetch(PDO::FETCH_ASSOC);

        // 상품이 존재하면 삭제 수행
        if ($product) {
            $del_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $del_stmt->execute([$delete_id]);

            // 삭제 완료 후 알림 및 상품 목록 페이지로 이동
            echo "<script>alert('상품이 삭제되었습니다.'); location.href='product_list.php';</script>";
            exit;
        } else {
            // 존재하지 않는 상품일 경우 알림 및 상품 목록 페이지로 이동
            echo "<script>alert('존재하지 않는 상품입니다.'); location.href='product_list.php';</script>";
            exit;
        }
    } else {
        // 잘못된 접근일 경우 알림 및 상품 목록 페이지로 이동
        echo "<script>alert('잘못된 접근입니다.'); location.href='product_list.php';</script>";
        exit;
    }
}

// 전체 상품 목록 조회
$stmt = $pdo->query("
    SELECT p.id, p.name, p.price, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 상품 관리 페이지 제목 -->
<h2>상품 관리</h2>

<!-- 상품 등록 페이지로 이동하는 버튼 -->
<p><a href="product_add.php" class="btn">상품 등록하기</a></p>

<!-- 상품 목록 테이블 시작 -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>상품명</th>
        <th>가격</th>
        <th>카테고리</th>
        <th>관리</th>
    </tr>
    <?php foreach ($products as $p): ?>
    <tr>
        <!-- 상품 ID 표시 -->
        <td><?php echo htmlspecialchars($p['id']); ?></td>
        
        <!-- 상품명 표시 -->
        <td><?php echo htmlspecialchars($p['name']); ?></td>
        
        <!-- 가격 표시 -->
        <td><?php echo htmlspecialchars($p['price']); ?></td>
        
        <!-- 카테고리명 표시 -->
        <td><?php echo htmlspecialchars($p['category_name']); ?></td>
        
        <!-- 관리 옵션: 수정 및 삭제 링크 -->
        <td>
            <a href="product_edit.php?id=<?php echo $p['id']; ?>">수정</a> | 
            <a href="product_list.php?delete_id=<?php echo $p['id']; ?>" 
               onclick="return confirm('삭제하시겠습니까?');">삭제</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<!-- 상품 목록 테이블 종료 -->

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
