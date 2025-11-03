CREATE DATABASE IF NOT EXISTS internship_management;
USE internship_management;

-- USERS TABLE
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('student', 'admin') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STUDENT TABLE
CREATE TABLE student (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  resume_link VARCHAR(255) NOT NULL,
  skills VARCHAR(255) NOT NULL,
  year VARCHAR(10) NOT NULL,
  department VARCHAR(50) NOT NULL,
  roll_number INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ADMIN TABLE
CREATE TABLE admin (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- INTERNSHIP TABLE
CREATE TABLE internship (
  internship_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  role VARCHAR(100) NOT NULL,
  company_name VARCHAR(100) NOT NULL,
  posted_on DATE NOT NULL,
  application_link VARCHAR(255),
  deadline DATE NOT NULL,
  status ENUM('open', 'closed', 'filled') NOT NULL,
  location VARCHAR(100),
  stipend VARCHAR(50),
  duration VARCHAR(50),
  department VARCHAR(100) NOT NULL
);

-- APPLICATION TABLE
CREATE TABLE application (
  application_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  internship_id INT NOT NULL,
  applied_on DATE NOT NULL,
  status ENUM('applied', 'accepted', 'rejected') NOT NULL,
  FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  FOREIGN KEY (internship_id) REFERENCES internship(internship_id) ON DELETE CASCADE
);

-- INSERT DEFAULT ADMIN ACCOUNT
INSERT INTO users (email, password, role)
VALUES ('admin@gmail.com', 'admin123', 'admin');

-- Link admin user to the admin table
INSERT INTO admin (user_id)
SELECT user_id FROM users WHERE email = 'admin';