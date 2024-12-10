<?php
// admin/inquiry_manage.php: 관리자 문의 관리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
require_once __DIR__ . "/admin_header.php";

// 문의 삭제 처리
if (isset($_GET['delete_id'])) {
    // 삭제할 문의 ID 가져오기
    $delete_id = (int)$_GET['delete_id'];
    
    // 유효한 문의 ID인지 확인
    if ($delete_id > 0) {
        // 문의 존재 여부 확인
        $check_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
        $check_stmt->execute([$delete_id]);
        $inq = $check_stmt->fetch(PDO::FETCH_ASSOC);

        // 문의가 존재하면 삭제
        if ($inq) {
            $del_stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            echo "<script>alert('문의가 삭제되었습니다.'); location.href='inquiry_manage.php';</script>";
            exit;
        } else {
            // 문의가 존재하지 않을 경우
            echo "<script>alert('존재하지 않는 문의입니다.'); location.href='inquiry_manage.php';</script>";
            exit;
        }
    }
}

// 답변 등록/수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer_id'])) {
    // 답변할 문의 ID 가져오기
    $answer_id = (int)$_POST['answer_id'];
    
    // 답변 내용 가져오기
    $answer = trim($_POST['answer'] ?? '');

    // 문의 존재 여부 확인
    $check_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
    $check_stmt->execute([$answer_id]);
    $inq = $check_stmt->fetch(PDO::FETCH_ASSOC);

    // 문의가 존재하지 않을 경우
    if (!$inq) {
        echo "<script>alert('존재하지 않는 문의입니다.'); location.href='inquiry_manage.php';</script>";
        exit;
    }

    // 답변 업데이트
    $upd_stmt = $pdo->prepare("UPDATE inquiries SET answer = ? WHERE id = ?");
    $upd_stmt->execute([$answer, $answer_id]);

    // 답변 완료 후 페이지 리다이렉트
    echo "<script>alert('답변이 등록/수정되었습니다.'); location.href='inquiry_manage.php';</script>";
    exit;
}

// 답변 폼 표시용 변수 초기화
$answer_mode = false;
$answer_inquiry = null;

// 답변 폼 요청 확인
if (isset($_GET['answer_id'])) {
    // 답변할 문의 ID 가져오기
    $answer_id = (int)$_GET['answer_id'];
    
    // 유효한 ID인지 확인
    if ($answer_id > 0) {
        // 문의 정보 조회
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
        $stmt->execute([$answer_id]);
        $answer_inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 문의가 존재하면 답변 모드 활성화
        if ($answer_inquiry) {
            $answer_mode = true;
        }
    }
}

// 전체 문의 조회
$stmt = $pdo->query("SELECT * FROM inquiries ORDER BY id DESC");
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- 문의 관리 페이지 제목 -->
<h2>문의 관리</h2>

<?php if ($answer_mode && $answer_inquiry): ?>
    <!-- 답변 작성 폼 -->
    <h3>답변 작성</h3>
    <form method="post" style="margin-bottom:20px;">
        <!-- 답변할 문의 ID 숨김 필드 -->
        <input type="hidden" name="answer_id" value="<?php echo $answer_inquiry['id']; ?>">
        
        <!-- 문의 제목 표시 -->
        <p>제목: <?php echo htmlspecialchars($answer_inquiry['subject']); ?></p>
        
        <!-- 문의 작성자 및 이메일 표시 -->
        <p>작성자: <?php echo htmlspecialchars($answer_inquiry['name']); ?> (<?php echo htmlspecialchars($answer_inquiry['email']); ?>)</p>
        
        <!-- 문의 내용 표시 -->
        <p>내용:<br><?php echo nl2br(htmlspecialchars($answer_inquiry['message'])); ?></p>
        
        <!-- 답변 입력 필드 -->
        <p>답변:<br><textarea name="answer" rows="5" cols="50" style="width:100%;"><?php echo htmlspecialchars($answer_inquiry['answer'] ?? ''); ?></textarea></p>
        
        <!-- 제출 버튼 -->
        <p><input type="submit" value="등록/수정"></p>
    </form>
<?php endif; ?>

<!-- 문의 목록 테이블 시작 -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>제목</th>
        <th>작성자</th>
        <th>이메일</th>
        <th>공개여부</th>
        <th>작성일</th>
        <th>답변상태</th>
        <th>관리</th>
    </tr>
    <?php foreach ($inquiries as $inq): ?>
    <tr>
        <!-- 문의 ID 표시 -->
        <td><?php echo htmlspecialchars($inq['id']); ?></td>
        
        <!-- 문의 제목 표시 -->
        <td><?php echo htmlspecialchars($inq['subject']); ?></td>
        
        <!-- 문의 작성자 표시 -->
        <td><?php echo htmlspecialchars($inq['name']); ?></td>
        
        <!-- 문의 작성자 이메일 표시 -->
        <td><?php echo htmlspecialchars($inq['email']); ?></td>
        
        <!-- 공개 여부 표시 -->
        <td><?php echo $inq['public'] ? '공개' : '비공개'; ?></td>
        
        <!-- 문의 작성일 표시 -->
        <td><?php echo htmlspecialchars($inq['created_at']); ?></td>
        
        <!-- 답변 상태 표시 -->
        <td><?php echo ($inq['answer'] && trim($inq['answer']) !== '') ? '답변완료' : '미답변'; ?></td>
        
        <!-- 관리 옵션: 답변하기 및 삭제 -->
        <td>
            <a href="?answer_id=<?php echo $inq['id']; ?>">답변하기</a> | 
            <a href="?delete_id=<?php echo $inq['id']; ?>" onclick="return confirm('삭제하시겠습니까?');">삭제</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<!-- 문의 목록 테이블 종료 -->

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
