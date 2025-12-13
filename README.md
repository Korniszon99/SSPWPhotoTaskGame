# FUT Photo Game

## Opis
FUT Photo Game to aplikacja internetowa stworzona w PHP na potrzeby wydarzeń Forum Uczelni Technicznych. Celem aplikacji jest integracja uczestników w trakcie integracji, które odbywają się w trakcie wydarzeń, poprzez wykonywanie zadań fotograficznych głównie z innymi uczestnikami. Użytkownicy mogą się logować, losować zadania, przeglądać zadania, które zostały dla nich wylosowane, wykonywać je poprzez przesyłanie zdjęć oraz przeglądać galerię wykonanych zadań. Ponadto każdy uczestnik miał możliwość przeglądania galerii zdjęć przedstawiających wykonane zadania przez wszystkich uczestników.
<br>Aktualna wersja aplikacji dostępna jest pod adresem: <a href="https://futopack.fut.edu.pl/photo_game">futopack.fut.edu.pl/photo_game</a>.
## Funkcje
- Rejestracja i logowanie użytkowników
- Losowanie losowych zadań
- Przesyłanie zdjęć w celu wykonania zadań
- Przeglądanie galerii wykonanych zadań
- Przeglądanie najlepszych użytkowników z największą liczbą wykonanych zadań
- Przeglądanie najlepiej ocenianych zdjęć 

## Użytkowanie

1. Zarejestruj nowe konto użytkownika.
2. Zaloguj się swoimi danymi.
3. Wylosuj zadanie i wykonaj je, przesyłając zdjęcie.
4. Przeglądaj swoje zadania i galerię wykonanych zadań.

## Konfiguracja przez zmienne środowiskowe

Aplikacja wspiera konfigurację poprzez zmienne środowiskowe (np. w Azure App Service). Jeśli zmienne nie są ustawione, użyte zostaną bezpieczne wartości domyślne do środowisk testowych.

Wspierane zmienne środowiskowe:

- `ADMIN_USERNAME` – login konta administratora.
  - Domyślna wartość: `admin`
- `ADMIN_PASSWORD` – hasło konta administratora.
  - Domyślna wartość: `admin`
  - Zachowanie: jeśli użytkownik o nazwie `ADMIN_USERNAME` istnieje i ta zmienna jest ustawiona, hasło zostanie zaktualizowane przy starcie aplikacji.
- `ACCESS_CODE` – aktywny kod dostępu do wydarzenia.
  - Domyślna wartość: `demo`
  - Zachowanie: jeżeli kod nie istnieje, zostanie utworzony jako aktywny; jeżeli istnieje i jest nieaktywny, zostanie aktywowany.

Uwaga bezpieczeństwa: w środowiskach produkcyjnych bezwzględnie ustaw wartości niestandardowe dla `ADMIN_USERNAME` i silne hasło w `ADMIN_PASSWORD`. Nie commituj plików z danymi wrażliwymi do repozytorium.

### Przykładowa konfiguracja (Azure App Service)

W Azure Portal → App Service → Configuration → Application settings dodaj wpisy:

- Name: `ADMIN_USERNAME`, Value: `twoj_admin`
- Name: `ADMIN_PASSWORD`, Value: `bardzo_silne_haslo`
- Name: `ACCESS_CODE`, Value: `FUT2025`

Zapisz zmiany i zrestartuj aplikację. Przy starcie ustawienia zostaną zastosowane.

## Uruchomienie lokalne (dev)

Wymagania: PHP z rozszerzeniem PDO SQLite.

1. Przejdź do katalogu `photo_game/` i uruchom wbudowany serwer:
   ```bash
   php -S localhost:8000
   ```
2. Wejdź na `http://localhost:8000/` – przy pierwszym uruchomieniu aplikacja utworzy plik bazy `photo_game/database/database.sqlite` i zainicjalizuje schemat bazy.
3. Domyślne dane logowania (jeśli nie ustawiono ENV):
   - login: `admin`
   - hasło: `admin`
4. Domyślny kod dostępu do wydarzenia (jeśli nie ustawiono ENV): `demo`.

## Uwagi dot. bazy danych i mock danych

- Silnik: SQLite (plik `photo_game/database/database.sqlite`).
- Schemat jest ładowany z `photo_game/database/schema.sql` przy pierwszym uruchomieniu.
- W schemacie znajduje się jeden przykładowy „mock” rekord w tabeli `tasks`, dodawany tylko gdy tabela jest pusta (łatwo go później edytować/usunąć).

## Special Thanks
Specjalne podziękowania dla: <a href="https://github.com/GizaBartosz/FUTPhotoTaskGame">Bartosza Gizy</a>, twórcy pierwotnej wersji aplikacji, która posłużyła jako baza do stworzenia tej wersji.
