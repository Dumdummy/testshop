<?php
// admin/user_manage.php: 관리자 회원 관리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
include_once __DIR__ . "/admin_header.php";

// 관리자 권한 부여
if (isset($_GET['make_admin'])) {
    $userId = (int) $_GET['make_admin'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->execute([$userId]);
    header("Location: user_manage.php"); // 업데이트 후 페이지 새로고침
    exit;
}

// 관리자 권한 해제
if (isset($_GET['remove_admin'])) {
    $userId = (int) $_GET['remove_admin'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
    $stmt->execute([$userId]);
    header("Location: user_manage.php"); // 업데이트 후 페이지 새로고침
    exit;
}

// 회원 목록 조회
$stmt = $pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 회원 관리 페이지 제목 -->
<h2>회원 관리</h2>

<!-- 회원 목록 테이블 -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>이름</th>
        <th>이메일</th>
        <th>권한</th>
        <th>관리</th>
    </tr>
    <?php foreach($users as $u): ?>
    <tr>
        <!-- 회원 ID 표시 -->
        <td><?php echo htmlspecialchars($u['id']); ?></td>
        
        <!-- 회원 이름 표시 -->
        <td><?php echo htmlspecialchars($u['username']); ?></td>
        
        <!-- 회원 이메일 표시 -->
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        
        <!-- 회원 권한 표시 -->
        <td><?php echo $u['is_admin'] ? '관리자' : '일반'; ?></td>
        
        <!-- 권한 관리 링크 -->
        <td>
            <?php if($u['is_admin']): ?>
                <!-- 관리자 권한 해제 링크 -->
                <a href="?remove_admin=<?php echo $u['id']; ?>">관리자해제</a>
            <?php else: ?>
                <!-- 관리자 권한 부여 링크 -->
                <a href="?make_admin=<?php echo $u['id']; ?>">관리자부여</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
