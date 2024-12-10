<?php
// admin/category_manage.php: 관리자 카테고리 관리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
include_once __DIR__ . "/admin_header.php";

// POST 요청 시 카테고리 추가 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력된 카테고리명 가져오기
    $name = trim($_POST['name'] ?? '');
    
    // 카테고리명 유효성 검사
    if ($name === '') {
        echo "<script>alert('카테고리명을 입력해주세요.'); history.back();</script>";
        exit;
    }
    
    // 카테고리 추가
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    
    // 추가 완료 후 카테고리 관리 페이지로 이동
    echo "<script>alert('카테고리 추가 완료'); location.href='category_manage.php';</script>";
    exit;
}

// GET 요청 시 카테고리 삭제 처리
if (isset($_GET['delete_id'])) {
    // 삭제할 카테고리 ID 가져오기
    $delete_id = (int)$_GET['delete_id'];
    
    // 카테고리 삭제
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$delete_id]);
    
    // 삭제 완료 후 카테고리 관리 페이지로 이동
    echo "<script>alert('카테고리 삭제 완료'); location.href='category_manage.php';</script>";
    exit;
}

// 카테고리 목록 조회
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 카테고리 관리 페이지 제목 -->
<h2>카테고리 관리</h2>

<!-- 카테고리 추가 폼 -->
<form method="post">
    <!-- 카테고리명 입력 필드 -->
    <p>카테고리명: <input type="text" name="name" required><input type="submit" value="추가"></p>
</form>

<!-- 카테고리 목록 테이블 -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>이름</th>
        <th>관리</th>
    </tr>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <!-- 카테고리 ID 표시 -->
        <td><?php echo htmlspecialchars($cat['id']); ?></td>
        
        <!-- 카테고리 이름 표시 -->
        <td><?php echo htmlspecialchars($cat['name']); ?></td>
        
        <!-- 카테고리 삭제 링크 -->
        <td><a href="?delete_id=<?php echo $cat['id']; ?>" onclick="return confirm('삭제?');">삭제</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
