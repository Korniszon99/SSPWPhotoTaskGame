# Photo Task Game FUT
English below

<img src="https://github.com/user-attachments/assets/9b09c2e4-652b-4204-9f7e-aabcbdb6319d" width="200" />
<img src="https://github.com/user-attachments/assets/5db8c9a2-66f7-40e7-8d35-15a489e989e4" width="200" />
<img src="https://github.com/user-attachments/assets/42f32a70-1e25-4970-97e8-a72480956aa5" width="200" />
<img src="https://github.com/user-attachments/assets/1662481b-7f71-4d1b-bd31-e23e266cf008" width="200" />

## Opis
Photo Task Game FUT to aplikacja internetowa stworzona w PHP na potrzeby Zjazdu Sprawozdawczo-Wyborczego Forum Uczelni Technicznych na Politechnice Gdańskiej w 2024 r. Celem aplikacji była integracja gości na balu, który odbywał się w trakcie zjazdu, poprzez wykonywanie zadań fotograficznych głównie z innymi uczestnikami. Użytkownicy mogą się logować, losować zadania, przeglądać zadania, które zostały dla nich wylosowane, wykonywać je poprzez przesyłanie zdjęć oraz przeglądać galerię wykonanych zadań. Ponadto każdy uczestnik miał możliwość przeglądania galerii zdjęć przedstawiających wykonane zadania przez wszystkich uczestników oraz pobierania ich na swoje urządzenie.

## Funkcje
- Rejestracja i logowanie użytkowników
- Losowanie losowych zadań
- Przesyłanie zdjęć w celu wykonania zadań
- Przeglądanie galerii wykonanych zadań
- Przeglądanie najlepszych użytkowników z największą liczbą wykonanych zadań (funkcja administratora)

## Instalacja

1. Sklonuj repozytorium:
    ```sh
    git clone https://github.com/GizaBartosz/PhotoTaskGameFUT.git
    cd PhotoTaskGameFUT
    ```

2. Skonfiguruj bazę danych:
    - Zaimportuj plik `database.sql` do swojej bazy danych MySQL, aby utworzyć niezbędne tabele i wstawić przykładowe dane.

3. Skonfiguruj aplikację:
    - Zaktualizuj plik `config.php` swoimi danymi połączenia z bazą danych.

## Użytkowanie

1. Zarejestruj nowe konto użytkownika.
2. Zaloguj się swoimi danymi.
3. Wylosuj zadanie i wykonaj je, przesyłając zdjęcie.
4. Przeglądaj swoje zadania i galerię wykonanych zadań.

## Wkład

1. Forkuj repozytorium.
2. Utwórz nową gałąź (`git checkout -b feature-branch`).
3. Wprowadź swoje zmiany.
4. Zatwierdź swoje zmiany (`git commit -m 'Dodaj nową funkcję'`).
5. Wypchnij zmiany do gałęzi (`git push origin feature-branch`).
6. Otwórz pull request.

# English

## Description
Photo Task Game FUT is a web application developed in PHP for the event of the Forum of Technical Universities held at Gdańsk University of Technology in 2024. The purpose of the application was to integrate guests at the ball held during the event by completing photo tasks. Users can log in, draw tasks, complete them by uploading photos, and browse the gallery of completed tasks.

## Features
- User registration and login
- Random task drawing
- Uploading photos to complete tasks
- Browsing the gallery of completed tasks
- Viewing the top users with the highest number of completed tasks (admin feature)

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/GizaBartosz/PhotoTaskGameFUT.git
    cd PhotoTaskGameFUT
    ```

2. Configure the database:
    - Import the `database.sql` file into your MySQL database to create the necessary tables and insert sample data.

3. Configure the application:
    - Update the `config.php` file with your database connection details.

## Usage

1. Register a new user account.
2. Log in with your credentials.
3. Draw a task and complete it by uploading a photo.
4. Browse your tasks and the gallery of completed tasks.

## Contribution

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Make your changes.
4. Commit your changes (`git commit -m 'Add new feature'`).
5. Push your changes to the branch (`git push origin feature-branch`).
6. Open a pull request.


