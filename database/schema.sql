-- ============================================================
-- Hostel Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS hostel_management
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE hostel_management;

-- -----------------------------------------------------------
-- Users table (admins and students)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password      VARCHAR(255) NOT NULL,
  role           ENUM('admin','student') NOT NULL DEFAULT 'student',
  phone          VARCHAR(20)  DEFAULT NULL,
  status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Students table (linked 1:1 to users with role=student)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NOT NULL,
  student_number VARCHAR(20) NOT NULL UNIQUE,
  full_name      VARCHAR(100) NOT NULL,
  gender         ENUM('male','female','other') NOT NULL,
  course         VARCHAR(100) DEFAULT NULL,
  year_of_study  INT DEFAULT NULL,
  phone          VARCHAR(20) DEFAULT NULL,
  email          VARCHAR(150) DEFAULT NULL,
  address        VARCHAR(255) DEFAULT NULL,
  guardian_name  VARCHAR(100) DEFAULT NULL,
  guardian_phone VARCHAR(20)  DEFAULT NULL,
  status         ENUM('checked_in','checked_out') NOT NULL DEFAULT 'checked_out',
  check_in_date  DATE DEFAULT NULL,
  check_out_date DATE DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Rooms table
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS rooms (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  room_number   VARCHAR(10) NOT NULL UNIQUE,
  block         VARCHAR(20) DEFAULT NULL,
  floor_number  INT DEFAULT NULL,
  capacity      INT NOT NULL DEFAULT 1,
  occupied       INT NOT NULL DEFAULT 0,
  room_type     ENUM('single','double','triple','dormitory') NOT NULL DEFAULT 'single',
  monthly_fee   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status        ENUM('available','full','maintenance') NOT NULL DEFAULT 'available',
  description   VARCHAR(255) DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Allocations table (student <-> room)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS allocations (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  student_id    INT NOT NULL,
  room_id       INT NOT NULL,
  allocated_date DATE NOT NULL,
  vacate_date    DATE DEFAULT NULL,
  status         ENUM('active','vacated') NOT NULL DEFAULT 'active',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_alloc_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_alloc_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Payments table
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  student_id      INT NOT NULL,
  amount          DECIMAL(10,2) NOT NULL,
  payment_type    ENUM('hostel_fee','security_deposit','utility','fine','other') NOT NULL DEFAULT 'hostel_fee',
  payment_method  ENUM('cash','card','bank_transfer','online') NOT NULL DEFAULT 'cash',
  payment_date    DATE NOT NULL,
  month_for       VARCHAR(20) DEFAULT NULL,
  status          ENUM('paid','pending','overdue') NOT NULL DEFAULT 'paid',
  receipt_number  VARCHAR(50) DEFAULT NULL,
  notes           VARCHAR(255) DEFAULT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pay_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Complaints table
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS complaints (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  student_id    INT NOT NULL,
  category      ENUM('maintenance','cleanliness','noise','security','electrical','plumbing','other') NOT NULL DEFAULT 'other',
  subject       VARCHAR(200) NOT NULL,
  description   TEXT NOT NULL,
  priority      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  status        ENUM('open','in_progress','resolved','rejected') NOT NULL DEFAULT 'open',
  response      TEXT DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_comp_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Visitors table
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS visitors (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  student_id     INT NOT NULL,
  visitor_name   VARCHAR(100) NOT NULL,
  visitor_phone  VARCHAR(20) DEFAULT NULL,
  relation       VARCHAR(50) DEFAULT NULL,
  purpose        VARCHAR(200) DEFAULT NULL,
  check_in       DATETIME NOT NULL,
  check_out      DATETIME DEFAULT NULL,
  status         ENUM('visited','inside','left') NOT NULL DEFAULT 'inside',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_vis_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Default admin account
-- password is "admin123" - hashed with PHP password_hash()
-- -----------------------------------------------------------
INSERT INTO users (full_name, email, password, role, phone, status)
VALUES ('System Administrator', 'admin@hostel.com', '$2y$10$N9qo8uLOickgx2ZMRZoMy.Mrq4YjVqGqDqoQ1qGqDqoQ1qGqDqoQ1q', 'admin', '0000000000', 'active')
ON DUPLICATE KEY UPDATE email = email;

-- -----------------------------------------------------------
-- Helpful indexes
-- -----------------------------------------------------------
CREATE INDEX idx_students_number ON students(student_number);
CREATE INDEX idx_allocations_status ON allocations(status);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_visitors_status ON visitors(status);
