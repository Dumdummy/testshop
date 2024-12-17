<?php
// public/inquiry_edit.php: 문의 수정 페이지
// 이 페이지에서는 로그인한 사용자 중에서 관리자나 해당 문의의 작성자가 문의 내용을 수정할 수 있다.

require_once __DIR__ . "/../config/db.php";       // DB 연결
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수
include_once __DIR__ . "/../includes/header.php"; // 공용 헤더

if(!isLoggedIn()) {
    // 비로그인 상태면 접근 불가
    echo "<script>alert('로그인이 필요합니다.');location.href='login.php';</script>";
    exit;
}

$inquiry_id = (int)($_GET['id'] ?? 0);
if($inquiry_id <= 0) {
    echo "<script>alert('잘못된 접근');location.href='inquiry_list.php';</script>";
    exit;
}

// 문의 조회
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->execute([$inquiry_id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$inquiry) {
    echo "<script>alert('존재하지 않는 문의입니다.');location.href='inquiry_list.php';</script>";
    exit;
}

// 관리자이거나 본인 글이면 수정 가능
if(!isAdmin() && $inquiry['user_id'] !== $_SESSION['user_id']) {
    echo "<script>alert('본인 작성 글이 아니며, 관리자도 아닙니다. 수정 불가');location.href='inquiry_list.php';</script>";
    exit;
}

// POST 요청 시 수정 처리
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $public = isset($_POST['public']) ? (int)$_POST['public'] : 1;
    $message = trim($_POST['message'] ?? '');

    if($subject===''||$message==='') {
        echo "<script>alert('제목과 내용을 입력해주세요.');history.back();</script>";
        exit;
    }

    // 이미지 변경 여부
    $image_path = $inquiry['image_path'];
    if(isset($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK) {
        $allowed_extensions=['jpg','jpeg','png','gif'];
        $file_tmp=$_FILES['image']['tmp_name'];
        $file_name=basename($_FILES['image']['name']);
        $ext=strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
        if(!in_array($ext,$allowed_extensions)) {
            echo "<script>alert('이미지 형식 오류');history.back();</script>";
            exit;
        }

        $upload_dir=__DIR__."/../uploads/";
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir,0755,true);
        }

        $new_file_name=time().'_'.uniqid().'.'.$ext;
        $target_path=$upload_dir.$new_file_name;

        if(move_uploaded_file($file_tmp,$target_path)) {
            $image_path="uploads/".$new_file_name;
        } else {
            echo "<script>alert('이미지 업로드 실패');history.back();</script>";
            exit;
        }
    }

    // DB 업데이트
    $upd_stmt = $pdo->prepare("UPDATE inquiries SET subject=?, message=?, public=?, image_path=? WHERE id=?");
    $upd_stmt->execute([$subject, $message, $public, $image_path, $inquiry_id]);

    echo "<script>alert('문의가 수정되었습니다.');location.href='inquiry_detail.php?id=$inquiry_id';</script>";
    exit;
}
?>

<h2>문의 수정</h2>
<form method="post" enctype="multipart/form-data">
    <p>제목: <input type="text" name="subject" value="<?php echo h($inquiry['subject']); ?>" style="width:100%;"></p>
    <p>공개 여부:
       <label><input type="radio" name="public" value="1" <?php if($inquiry['public']==1) echo 'checked'; ?>>공개</label>
       <label><input type="radio" name="public" value="0" <?php if($inquiry['public']==0) echo 'checked'; ?>>비공개</label>
    </p>
    <p>내용:<br><textarea name="message" rows="5" style="width:100%;"><?php echo h($inquiry['message']); ?></textarea></p>
    <?php if($inquiry['image_path']): ?>
        <p>현재 이미지:<br><img src="/<?php echo h($inquiry['image_path']); ?>" style="max-width:200px;"></p>
    <?php endif; ?>
    <p>이미지 변경: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
    <p><input type="submit" value="수정하기"></p>
</form>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
