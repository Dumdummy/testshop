<?php
// public/inquiry_detail.php: 문의 상세 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// GET 파라미터로 문의 ID 받기
$id = (int)($_GET['id'] ?? 0);

// 문의 정보 조회
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->execute([$id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

// 문의가 존재하지 않을 경우
if (!$inquiry) {
    echo "<p>존재하지 않는 문의사항입니다.</p>";
    include_once __DIR__ . "/../includes/footer.php";
    exit;
}

// 비공개글이고 관리자 또는 글 작성자가 아닐 경우 접근 차단
if ($inquiry['public'] == 0 && !isAdmin() && $_SESSION['username'] !== $inquiry['name']) {
    echo "<p>비공개된 문의사항입니다. 접근 권한이 없습니다.</p>";
    include_once __DIR__ . "/../includes/footer.php";
    exit;
}

// 공개글이거나 관리자면 상세 내용 표시
?>
<!-- 문의 상세 내용 표시 -->
<h2>문의 상세 내용</h2>
<p><strong>제목:</strong> <?php echo h($inquiry['subject']); ?></p>
<p><strong>작성자:</strong> <?php echo h($inquiry['name']); ?> (이메일: <?php echo h($inquiry['email']); ?>)</p>
<p><strong>작성일:</strong> <?php echo h($inquiry['created_at']); ?></p>
<p><strong>내용:</strong><br><?php echo nl2br(h($inquiry['message'])); ?></p>

<?php if ($inquiry['image_path']): ?>
    <!-- 첨부 이미지 표시 -->
    <p><strong>첨부 이미지:</strong><br>
    <img src="/<?php echo h($inquiry['image_path']); ?>" alt="첨부이미지" style="max-width:300px;"></p>
<?php endif; ?>

<?php if (isLoggedIn() && $inquiry['user_id'] === $_SESSION['user_id']): ?>
    <!-- 본인 작성 문의일 경우 수정 링크 표시 -->
    <p><a href="inquiry_edit.php?id=<?php echo $inquiry['id']; ?>">수정하기</a></p>
<?php endif; ?>

<?php if ($inquiry['answer'] && trim($inquiry['answer']) !== ''): ?>
    <!-- 관리자 답변 표시 -->
    <hr>
    <h3>관리자 답변</h3>
    <p><?php echo nl2br(h($inquiry['answer'])); ?></p>
<?php endif; ?>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
