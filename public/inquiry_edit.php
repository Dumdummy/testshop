<?php
// public/inquiry_edit.php: 문의 수정 페이지, 본인 작성 문의만 수정 가능

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// 로그인 여부 확인
if (!isLoggedIn()) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// GET 파라미터로 문의 ID 받기
$inquiry_id = (int)($_GET['id'] ?? 0);

// 유효한 문의 ID인지 확인
if ($inquiry_id <= 0) {
    echo "<script>alert('잘못된 접근'); location.href='inquiry_list.php';</script>";
    exit;
}

// 문의 정보 조회
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->execute([$inquiry_id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

// 문의가 존재하지 않을 경우
if (!$inquiry) {
    echo "<script>alert('존재하지 않는 문의입니다.'); location.href='inquiry_list.php';</script>";
    exit;
}

// 본인 작성 문의인지 확인
if ($inquiry['user_id'] !== $_SESSION['user_id']) {
    echo "<script>alert('본인 작성 글이 아닙니다.'); location.href='inquiry_list.php';</script>";
    exit;
}

// POST 요청 시 수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력값 받아오기
    $subject = trim($_POST['subject'] ?? '');
    $public = isset($_POST['public']) ? (int)$_POST['public'] : 1;
    $message = trim($_POST['message'] ?? '');

    // 필수 입력값 검증
    if ($subject === '' || $message === '') {
        echo "<script>alert('제목과 내용을 입력해주세요.'); history.back();</script>";
        exit;
    }

    // 이미지 변경 여부 확인
    $image_path = $inquiry['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // 허용된 이미지 확장자 목록
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // 업로드된 파일의 임시 경로와 원본 이름 가져오기
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        
        // 파일 확장자 추출 및 소문자로 변환
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 확장자 검증
        if (!in_array($ext, $allowed_extensions)) {
            echo "<script>alert('이미지 형식 오류'); history.back();</script>";
            exit;
        }

        // 이미지 업로드 디렉토리 설정
        $upload_dir = __DIR__ . "/../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // 고유한 파일 이름 생성
        $new_file_name = time() . '_' . uniqid() . '.' . $ext;
        $target_path = $upload_dir . $new_file_name;

        // 파일 이동
        if (move_uploaded_file($file_tmp, $target_path)) {
            // 이미지 경로 업데이트
            $image_path = "uploads/" . $new_file_name;
        } else {
            echo "<script>alert('이미지 업로드 실패'); history.back();</script>";
            exit;
        }
    }

    // DB 업데이트
    $upd_stmt = $pdo->prepare("UPDATE inquiries SET subject = ?, message = ?, public = ?, image_path = ? WHERE id = ?");
    $upd_stmt->execute([$subject, $message, $public, $image_path, $inquiry_id]);

    // 수정 완료 후 리다이렉트
    echo "<script>alert('문의가 수정되었습니다.'); location.href='inquiry_detail.php?id=$inquiry_id';</script>";
    exit;
}
?>

<!-- 문의 수정 페이지 제목 -->
<h2>문의 수정</h2>

<!-- 문의 수정 폼 -->
<form method="post" enctype="multipart/form-data">
    <!-- 제목 입력 필드 -->
    <p>제목: <input type="text" name="subject" value="<?php echo h($inquiry['subject']); ?>" style="width:100%;" required></p>
    
    <!-- 공개 여부 선택 필드 -->
    <p>공개 여부:
       <label><input type="radio" name="public" value="1" <?php if ($inquiry['public'] == 1) echo 'checked'; ?>>공개</label>
       <label><input type="radio" name="public" value="0" <?php if ($inquiry['public'] == 0) echo 'checked'; ?>>비공개</label>
    </p>
    
    <!-- 내용 입력 필드 -->
    <p>내용:<br><textarea name="message" rows="5" style="width:100%;" required><?php echo h($inquiry['message']); ?></textarea></p>
    
    <!-- 현재 이미지 표시 -->
    <?php if ($inquiry['image_path']): ?>
        <p>현재 이미지:<br><img src="/<?php echo h($inquiry['image_path']); ?>" style="max-width:200px;"></p>
    <?php endif; ?>
    
    <!-- 이미지 변경 입력 필드 -->
    <p>이미지 변경: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
    
    <!-- 제출 버튼 -->
    <p><input type="submit" value="수정하기"></p>
</form>

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
