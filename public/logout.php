<?php
// public/logout.php: 로그아웃 처리
require_once __DIR__ . "/../includes/session.php";

// 세션 종료
session_unset();
session_destroy();

// 메인 페이지로 리다이렉트
header("Location: index.php");
exit;
