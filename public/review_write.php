<?php
// public/review_write.php: 리뷰 작성 폼 처리

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/functions.php";

$product_id=(int)($_POST['product_id']??0);
$author=trim($_POST['author']??'');
$content=trim($_POST['content']??'');
$rating=(int)($_POST['rating']??5);

// 기본 검증
if($product_id<=0 || $author===''|| $content==='' || $rating<1 || $rating>5) {
    echo "<script>alert('입력값 확인 필요');history.back();</script>";
    exit;
}

// 리뷰 DB 삽입
$stmt=$pdo->prepare("INSERT INTO reviews (product_id,author,content,rating,created_at) VALUES(?,?,?,?,NOW())");
$stmt->execute([$product_id,$author,$content,$rating]);

// 작성 후 상세페이지로 이동
header("Location: product_detail.php?id=".$product_id);
exit;
