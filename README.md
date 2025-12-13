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

- Konfiguracja bazy danych (kolejność priorytetów):
  1) `DATABASE_URL` – pełny URL połączenia (np. `mysql://user:pass@host:3306/db?charset=utf8mb4`, `pgsql://...`, `sqlsrv://...`, `sqlite:///absolute/path/to.db`).
  2) Azure Connection Strings (automatyczna detekcja):
     - dowolna zmienna zaczynająca się od: `MYSQLCONNSTR_`, `POSTGRESQLCONNSTR_`, `SQLCONNSTR_`, `SQLAZURECONNSTR_`.
       Aplikacja sparsuje wartości w formacie `Key=Value;Key=Value;...` (np. `Data Source=...;User Id=...;Password=...;Database=...`).
  3) Zestaw klasycznych zmiennych:
     - `DB_DRIVER` – `mysql` | `pgsql` | `postgres` | `postgresql` | `sqlsrv` | `mssql` | `sqlite`
     - `DB_HOST` – host serwera DB
     - `DB_PORT` – port serwera DB
     - `DB_NAME` – nazwa bazy danych
     - `DB_USER` – użytkownik bazy danych
     - `DB_PASSWORD` – hasło
     - `DB_CHARSET` – np. `utf8mb4` (dla MySQL)
     - `DB_PATH` – pełna ścieżka do pliku przy `DB_DRIVER=sqlite`
  4) Fallback: jeśli żadna z powyższych nie jest ustawiona – użyta zostanie lokalna baza SQLite w repo: `photo_game/database/database.sqlite` (tworzona automatycznie).

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

Do bazy danych na Azure możesz użyć jednej z metod:

- Azure Connection String (zalecane – dodaj w sekcji Connection strings):
  - MySQL: utwórz wpis `MYSQLCONNSTR_MAIN` z wartością np.: `Data Source=yourmysqlhost.mysql.database.azure.com;Database=photogame;User Id=user@yourmysqlhost;Password=SuperHaslo123;Port=3306`
  - PostgreSQL: `POSTGRESQLCONNSTR_MAIN` np.: `Host=yourpg.postgres.database.azure.com;Database=photogame;User Id=user@yourpg;Password=SuperHaslo123;Port=5432`
  - Azure SQL: `SQLAZURECONNSTR_MAIN` np.: `Data Source=tcp:your-sql.database.windows.net,1433;Initial Catalog=photogame;User Id=user;Password=SuperHaslo123`

- Lub `DATABASE_URL`, np.:
  - `mysql://user:SuperHaslo123@yourmysqlhost.mysql.database.azure.com:3306/photogame?charset=utf8mb4`
  - `pgsql://user:SuperHaslo123@yourpg.postgres.database.azure.com:5432/photogame`
  - `sqlsrv://user:SuperHaslo123@your-sql.database.windows.net:1433/photogame`

- Lub zestaw `DB_*`:
  - `DB_DRIVER=mysql`, `DB_HOST=yourmysqlhost.mysql.database.azure.com`, `DB_PORT=3306`, `DB_NAME=photogame`, `DB_USER=user@yourmysqlhost`, `DB_PASSWORD=SuperHaslo123`, `DB_CHARSET=utf8mb4`

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
