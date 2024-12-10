<?php
// admin/index.php: 관리자 메인 페이지

// 관리자 전용 헤더 파일 포함 (관리자 네비게이션 바 등)
include_once __DIR__ . "/admin_header.php";
?>

<!-- 관리자 메인 페이지 제목 -->
<h2>관리자 메인</h2>

<!-- 관리자 전용 대시보드 설명 -->
<p>관리자 전용 대시보드 페이지입니다.</p>
<p>상단 메뉴를 통해 상품관리, 회원관리, 문의관리, 매출통계 페이지로 이동할 수 있습니다.</p>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
