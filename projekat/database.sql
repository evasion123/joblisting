-- Create database and tables
CREATE DATABASE IF NOT EXISTS job_listings
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE job_listings;

-- Users who can apply
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories for jobs
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Jobs (visible to everyone)
CREATE TABLE IF NOT EXISTS jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  company_name VARCHAR(160) NOT NULL,
  category_id INT NULL,
  city VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_jobs_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Applications (only logged-in users)
CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT uq_app UNIQUE (job_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS companies (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(160) NOT NULL UNIQUE,
      email VARCHAR(190) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      website VARCHAR(255) NULL,
      address VARCHAR(255) NULL,
      about TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(120) NOT NULL,
      email VARCHAR(190) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4

-- Seed categories
INSERT IGNORE INTO categories (name) VALUES
  ('IT'), ('Marketing'), ('Sales'), ('Design');

-- Seed jobs
INSERT INTO jobs (title, company_name, category_id, city, description, is_active) VALUES
  ('Junior PHP Developer', 'SubSoft', (SELECT id FROM categories WHERE name="IT"), 'Subotica', 'Work on PHP apps. Learn, build, ship.', 1),
  ('Front-End Intern', 'Pixel Forge', (SELECT id FROM categories WHERE name="Design"), 'Novi Sad', 'Assist in building responsive UIs.', 1),
  ('Marketing Assistant', 'North Media', (SELECT id FROM categories WHERE name="Marketing"), 'Beograd', 'Help run campaigns and social media.', 1),
  ('Sales Representative', 'TechTrade', (SELECT id FROM categories WHERE name="Sales"), 'Niš', 'Talk to customers and close deals.', 1);
