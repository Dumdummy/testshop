<?php
// public/inquiry_process.php: 문의 처리 스크립트

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 사용자 입력값 받아오기
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$public = isset($_POST['public']) ? (int)$_POST['public'] : 1;
$message = trim($_POST['message'] ?? '');

// 로그인한 경우 user_id 설정
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

// 필수 입력값 검증
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    echo "<script>alert('모든 필드를 입력해주세요.'); history.back();</script>";
    exit;
}

// 이미지 업로드 처리
$image_path = null;
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
        echo "<script>alert('이미지 형식 오류 (jpg, jpeg, png, gif만 가능)'); history.back();</script>";
        exit;
    }

    // 이미지 업로드 디렉토리 설정
    $upload_dir = __DIR__ . '/../uploads/';
    
    // 업로드 디렉토리가 존재하지 않으면 생성
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // 고유한 파일 이름 생성 (타임스탬프와 고유 ID 사용)
    $new_file_name = time() . '_' . uniqid() . '.' . $ext;
    $target_path = $upload_dir . $new_file_name;

    // 파일을 업로드 디렉토리로 이동
    if (move_uploaded_file($file_tmp, $target_path)) {
        // 웹에서 접근 가능한 경로로 설정
        $image_path = "uploads/" . $new_file_name;
    } else {
        echo "<script>alert('이미지 업로드 실패'); history.back();</script>";
        exit;
    }
}

// DB 삽입 (user_id 포함)
$stmt = $pdo->prepare("INSERT INTO inquiries (user_id, name, email, subject, message, public, image_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute([$user_id, $name, $email, $subject, $message, $public, $image_path]);

// 문의 등록 완료 후 리다이렉트
echo "<script>alert('문의가 등록되었습니다.'); location.href='inquiry_list.php';</script>";
exit;
?>
