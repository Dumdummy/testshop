<?php
// public/inquiry_detail.php: 문의 상세 페이지
// 이 페이지에서는 문의사항의 상세 내용을 표시하고, 관리자나 작성자가 문의글을 수정할 수 있는 링크를 제공한다.
// 비공개 문의글은 관리자 또는 해당 글 작성자 본인만 접근 가능하도록 수정.

require_once __DIR__ . "/../config/db.php";       // DB 연결 설정
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수 (isAdmin, isLoggedIn, h 등)
include_once __DIR__ . "/../includes/header.php"; // 공용 헤더

$id = (int)($_GET['id'] ?? 0); // GET 파라미터로 문의 ID 수신

// 문의글 조회
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
$stmt->execute([$id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$inquiry) {
    // 해당 ID의 문의글이 없으면 에러 메시지 출력 후 종료
    echo "<p>존재하지 않는 문의사항입니다.</p>";
    include_once __DIR__ . "/../includes/footer.php";
    exit;
}

// 접근 권한 판단 로직
if($inquiry['public'] == 0) {
    // 비공개 글
    if(!isAdmin()) {
        // 관리자가 아니면 작성자 본인인지 체크
        if(!isLoggedIn() || $inquiry['user_id'] !== $_SESSION['user_id']) {
            // 비로그인 상태이거나, 로그인했어도 작성자가 아니면 접근 불가
            echo "<p>비공개된 문의사항입니다. 접근 권한이 없습니다.</p>";
            include_once __DIR__ . "/../includes/footer.php";
            exit;
        }
    }
}
// 공개글(public=1)인 경우 별도 조건 없음, 바로 접근 가능

// 문의 상세 내용 표시
?>
<h2>문의 상세 내용</h2>
<p><strong>제목:</strong> <?php echo h($inquiry['subject']); ?></p>
<p><strong>작성자:</strong> <?php echo h($inquiry['name']); ?> (이메일: <?php echo h($inquiry['email']); ?>)</p>
<p><strong>작성일:</strong> <?php echo h($inquiry['created_at']); ?></p>
<p><strong>내용:</strong><br><?php echo nl2br(h($inquiry['message'])); ?></p>

<?php if($inquiry['image_path']): ?>
    <!-- 첨부 이미지가 있을 경우 표시 -->
    <p><strong>첨부 이미지:</strong><br>
    <img src="/<?php echo h($inquiry['image_path']); ?>" style="max-width:300px;"></p>
<?php endif; ?>

<?php if($inquiry['answer'] && trim($inquiry['answer'])!==''): ?>
    <!-- 관리자 답변이 존재한다면 표시 -->
    <hr>
    <h3>관리자 답변</h3>
    <p><?php echo nl2br(h($inquiry['answer'])); ?></p>
<?php endif; ?>

<?php
// 수정 버튼 표시 로직
// 관리자이거나, 현재 로그인한 사용자가 작성자이면 수정 가능
if(isLoggedIn()) {
    if(isAdmin() || $inquiry['user_id'] === $_SESSION['user_id']) {
        // 관리자 또는 글 작성자일 경우 수정하기 버튼 표시
        echo '<p><a href="inquiry_edit.php?id='.h($inquiry['id']).'" style="background:#333; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px;">수정하기</a></p>';
    }
}
?>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
