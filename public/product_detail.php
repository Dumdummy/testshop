<?php
// public/product_detail.php: 상품 상세 페이지, 리뷰 목록, 리뷰 작성, 장바구니 담기 등

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// GET 파라미터로 상품 ID 받기
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 상품 정보 조회
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// 상품이 존재하지 않을 경우
if (!$product) {
    echo "<p>존재하지 않는 상품입니다.</p>";
    include_once __DIR__ . "/../includes/footer.php";
    exit;
}

// 리뷰 목록 조회
$review_stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
$review_stmt->execute([$product_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- 상품 상세 정보 표시 -->
<h2><?php echo h($product['name']); ?></h2>

<!-- 상품 이미지 표시 -->
<?php if (!empty($product['image_path'])): ?>
    <p><img src="/<?php echo h($product['image_path']); ?>" alt="<?php echo h($product['name']); ?>" style="max-width:300px;"></p>
<?php else: ?>
    <p><img src="/assets/images/no-image.png" alt="이미지 없음" style="max-width:300px;"></p>
<?php endif; ?>

<!-- 상품 가격 표시 -->
<p>가격: <?php echo h($product['price']); ?>원</p>

<!-- 상품 설명 표시 -->
<p>설명: <?php echo nl2br(h($product['description'])); ?></p>

<hr>

<!-- 장바구니 담기 폼 -->
<h3>장바구니 담기</h3>
<form action="add_to_cart.php" method="post">
    <input type="hidden" name="product_id" value="<?php echo h($product_id); ?>">
    <p>수량: <input type="number" name="quantity" value="1" min="1"></p>
    <p><input type="submit" value="장바구니 담기"></p>
</form>

<hr>

<!-- 리뷰 목록 표시 -->
<h3>리뷰 목록</h3>
<?php if (count($reviews) > 0): ?>
    <ul>
        <?php foreach ($reviews as $rev): ?>
        <li>
            <strong><?php echo h($rev['author']); ?> (<?php echo $rev['rating']; ?>점):</strong><br>
            <?php echo nl2br(h($rev['content'])); ?>
        </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>아직 리뷰가 없습니다.</p>
<?php endif; ?>

<hr>

<!-- 리뷰 작성 폼 -->
<h3>리뷰 작성</h3>
<form action="review_write.php" method="post">
    <input type="hidden" name="product_id" value="<?php echo h($product_id); ?>">
    <p>작성자: <input type="text" name="author" required></p>
    <p>평점: 
        <select name="rating" required>
            <option value="5">★★★★★(5)</option>
            <option value="4">★★★★(4)</option>
            <option value="3">★★★(3)</option>
            <option value="2">★★(2)</option>
            <option value="1">★(1)</option>
        </select>
    </p>
    <p>내용:<br><textarea name="content" rows="5" cols="50" required></textarea></p>
    <p><input type="submit" value="리뷰 등록"></p>
</form>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
