<?php
// functions.php: 공용 함수들

// HTML 특수문자 이스케이프 함수 (XSS 예방)
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 로그인 여부 확인 함수: 세션에 user_id가 있으면 로그인 상태
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 관리자 여부 확인 함수: 세션에 is_admin이 1이면 관리자
function isAdmin() {
    return (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
}
