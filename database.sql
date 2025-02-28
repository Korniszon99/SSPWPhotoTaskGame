CREATE DATABASE IF NOT EXISTS sspw_starachowice_app;
USE sspw_starachowice_app;


-- Tworzenie tabeli users
CREATE TABLE IF NOT EXISTS users (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
    );

-- Tworzenie tabeli tasks
CREATE TABLE IF NOT EXISTS tasks (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     description TEXT NOT NULL,
                                     language ENUM ('pl', 'en')
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

INSERT INTO tasks (description, language) VALUES
                                              ('Zrób zdjęcie z osobą ubraną nieodpowiednio do pogody', 'pl'),
                                              ('Take a photo with someone dressed inappropriately for the weather', 'en'),
                                              ('Udowodnijcie, że jesteście przyszłymi inżynierami', 'pl'),
                                              ('Prove that you are future engineers', 'en'),
                                              ('Znajdź i zrób sobie zdjęcie z osobą z kadry', 'pl'),
                                              ('Find and take a photo with a member of the cadre', 'en'),
                                              ('Zrób zdjęcie ze zwierzakiem (interpretacja dowolna)', 'pl'),
                                              ('Take a photo with an animal (any interpretation)', 'en'),
                                              ('Zbuduj piramidę z ludzi', 'pl'),
                                              ('Build a human pyramid', 'en'),
                                              ('Zrób zdjęcie, na którym będzie jak najwięcej ludzi', 'pl'),
                                              ('Take a photo with as many people as possible', 'en'),
                                              ('Zrób zdjęcie z objawem wiosny', 'pl'),
                                              ('Take a photo with a sign of spring', 'en'),
                                              ('Zrób zdjęcie z samochodem wojskowym', 'pl'),
                                              ('Take a photo with a military vehicle', 'en'),
                                              ('Zrób zdjęcie, jak prowadzisz aktywny tryb życia', 'pl'),
                                              ('Take a photo showing your active lifestyle', 'en'),
                                              ('Pokaż nam jak się bawisz!', 'pl'),
                                              ('Show us how you have fun!', 'en'),
                                              ('Zróbcie zdjęcie, na którym jesteście zamienieni ubraniami', 'pl'),
                                              ('Take a photo where you have swapped clothes', 'en'),
                                              ('Pokaż nam najciekawsze miejsce jakie znalazłeś w parku miejskim Żeromskiego', 'pl'),
                                              ('Show us the most interesting place you found in Zeromski City Park', 'en'),
                                              ('Pokaż nam najpiękniejsze widoki Starachowic', 'pl'),
                                              ('Show us the most beautiful views of Starachowice', 'en'),
                                              ('Zrób zdjęcie z fontanną', 'pl'),
                                              ('Take a photo with a fountain', 'en'),
                                              ('Zrób zdjęcie związane ze swoją komisją', 'pl'),
                                              ('Take a photo related to your committee', 'en'),
                                              ('Pokaż nam swój ulubiony sport', 'pl'),
                                              ('Show us your favorite sport', 'en'),
                                              ('Zrób zdjęcie z fajnym drzewem', 'pl'),
                                              ('Take a photo with a cool tree', 'en'),
                                              ('Zrób zdjęcie z osobą w jaskrawym ubraniu', 'pl'),
                                              ('Take a photo with someone in bright clothing', 'en'),
                                              ('Pochwal się zakupem godnym Starachowic', 'pl'),
                                              ('Show off a purchase worthy of Starachowice', 'en'),
                                              ('Zrób zdjęcie na siłowni', 'pl'),
                                              ('Take a photo at the gym', 'en'),
                                              ('Uchwyć transport miejski', 'pl'),
                                              ('Capture public transportation', 'en'),
                                              ('Zrób zdjęcie reklamie, która zwróciła Twoją uwagę', 'pl'),
                                              ('Take a photo of an advertisement that caught your attention', 'en'),
                                              ('Przedstaw herb Starachowic', 'pl'),
                                              ('Present the coat of arms of Starachowice', 'en');