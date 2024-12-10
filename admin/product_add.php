<?php
// admin/product_add.php: 관리자 상품 추가 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 관리자 전용 헤더 파일 포함
include_once __DIR__ . "/admin_header.php";

// 카테고리 목록 조회
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// 상품 등록 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력값 받아오기
    $name = trim($_POST['name'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // 필수 입력값 검증: 상품명과 가격
    if ($name === '' || $price <= 0) {
        echo "<script>alert('상품명, 가격을 입력해주세요.'); history.back();</script>";
        exit;
    }

    // 이미지 업로드 처리
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // 허용된 이미지 확장자 목록
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // 업로드된 파일의 임시 경로와 원본 이름 가져오기
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        
        // 파일 확장자 추출 및 소문자로 변환
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 확장자 검증
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
            // 웹에서 접근 가능한 경로로 설정
            $image_path = "uploads/" . $new_file_name;
        } else {
            echo "<script>alert('이미지 업로드 실패'); history.back();</script>";
            exit;
        }
    }

    // 상품 정보 데이터베이스에 삽입
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $price, $description, $category_id, $image_path]);

    // 상품 등록 완료 후 상품 목록 페이지로 이동
    echo "<script>alert('상품 등록 완료'); location.href='product_list.php';</script>";
    exit;
}
?>
<!-- 상품 등록 페이지 제목 -->
<h2>상품 등록</h2>

<!-- 상품 등록 폼 시작 -->
<form method="post" enctype="multipart/form-data" style="max-width:400px;">
    <!-- 상품명 입력 필드 -->
    <p>상품명: <input type="text" name="name" style="width:100%;" required></p>
    
    <!-- 가격 입력 필드 -->
    <p>가격: <input type="text" name="price" style="width:100%;" required></p>
    
    <!-- 카테고리 선택 필드 -->
    <p>카테고리: 
        <select name="category_id" style="width:100%;" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    
    <!-- 설명 입력 필드 -->
    <p>설명:<br><textarea name="description" rows="5" style="width:100%;"></textarea></p>
    
    <!-- 이미지 첨부 입력 필드 -->
    <p>이미지: <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"></p>
    
    <!-- 제출 버튼 -->
    <p><input type="submit" value="등록"></p>
</form>
<!-- 상품 등록 폼 종료 -->

<?php 
// 푸터 파일 포함
include_once __DIR__ . "/../includes/footer.php"; 
?>
