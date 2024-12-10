<?php
// public/index.php: 메인 페이지(상품 목록)
// 검색, 카테고리 필터, 정렬 기능 포함

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/header.php";

// GET 파라미터로 검색/필터/정렬 값 수신
$search = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);
$sort = $_GET['sort'] ?? '';

// 기본 SQL
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params=[];

// 검색 조건 추가
if($search !== '') {
    $sql .= " AND p.name LIKE ?";
    $params[]="%$search%";
}

// 카테고리 필터
if($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[]=$category_id;
}

// 정렬 조건
switch($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    default:
        $sql .= " ORDER BY p.id DESC";
}

// 쿼리 실행
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 카테고리 목록 가져오기 (검색용)
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>상품 목록</h2>
<!-- 검색 폼 -->
<form method="get" style="margin-bottom:20px;">
    <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="상품명 검색">
    <select name="category_id">
        <option value="0">전체 카테고리</option>
        <?php foreach($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>" <?php if($cat['id']==$category_id) echo 'selected'; ?>><?php echo h($cat['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <select name="sort">
        <option value="">정렬 없음</option>
        <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>>가격 낮은순</option>
        <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>>가격 높은순</option>
        <option value="name_asc" <?php if($sort=='name_asc') echo 'selected'; ?>>이름순</option>
        <option value="name_desc" <?php if($sort=='name_desc') echo 'selected'; ?>>이름역순</option>
    </select>
    <input type="submit" value="검색">
</form>

<ul>
<?php foreach($products as $product): ?>
    <li>
        [<?php echo h($product['category_name']); ?>]
        <a href="product_detail.php?id=<?php echo h($product['id']); ?>">
            <?php echo h($product['name']); ?>
        </a> - <?php echo h($product['price']); ?>원
    </li>
<?php endforeach; ?>
</ul>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
