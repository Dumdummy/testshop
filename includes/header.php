<?php
// header.php: 공용 헤더
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>쇼핑몰 예제</title>
<link rel="stylesheet" href="/assets/css/style.css">
<script src="/assets/js/script.js"></script>
<style>
    /* 헤더 오른쪽 상단에 사용자명 표시를 위한 간단한 스타일 */
    .welcome-user {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #fff;
        font-weight: bold;
    }
</style>
</head>
<body>
<header style="position: relative;">
    <h1><a href="/public/index.php">쇼핑몰 예제</a></h1>
    <?php if(isLoggedIn()): ?>
        <!-- 로그인 상태일 때 우상단 환영 메시지 표시 -->
        <div class="welcome-user"><?php echo h($_SESSION['username']); ?>님 환영합니다.</div>
    <?php endif; ?>
    <nav>
        <ul>
            <li><a href="/public/index.php">상품목록</a></li>
            <li><a href="/public/inquiry_list.php">문의사항 게시판</a></li>
            <li><a href="/public/cart.php">장바구니</a></li>
            <?php if(isLoggedIn()): ?>
                <li><a href="/public/my_orders.php">주문내역</a></li>
                <li><a href="/public/logout.php">로그아웃</a></li>
            <?php else: ?>
                <li><a href="/public/signup.php">회원가입</a></li>
                <li><a href="/public/login.php">로그인</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
