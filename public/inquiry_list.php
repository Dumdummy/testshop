<?php
// public/inquiry_list.php: 문의사항 게시판 페이지

// 데이터베이스 설정 파일 로드
require_once __DIR__ . "/../config/db.php";

// 공용 함수들 로드
require_once __DIR__ . "/../includes/functions.php";

// 세션 관리 파일 로드
require_once __DIR__ . "/../includes/session.php";

// 헤더 파일 포함 (HTML 헤더 및 네비게이션 바 등)
include_once __DIR__ . "/../includes/header.php";

// 검색 유형과 검색어 가져오기
$search_type = $_GET['search_type'] ?? '';
$search_query = trim($_GET['search_query'] ?? '');

// 기본 SQL 쿼리 설정
$sql = "SELECT * FROM inquiries WHERE 1=1";
$params = [];

// 검색어가 있는 경우 검색 조건 추가
if ($search_query !== '') {
    switch ($search_type) {
        case 'subject':
            $sql .= " AND subject LIKE ?";
            $params[] = "%$search_query%";
            break;
        case 'subject_content':
            $sql .= " AND (subject LIKE ? OR message LIKE ?)";
            $params[] = "%$search_query%";
            $params[] = "%$search_query%";
            break;
        case 'author':
            $sql .= " AND name LIKE ?";
            $params[] = "%$search_query%";
            break;
        default:
            $sql .= " AND subject LIKE ?";
            $params[] = "%$search_query%";
            break;
    }
}

// 최종 SQL 쿼리 설정 (ID 내림차순 정렬)
$sql .= " ORDER BY id DESC";

// 준비된 문장 실행
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// 문의사항 목록 가져오기
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- 문의사항 게시판 제목 -->
<h2>문의사항 게시판</h2>

<!-- 검색 폼 -->
<form method="get" style="margin-bottom:20px;">
    <!-- 검색 유형 선택 -->
    <select name="search_type">
        <option value="subject" <?php if($search_type=='subject') echo 'selected'; ?>>제목</option>
        <option value="subject_content" <?php if($search_type=='subject_content') echo 'selected'; ?>>제목+내용</option>
        <option value="author" <?php if($search_type=='author') echo 'selected'; ?>>작성자</option>
    </select>
    <!-- 검색어 입력 -->
    <input type="text" name="search_query" value="<?php echo h($search_query); ?>">
    <!-- 검색 버튼 -->
    <input type="submit" value="검색">
</form>

<!-- 로그인한 사용자만 글쓰기 버튼 표시 -->
<?php if (isLoggedIn()): ?>
    <p><a href="inquiry.php" style="background:#333; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px;">글쓰기</a></p>
<?php endif; ?>

<!-- 문의사항 목록 테이블 -->
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>제목</th>
        <th>작성자</th>
        <th>작성일</th>
    </tr>
    <?php foreach ($inquiries as $inq): ?>
    <tr>
        <!-- 문의 ID 표시 -->
        <td><?php echo h($inq['id']); ?></td>
        <td>
            <?php if ($inq['public'] == 1): ?>
                <!-- 공개글: 제목 클릭 시 상세페이지로 이동 -->
                <a href="inquiry_detail.php?id=<?php echo h($inq['id']); ?>"><?php echo h($inq['subject']); ?></a>
            <?php else: ?>
                <?php if (isAdmin() || $_SESSION['username'] === $inq['name']): ?>
                    <!-- 관리자 및 글 작성자: 비공개 글도 클릭 가능 -->
                    <a href="inquiry_detail.php?id=<?php echo h($inq['id']); ?>"><?php echo h($inq['subject']); ?> (비공개)</a>
                <?php else: ?>
                    <!-- 일반 사용자: 비공개 글 접근 불가 -->
                    비공개된 문의입니다.
                <?php endif; ?>
            <?php endif; ?>
        </td>
        <td>
            <?php 
            if ($inq['public'] == 1 || isAdmin() || $_SESSION['username'] === $inq['name']) {
                // 공개글이거나 관리자, 글 작성자일 경우 작성자 표시
                echo h($inq['name']);
            } else {
                // 비공개 글인 경우 작성자 미표시
                echo "-";
            }
            ?>
        </td>
        <!-- 작성일 표시 -->
        <td><?php echo h($inq['created_at']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php 
// 푸터 파일 포함 (HTML 푸터 등)
include_once __DIR__ . "/../includes/footer.php"; 
?>
