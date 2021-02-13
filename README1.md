# Laravel Blog with Roles

Celem projektu jest utworzenie struktury **bloga** z założeniami że:
- każdy może się zarejestrować/zalogować;
- użytkownicy mogą mieć jedną z ról *admin*, *autor*, *czytelnik*;
- *Autorzy* mogą w blogu dodawać nowe posty, edytować lub usuwać posty swojego autorstwa;
- *Admini* mają pełny dostęp do wszystkich postów tj. mogą dodawać nowe, edytować lub usuwać wszystkie posty;
- *Admini* mają dostęp również do edycji kont użytkowników tj. mogą im zmieniać role lub ich usuwać;
- *Czytelnicy* mogą tylko komentować pod danym postem;
- każdy może czytać opublikowane posty

Projekt bazuje na projekcie [Harish Kumar](https://www.flowkl.com/tutorial/web-development/simple-blog-application-in-laravel-7)

# Przygotowanie bazy projektu

Pusty projekt Laravela tworzymy za pomocą komendy w polu poleceń:
`composer create-project laravel/laravel nazwa_projektu`
W tym przypadku nazwą projektu jest **"blog_with_roles"**

Następnie doinstalowane są:
- pakiet z podręcznymi klasami pomocniczymi: `composer require laravel/helpers`
- pakiet odpowiadający za wczytanie odpowiedniego UI: `composer require laravel/ui`
- przełączamy się na UI bootstrapa wraz ze ścieżkami autoryzacji `php artisan ui bootstrap --auth`

Konfigurujemy bazę danych w dwóch krokach:
1. tworzymy bazę danych w MySQL poprzez `CREATE DATABASE nazwa_bazy` - u mnie **blog_with_roles**
2. wprowadzamy dane do pliku *.env*:
```sql
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nazwa_bazy #blog_with_roles
DB_USERNAME=nazwa_użytkownika
DB_PASSWORD=hasło_użytkownika
```
Korzystając z możliwości *artisan*'a tworzymy dla postów i komentarzy równocześnie pliki modelu, migracji i kontrolera wraz z podstawowymi metodami:
`php artisan make:model Post -mcr`
`php artisan make:model Comment -mcr`
Znaczenie przełączników przy tworzeniu modelu:
- "m" - utworzenie migracji
- "c" - utworzenie kontrolera
- "cr" - utworzenie kontrolera wraz z podstawowymi metodami
- "s" - utworzenie pliku wpisującego dane z fabryki do bazy oraz pierwsze dane np. z góry przewidzianych użytkowników
- "f" - utworzenie pliku "fabryki" z informacjami, jak tworzyć fake'owe dane

Tworzymy kontroler użytkownika: `php make:controller UserController -r`
Zarówno migracja jak i model są zaimplementowane odrazu przy tworzeniu projektu.

## Uzupełnianie migracji

### Posts
Do istniejących pól `$table` dopisujemy informacje o nowych kolumnach tak, żeby całość wyglądała:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('author_id');
    $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('title')->unidue();
    $table->text('body');
    $table->string('slug')->unique();
    $table->boolean('active');
    $table->timestamps();
});
```
### Comments
Podobnie jak w przypadku **Posts** uzupełniamy informacje o brakujące kolumny:
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('on_post');
    $table->unsignedBigInteger('from_user');
    $table->foreign('on_post')->references('id')->on('posts')->onDelete('cascade');
    $table->foreign('from_user')->references('id')->on('users')->onDelete('cascade');
    $table->text('body');
    $table->timestamps();
});
```

### Users
Dodajemy jedną kolumnę do tabeli mówiącą o rolu użytkownika:
```php
            $table->enum('role', ['admin', 'author', 'subcriber'])->default('author');
```