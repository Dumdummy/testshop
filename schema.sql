-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS shopping_db 
    DEFAULT CHARACTER SET utf8mb4 
    COLLATE utf8mb4_general_ci;
USE shopping_db;

-- users: 사용자 정보 (username, email, password, phone, is_admin)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- categories: 카테고리 관리
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- products: 상품 정보 (category_id 참조, image_path로 이미지 경로 저장)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  description TEXT,
  category_id INT,
  image_path VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- reviews: 상품 리뷰 (product_id 참조, rating 별점)
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  author VARCHAR(100),
  content TEXT,
  rating INT DEFAULT 5,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- inquiries: 문의사항 (user_id로 작성자, public 공개/비공개, image_path 첨부이미지, answer 관리자 답변)
DROP TABLE IF EXISTS inquiries;

CREATE TABLE IF NOT EXISTS inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  public TINYINT(1) DEFAULT 1,
  image_path VARCHAR(255) DEFAULT NULL,
  answer TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- orders: 주문 정보 (user_id 참조)
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  receiver_name VARCHAR(100),
  receiver_address VARCHAR(255),
  receiver_phone VARCHAR(50),
  total_price INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- order_items: 주문 상세 (order_id, product_id 참조)
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 샘플 데이터
INSERT INTO categories (name) VALUES ('전자제품'), ('의류'), ('식품'), ('도서');

-- 관리자 계정 예시 (비밀번호 해시 필요)
-- password_hash('admin123', PASSWORD_BCRYPT) 결과를 'admin_hashed_value' 대신 넣는것이 보안상 좋음.
INSERT INTO users (username, email, password, phone, is_admin) VALUES ('admin', 'admin@admin.com', '1234', '010-0000-0000', 1);

-- 샘플 상품
INSERT INTO products (name, price, description, category_id) VALUES
('노트북', 1000000, '고성능 노트북', 1),
('청바지', 50000, '편한 청바지', 2),
('사과', 3000, '신선한 사과', 3),
('소설책', 15000, '베스트셀러 소설', 4);
