<?php
// admin_header.php: 관리자 공용 헤더
require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/functions.php";

// 관리자 권한 체크
if (!isAdmin()) {
    echo "<script>alert('관리자 권한 필요');location.href='login.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>관리자 페이지</title>
<link rel="stylesheet" href="/assets/css/admin_style.css">
</head>
<body>
<header class="admin-header">
    <h1><a href="index.php">관리자 페이지</a></h1>
    <nav>
        <ul class="admin-menu">
            <li><a href="product_list.php">상품관리</a></li>
            <li><a href="category_manage.php">카테고리관리</a></li>
            <li><a href="user_manage.php">회원관리</a></li>
            <li><a href="inquiry_manage.php">문의관리</a></li>
            <li><a href="statistics.php">매출통계</a></li>
            <li><a href="login.php">로그아웃</a></li>
        </ul>
    </nav>
</header>
<main class="admin-main">
