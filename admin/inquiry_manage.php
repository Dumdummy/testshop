<?php
// admin/inquiry_manage.php: 관리자 문의 관리 페이지
// 이 페이지에서는 관리자 권한을 가진 사용자가 모든 문의 목록을 보고,
// 문의에 답변하거나 문의를 수정/삭제할 수 있다.

require_once __DIR__ . "/../config/db.php";       // DB 연결 설정
require_once __DIR__ . "/../includes/session.php"; // 세션 시작
require_once __DIR__ . "/../includes/functions.php"; // 공용 함수

// 관리자 공용 헤더 포함(상단 메뉴 표시)
include_once __DIR__ . "/admin_header.php";

// 문의 삭제 처리
if(isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if($delete_id > 0) {
        // 해당 문의 존재 확인
        $check_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
        $check_stmt->execute([$delete_id]);
        $inq = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if($inq) {
            // 문의 삭제
            $del_stmt = $pdo->prepare("DELETE FROM inquiries WHERE id=?");
            $del_stmt->execute([$delete_id]);
            echo "<script>alert('문의가 삭제되었습니다.');location.href='inquiry_manage.php';</script>";
            exit;
        } else {
            echo "<script>alert('존재하지 않는 문의입니다.');location.href='inquiry_manage.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('잘못된 접근');location.href='inquiry_manage.php';</script>";
        exit;
    }
}

// 답변 등록/수정 처리
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer_id'])) {
    // answer_id 파라미터가 있으면 관리자 답변 등록/수정
    $answer_id = (int)$_POST['answer_id'];
    $answer = trim($_POST['answer'] ?? '');

    $check_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
    $check_stmt->execute([$answer_id]);
    $inq = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if(!$inq) {
        echo "<script>alert('존재하지 않는 문의입니다.');location.href='inquiry_manage.php';</script>";
        exit;
    }

    $upd_stmt = $pdo->prepare("UPDATE inquiries SET answer=? WHERE id=?");
    $upd_stmt->execute([$answer, $answer_id]);

    echo "<script>alert('답변이 등록/수정되었습니다.');location.href='inquiry_manage.php';</script>";
    exit;
}

// 문의 수정 처리
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    // edit_id 파라미터가 있으면 문의 수정 처리
    $edit_id = (int)$_POST['edit_id'];
    $check_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
    $check_stmt->execute([$edit_id]);
    $inq = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if(!$inq) {
        echo "<script>alert('존재하지 않는 문의입니다.');location.href='inquiry_manage.php';</script>";
        exit;
    }

    // POST로 수정할 필드 받기
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $public = isset($_POST['public']) ? (int)$_POST['public'] : 1;

    if($subject===''||$message==='') {
        echo "<script>alert('제목과 내용을 입력해주세요.');history.back();</script>";
        exit;
    }

    // 이미지 변경 처리
    $image_path = $inq['image_path'];
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
    $upd_inq_stmt = $pdo->prepare("UPDATE inquiries SET subject=?, message=?, public=?, image_path=? WHERE id=?");
    $upd_inq_stmt->execute([$subject, $message, $public, $image_path, $edit_id]);

    echo "<script>alert('문의가 수정되었습니다.');location.href='inquiry_manage.php';</script>";
    exit;
}

// 답변 폼 표시용
$answer_mode = false;
$answer_inquiry = null;
if(isset($_GET['answer_id'])) {
    // answer_id 파라미터로 특정 문의에 대해 답변 폼 표시
    $answer_id = (int)$_GET['answer_id'];
    if($answer_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
        $stmt->execute([$answer_id]);
        $answer_inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        if($answer_inquiry) {
            $answer_mode = true;
        }
    }
}

// 수정 폼 표시용
$edit_mode = false;
$edit_inquiry = null;
if(isset($_GET['edit_id'])) {
    // edit_id 파라미터로 특정 문의에 대해 수정 폼 표시
    $edit_id = (int)$_GET['edit_id'];
    if($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id=?");
        $stmt->execute([$edit_id]);
        $edit_inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        if($edit_inquiry) {
            $edit_mode = true;
        }
    }
}

// 전체 문의 조회
$stmt=$pdo->query("SELECT * FROM inquiries ORDER BY id DESC");
$inquiries=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>문의 관리</h2>

<?php if($answer_mode && $answer_inquiry): ?>
    <!-- 답변 작성 폼 -->
    <h3>답변 작성</h3>
    <form method="post" style="margin-bottom:20px;">
        <input type="hidden" name="answer_id" value="<?php echo $answer_inquiry['id']; ?>">
        <p>제목: <?php echo htmlspecialchars($answer_inquiry['subject']); ?></p>
        <p>작성자: <?php echo htmlspecialchars($answer_inquiry['name']); ?> (<?php echo htmlspecialchars($answer_inquiry['email']); ?>)</p>
        <p>내용:<br><?php echo nl2br(htmlspecialchars($answer_inquiry['message'])); ?></p>
        <p>답변:<br><textarea name="answer" rows="5" cols="50" style="width:100%;"><?php echo htmlspecialchars($answer_inquiry['answer']??''); ?></textarea></p>
        <p><input type="submit" value="등록/수정"></p>
    </form>
<?php endif; ?>

<?php if($edit_mode && $edit_inquiry): ?>
    <!-- 문의 수정 폼 -->
    <h3>문의 수정</h3>
    <form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">
        <input type="hidden" name="edit_id" value="<?php echo $edit_inquiry['id']; ?>">
        <p>제목: <input type="text" name="subject" value="<?php echo htmlspecialchars($edit_inquiry['subject']); ?>" style="width:100%;"></p>
        <p>공개 여부:
           <label><input type="radio" name="public" value="1" <?php if($edit_inquiry['public']==1) echo 'checked'; ?>>공개</label>
           <label><input type="radio" name="public" value="0" <?php if($edit_inquiry['public']==0) echo 'checked'; ?>>비공개</label>
        </p>
        <p>내용:<br><textarea name="message" rows="5" style="width:100%;"><?php echo htmlspecialchars($edit_inquiry['message']); ?></textarea></p>
        <?php if($edit_inquiry['image_path']): ?>
            <p>현재 이미지:<br><img src="/<?php echo htmlspecialchars($edit_inquiry['image_path']); ?>" style="max-width:200px;"></p>
        <?php endif; ?>
        <p>이미지 변경: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
        <p><input type="submit" value="수정하기"></p>
    </form>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <tr><th>ID</th><th>제목</th><th>작성자</th><th>이메일</th><th>공개여부</th><th>작성일</th><th>답변상태</th><th>관리</th></tr>
    <?php foreach($inquiries as $inq): ?>
    <tr>
        <td><?php echo htmlspecialchars($inq['id']); ?></td>
        <td><?php echo htmlspecialchars($inq['subject']); ?></td>
        <td><?php echo htmlspecialchars($inq['name']); ?></td>
        <td><?php echo htmlspecialchars($inq['email']); ?></td>
        <td><?php echo $inq['public']?'공개':'비공개'; ?></td>
        <td><?php echo htmlspecialchars($inq['created_at']); ?></td>
        <td><?php echo ($inq['answer'] && trim($inq['answer'])!=='') ? '답변완료' : '미답변'; ?></td>
        <td>
            <a href="?answer_id=<?php echo $inq['id']; ?>">답변하기</a> | 
            <a href="?edit_id=<?php echo $inq['id']; ?>">수정하기</a> | 
            <a href="?delete_id=<?php echo $inq['id']; ?>" onclick="return confirm('삭제하시겠습니까?');">삭제</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include_once __DIR__ . "/../includes/footer.php"; ?>
