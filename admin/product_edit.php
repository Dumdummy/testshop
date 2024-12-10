<?php
// admin/product_edit.php: 관리자 상품 수정 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함 (관리자 네비게이션 바 등)
include_once __DIR__ . "/admin_header.php";

// GET 파라미터로 상품 ID 받기
$product_id = (int)($_GET['id'] ?? 0);

// 유효한 상품 ID인지 확인
if ($product_id <= 0) {
    // 잘못된 접근 시 경고 메시지 후 상품 목록 페이지로 이동
    echo "<script>alert('잘못된 접근입니다.'); location.href='product_list.php';</script>";
    exit;
}

// 상품 정보 조회
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// 상품이 존재하지 않을 경우 경고 메시지 후 상품 목록 페이지로 이동
if (!$product) {
    echo "<script>alert('존재하지 않는 상품입니다.'); location.href='product_list.php';</script>";
    exit;
}

// 카테고리 목록 조회 (이름 순으로 정렬)
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// POST 요청 시 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력값을 받아와서 앞뒤 공백 제거
    $name = trim($_POST['name'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // 필수 입력값 검증: 상품명과 가격이 올바르게 입력되었는지 확인
    if ($name === '' || $price <= 0) {
        echo "<script>alert('상품명, 가격을 정확히 입력해주세요.'); history.back();</script>";
        exit;
    }

    // 이미지 업로드 처리 (선택사항: 이미지 변경 시)
    $image_path = $product['image_path']; // 기존 이미지 경로 유지
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // 허용된 이미지 확장자 목록
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // 업로드된 파일의 임시 경로와 원본 이름 가져오기
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        
        // 파일 확장자 추출 및 소문자로 변환
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 허용되지 않은 확장자일 경우 경고 메시지 표시 후 이전 페이지로 돌아감
        if (!in_array($ext, $allowed_extensions)) {
            echo "<script>alert('이미지 형식 오류'); history.back();</script>";
            exit;
        }

        // 이미지 업로드 디렉토리 설정
        $upload_dir = __DIR__ . "/../uploads/";
        
        // 업로드 디렉토리가 존재하지 않으면 생성
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // 고유한 파일 이름 생성 (타임스탬프와 고유 ID 사용)
        $new_file_name = time() . '_' . uniqid() . '.' . $ext;
        $target_path = $upload_dir . $new_file_name;

        // 파일을 업로드 디렉토리로 이동
        if (move_uploaded_file($file_tmp, $target_path)) {
            // 이미지 경로 업데이트 (웹에서 접근 가능한 경로로 설정)
            $image_path = "uploads/" . $new_file_name;
        } else {
            // 이미지 업로드 실패 시 경고 메시지 표시 후 이전 페이지로 돌아감
            echo "<script>alert('이미지 업로드 실패'); history.back();</script>";
            exit;
        }
    }

    // 상품 정보 업데이트를 위한 준비된 문장(Prepared Statement) 작성
    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = ?, image_path = ? WHERE id = ?");
    
    // 업데이트 실행 (사용자 입력값과 이미지 경로)
    $stmt->execute([$name, $price, $description, $category_id, $image_path, $product_id]);

    // 업데이트 성공 시 경고 메시지 표시 후 상품 목록 페이지로 이동
    echo "<script>alert('상품 정보가 수정되었습니다.'); location.href='product_list.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>상품 수정</title>
    <link rel="stylesheet" href="/assets/css/admin_style.css">
</head>
<body>
    <div style="max-width:400px; margin:50px auto; background:#fff; padding:20px; border:1px solid #ccc;">
        <!-- 상품 수정 페이지 제목 -->
        <h2>상품 수정</h2>
        
        <!-- 상품 수정 폼 시작 -->
        <form method="post" enctype="multipart/form-data">
            <!-- 상품명 입력 필드 -->
            <p>상품명: <input type="text" name="name" style="width:100%;" value="<?php echo htmlspecialchars($product['name']); ?>" required></p>
            
            <!-- 가격 입력 필드 -->
            <p>가격: <input type="text" name="price" style="width:100%;" value="<?php echo htmlspecialchars($product['price']); ?>" required></p>
            
            <!-- 카테고리 선택 필드 -->
            <p>카테고리: 
                <select name="category_id" style="width:100%;" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $product['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <!-- 설명 입력 필드 -->
            <p>설명:<br><textarea name="description" rows="5" style="width:100%;"><?php echo htmlspecialchars($product['description']); ?></textarea></p>
            
            <!-- 현재 이미지 표시 (존재할 경우) -->
            <?php if ($product['image_path']): ?>
                <p>현재 이미지:<br><img src="/<?php echo htmlspecialchars($product['image_path']); ?>" alt="상품이미지" style="max-width:100px;"></p>
            <?php endif; ?>
            
            <!-- 이미지 변경 입력 필드 -->
            <p>이미지 변경: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
            
            <!-- 제출 버튼 -->
            <p><input type="submit" value="수정" class="btn"></p>
        </form>
    </div>
</body>
</html>
<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
