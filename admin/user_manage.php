<?php
// admin/user_manage.php: 관리자 회원 관리 페이지
// 이 페이지에서는 관리자 권한을 가진 사용자가 회원 목록을 보고, 특정 회원을 관리자 부여/해제하거나 회원 계정을 삭제할 수 있다.

require_once __DIR__ . "/../config/db.php";       // DB 접속 설정
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수 (isAdmin, h 등)

// 관리자 공용 헤더 포함(상단 메뉴 표시)
include_once __DIR__ . "/admin_header.php";

// 관리자 권한 부여 처리
if (isset($_GET['make_admin'])) {
    // make_admin 파라미터로 유저 ID를 받는다
    $uid = (int)$_GET['make_admin'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->execute([$uid]);
    echo "<script>alert('관리자 권한 부여');location.href='user_manage.php';</script>";
    exit;
}

// 관리자 권한 해제 처리
if (isset($_GET['remove_admin'])) {
    // remove_admin 파라미터로 유저 ID를 받는다
    $uid = (int)$_GET['remove_admin'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
    $stmt->execute([$uid]);
    echo "<script>alert('관리자 권한 해제');location.href='user_manage.php';</script>";
    exit;
}

// 사용자 삭제 처리
if (isset($_GET['delete_id'])) {
    // delete_id 파라미터로 삭제할 유저 ID 받기
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id > 0) {
        // 해당 유저가 존재하는지 확인
        $check_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $check_stmt->execute([$delete_id]);
        $user = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 존재하는 유저면 삭제
            $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            echo "<script>alert('사용자 계정이 삭제되었습니다.');location.href='user_manage.php';</script>";
            exit;
        } else {
            // 해당 ID의 유저가 없으면 메시지 출력 후 리스트 페이지로
            echo "<script>alert('존재하지 않는 사용자입니다.');location.href='user_manage.php';</script>";
            exit;
        }
    } else {
        // delete_id 파라미터가 유효하지 않은 경우
        echo "<script>alert('잘못된 접근');location.href='user_manage.php';</script>";
        exit;
    }
}

// 회원 목록 조회
$stmt = $pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>회원 관리</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>이름</th><th>이메일</th><th>권한</th><th>관리</th>
    </tr>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?php echo h($u['id']); ?></td>
        <td><?php echo h($u['username']); ?></td>
        <td><?php echo h($u['email']); ?></td>
        <td><?php echo $u['is_admin']?'관리자':'일반'; ?></td>
        <td>
            <?php if($u['is_admin']): ?>
                <!-- 현재 관리자인 경우 관리자 해제 링크 표시 -->
                <a href="?remove_admin=<?php echo $u['id']; ?>">관리자해제</a>
            <?php else: ?>
                <!-- 일반 유저인 경우 관리자 부여 링크 표시 -->
                <a href="?make_admin=<?php echo $u['id']; ?>">관리자부여</a>
            <?php endif; ?>
            |
            <!-- 사용자 삭제 링크 -->
            <a href="?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include_once __DIR__ . "/../includes/footer.php"; ?>
