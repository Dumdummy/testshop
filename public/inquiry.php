<?php
// public/inquiry.php: 문의하기 페이지, 로그인 계정의 이름과 이메일 불러오기, 수정 불가하게 설정

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// 로그인 여부 확인
if (!isLoggedIn()) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// 세션에서 username과 email 가져오기
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';

// 이름과 이메일이 없으면 DB에서 가져오는 로직 필요 (현재는 세션에 저장되어 있다고 가정)
?>
<!-- 문의하기 페이지 제목 -->
<h2>문의하기</h2>

<!-- 문의하기 폼 시작 -->
<form action="inquiry_process.php" method="post" enctype="multipart/form-data">
    <!-- 이름 입력 필드 (수정 불가) -->
    <p>이름: <input type="text" name="name" value="<?php echo h($username); ?>" readonly></p>
    
    <!-- 이메일 입력 필드 -->
    <p>이메일: <input type="text" name="email" value="<?php echo h($email); ?>"></p>
    
    <!-- 제목 입력 필드 -->
    <p>제목: <input type="text" name="subject" required></p>
    
    <!-- 공개 여부 선택 필드 -->
    <p>공개 여부:
       <label><input type="radio" name="public" value="1" checked>공개</label>
       <label><input type="radio" name="public" value="0">비공개</label>
    </p>
    
    <!-- 문의내용 입력 필드 -->
    <p>문의내용:<br><textarea name="message" rows="5" cols="50"></textarea></p>
    
    <!-- 이미지 첨부 입력 필드 -->
    <p>이미지 첨부: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
    
    <!-- 제출 버튼 -->
    <p><input type="submit" value="등록"></p>
</form>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
