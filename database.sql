-- Tworzenie bazy danych
CREATE DATABASE IF NOT EXISTS fut_gdansk_app;
USE fut_gdansk_app;

-- Tworzenie tabeli users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tworzenie tabeli tasks
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL
);

-- Tworzenie tabeli user_tasks
CREATE TABLE IF NOT EXISTS user_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (task_id) REFERENCES tasks(id)
);

-- Tworzenie tabeli completed_tasks
CREATE TABLE IF NOT EXISTS completed_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_task_id INT NOT NULL,
    user_id INT NOT NULL,
    photo VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_task_id) REFERENCES user_tasks(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Dodawanie przykładowych zadań
INSERT INTO tasks (description) VALUES 
('Zrób zdjęcie z kimś, kto nosi spinki do mankietów.'),
('Zrób sobie z kimś zdjęcie w lustrze.'),
('Zrób sobie zdjęcie z choinką.'),
('Zrób sobie zdjęcie z kimś, kto umie opowiadać zabawne żarty.'),
('Zrób zdjęcie z osobą, która trzyma zimowy akcent.'),
('Zrób zdjęcie z kimś z kim zrobisz śmieszną pozę podczas pozowania do zdjęcia.'),
('Zrób sobie zdjęcie z przypinką z innej uczelni.'),
('Zrób zdjęcie z osobą, która ma pomalowane paznokcie.'),
('Zrób sobie zdjęcie na ściance SSPG.'),
('Zrób sobie zdjęcie z krawatem.'),
('Zrób sobie zdjęcie z osobą w spiętych włosach.');