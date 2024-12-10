<?php
// public/login.php: 일반 사용자용 로그인 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력값을 받아와서 앞뒤 공백을 제거
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 유효성 검사: 이메일과 비밀번호가 입력되었는지 확인
    if ($email !== '' && $password !== '') {
        // 사용자 정보 조회: 입력된 이메일로 사용자 데이터베이스에서 검색
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 사용자 존재 여부 및 비밀번호 확인
        if ($user && password_verify($password, $user['password'])) {
            // 로그인 성공: 세션에 사용자 정보 저장
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];

            // 성공 메시지 표시 후 메인 페이지로 리다이렉트
            echo "<script>alert('로그인 성공'); location.href='index.php';</script>";
            exit;
        } else {
            // 로그인 실패: 이메일 또는 비밀번호 오류 메시지 표시
            echo "<script>alert('이메일 또는 비밀번호 오류');</script>";
        }
    } else {
        // 입력 부족: 이메일 또는 비밀번호 미입력 메시지 표시
        echo "<script>alert('이메일/비밀번호 입력 필요');</script>";
    }
}
?>

<!-- 로그인 페이지 제목 -->
<h2>로그인</h2>

<!-- 로그인 폼 -->
<form method="post">
    <!-- 이메일 입력 필드 -->
    <p>이메일: <input type="email" name="email" required></p>
    
    <!-- 비밀번호 입력 필드 -->
    <p>비밀번호: <input type="password" name="password" required></p>
    
    <!-- 제출 버튼 -->
    <p><input type="submit" value="로그인"></p>
</form>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
