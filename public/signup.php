<?php
// signup.php: 사용자 회원가입 처리 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// POST 요청 처리
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 입력값을 받아와서 앞뒤 공백을 제거
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // 간단한 유효성 검사: 모든 필드가 입력되었는지 확인
    if($username == '' || $email == '' || $password == '' || $password_confirm == '' || $phone == '') {
        echo "<script>alert('모든 필드를 입력해주세요.');</script>";
    } 
    // 비밀번호와 비밀번호 확인이 일치하는지 확인
    elseif($password !== $password_confirm) {
        echo "<script>alert('비밀번호 확인 불일치');</script>";
    } 
    else {
        // 이메일 중복 체크: 동일한 이메일을 사용하는 사용자가 있는지 확인
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetchColumn() > 0) {
            echo "<script>alert('이미 사용중인 이메일입니다.');</script>";
        } 
        else {
            // 비밀번호를 BCRYPT 알고리즘으로 해시 처리
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            
            // 사용자 정보를 데이터베이스에 삽입
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone, is_admin, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt->execute([$username, $email, $hashed, $phone]);

            // 회원가입 완료 메시지 표시 후 로그인 페이지로 리다이렉트
            echo "<script>alert('회원가입 완료');location.href='login.php';</script>";
            exit;
        }
    }
}
?>

<!-- 회원가입 페이지 제목 -->
<h2>회원가입</h2>

<!-- 회원가입 폼 -->
<form method="post">
    <!-- 사용자 이름 입력 필드 -->
    <p>이름: <input type="text" name="username" required></p>
    
    <!-- 이메일 입력 필드 -->
    <p>이메일: <input type="email" name="email" required></p>
    
    <!-- 연락처 입력 필드 -->
    <p>연락처: <input type="text" name="phone" placeholder="010-XXXX-XXXX 또는 XXX-XXX-XXXX 등" required></p>
    
    <!-- 비밀번호 입력 필드 -->
    <p>비밀번호: <input type="password" name="password" required></p>
    
    <!-- 비밀번호 확인 입력 필드 -->
    <p>비밀번호 확인: <input type="password" name="password_confirm" required></p>
    
    <!-- 제출 버튼 -->
    <p><input type="submit" value="회원가입"></p>
</form>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
