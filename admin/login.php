<?php
// admin/login.php: 관리자 로그인 처리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력값을 받아와서 앞뒤 공백을 제거
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 유효성 검사: 이메일과 비밀번호가 입력되었는지 확인
    if ($email !== '' && $password !== '') {
        // 관리자 계정 조회: 입력된 이메일과 is_admin=1인 사용자 검색
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // 비밀번호 확인
            // 현재는 비밀번호를 단순 비교하고 있으나, 보안을 위해 password_verify 사용 권장
            if (password_verify($password, $admin['password'])) {
            // if ($admin['password'] === $password) {
                // 로그인 성공: 세션에 관리자 정보 저장
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['is_admin'] = $admin['is_admin'];

                // 성공 메시지 표시 후 관리자 메인 페이지로 리다이렉트
                echo "<script>alert('관리자 로그인 성공'); location.href='index.php';</script>";
                exit;
            } else {
                // 비밀번호 불일치: 오류 메시지 표시 후 이전 페이지로 돌아감
                echo "<script>alert('비밀번호가 틀렸습니다.'); history.back();</script>";
                exit;
            }
        } else {
            // 관리자 계정 없음 또는 이메일 오류: 오류 메시지 표시 후 이전 페이지로 돌아감
            echo "<script>alert('관리자 계정이 없거나 이메일이 잘못되었습니다.'); history.back();</script>";
            exit;
        }
    } else {
        // 입력 부족: 이메일과 비밀번호를 모두 입력하도록 오류 메시지 표시 후 이전 페이지로 돌아감
        echo "<script>alert('이메일과 비밀번호를 입력해주세요.'); history.back();</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인</title>
    <link rel="stylesheet" href="/assets/css/admin_style.css">
</head>
<body>
    <div style="max-width:300px; margin:50px auto; background:#fff; padding:20px; border:1px solid #ccc;">
        <!-- 관리자 로그인 페이지 제목 -->
        <h2>관리자 로그인</h2>

        <!-- 관리자 로그인 폼 -->
        <form method="post">
            <!-- 이메일 입력 필드 -->
            <p>이메일: <input type="email" name="email" style="width:100%;" required></p>
            
            <!-- 비밀번호 입력 필드 -->
            <p>비밀번호: <input type="password" name="password" style="width:100%;" required></p>
            
            <!-- 제출 버튼 -->
            <p><input type="submit" value="로그인" class="btn"></p>
        </form>
    </div>
</body>
</html>
