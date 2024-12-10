<?php
// db.php: 데이터베이스 연결 설정
$host = "localhost";     // DB 서버 호스트
$dbname = "shopping_db"; // 사용 DB명
$user = "root";       // DB 사용자명
$pass = "1234";   // DB 비밀번호

try {
    // PDO 객체 생성 및 UTF-8 인코딩 설정
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 예외 모드 설정
} catch (PDOException $e) {
    // 접속 실패 시 에러 메시지 출력 후 종료
    echo "DB 연결 실패: " . $e->getMessage();
    exit;
}
