-- =========================================================
-- DATABASE: ujian_asts
-- Tabel users dibuat menyesuaikan data JSON:
-- id, name, nisn, ttl, gender, email, address
-- =========================================================

CREATE DATABASE IF NOT EXISTS ujian_asts
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE ujian_asts;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    nisn     VARCHAR(30)  DEFAULT NULL,
    ttl      VARCHAR(150) DEFAULT NULL,     -- Tempat Tanggal Lahir
    gender   VARCHAR(20)  DEFAULT NULL,     -- Laki-laki / Perempuan
    email    VARCHAR(150) NOT NULL,
    address  VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- =========================================================
-- DATA AWAL (dari jsonplaceholder.typicode.com/users)
-- Field nisn, ttl, gender ditambahkan sendiri karena tidak
-- tersedia di JSON aslinya (sesuai contoh tugas)
-- =========================================================

INSERT INTO users (name, nisn, ttl, gender, email, address) VALUES
('Leanne Graham',          '0012345671', 'Ponorogo, 12 Agustus 2008',   'Perempuan', 'Sincere@april.biz',        'Kulas Light, Apt. 556, Gwenborough, 92998-3874'),
('Ervin Howell',           '0012345672', 'Surabaya, 03 Januari 2009',   'Laki-laki', 'Shanna@melissa.tv',       'Victor Plains, Suite 879, Wisokyburgh, 90566-7771'),
('Clementine Bauch',       '0012345673', 'Malang, 20 Mei 2008',         'Perempuan', 'Nathan@yesenia.net',      'Douglas Extension, Suite 847, McKenziehaven, 59590-4157'),
('Patricia Lebsack',       '0012345674', 'Jakarta, 15 Juli 2009',       'Perempuan', 'Julianne.OConner@kory.org','Hoeger Mall, Apt. 692, South Elvis, 53919-4257'),
('Chelsey Dietrich',       '0012345675', 'Bandung, 09 Februari 2008',   'Perempuan', 'Lucio_Hettinger@annie.ca','Skiles Walks, Suite 351, Roscoeview, 33263'),
('Mrs. Dennis Schulist',   '0012345676', 'Semarang, 22 Maret 2009',     'Perempuan', 'Karley_Dach@jasper.info', 'Norberto Crossing, Apt. 950, South Christy, 23505-1337'),
('Kurtis Weissnat',        '0012345677', 'Yogyakarta, 30 Juni 2008',    'Laki-laki', 'Telly.Hoeger@billy.biz',  'Rex Trail, Suite 280, Howemouth, 58804-1099'),
('Nicholas Runolfsdottir V','0012345678','Solo, 11 Agustus 2009',       'Laki-laki', 'Sherwood@rosamond.me',   'Ellsworth Summit, Suite 729, Aliyaview, 45169'),
('Glenna Reichert',        '0012345679', 'Denpasar, 05 September 2008', 'Perempuan', 'Chaim_McDermott@dana.io','Dayna Park, Suite 449, Bartholomebury, 76495-3109'),
('Clementina DuBuque',     '0012345680', 'Ponorogo, 12 Agustus 2010',   'Perempuan', 'Rey.Padberg@karina.biz', 'Kattie Turnpike, Suite 198, Lebsackbury, 31428-2261');

-- =========================================================
-- TABEL ADMIN (untuk fitur Login)
-- Password disimpan dalam bentuk hash (bcrypt), BUKAN teks biasa
-- Akun default -> username: admin | password: admin123
-- Segera ganti password ini setelah login pertama kali!
-- =========================================================

CREATE TABLE admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$x89Jm.iyPoGqlC4u/7TokOPm8QuT7oe3tMC6nWZjHcTVbemIqhp5K');

