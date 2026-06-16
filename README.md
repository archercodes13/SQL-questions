# Databázové dotazy – CRUD tahák ke zkoušce

GitHub-friendly tahák pro PHP/PDO + SQL. Zaměření: `SELECT`, `WHERE`, `LIKE`, `ORDER BY`, `LIMIT`, `JOIN`, `LEFT JOIN`, `COUNT`, `GROUP BY`, `HAVING`, `INSERT`, `UPDATE`, `DELETE`, `prepare()`, `execute()`, `fetch()`, `fetchAll()`.

---

## 1. Model knihovny

```text
books(id, title, author, description, publication_year, isbn, cover_image, genre_id, user_id)
genres(id, name, description)
users(id, name, email, password)
```

Vazby:

```text
books.genre_id -> genres.id
books.user_id -> users.id
```

---

## 2. Základní PDO vzor

```php
$stmt = $sql->prepare("
    SELECT *
    FROM books
    WHERE id = ?
");

$stmt->execute([$id]);

$book = $stmt->fetch();
```

Pravidlo:

```text
Počet ? v SQL = počet hodnot v execute()
fetch() = jeden řádek
fetchAll() = více řádků
```

---

# A) SELECT / WHERE / ORDER BY / LIMIT

## Výpis všech knih

```php
function getBooks($sql) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        ORDER BY id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Posledních 10 knih na homepage

```php
function getLatestBooks($sql) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        ORDER BY id DESC
        LIMIT 10
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Detail knihy podle ID

```php
function getBook($sql, $id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

## Knihy od konkrétního autora

```php
function getBooksByAuthor($sql, $author) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE author = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$author]);
    return $stmt->fetchAll();
}
```

## Knihy vydané po určitém roce

```php
function getBooksAfterYear($sql, $year) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE publication_year > ?
        ORDER BY publication_year DESC
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}
```

---

# B) SEARCH

## Search podle názvu

```php
function searchBooksByTitle($sql, $search) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE title LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute(["%" . $search . "%"]);
    return $stmt->fetchAll();
}
```

## Search podle autora

```php
function searchBooksByAuthor($sql, $search) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE author LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute(["%" . $search . "%"]);
    return $stmt->fetchAll();
}
```

## Search podle názvu NEBO autora

```php
function searchBooks($sql, $search) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE title LIKE ? OR author LIKE ?
        ORDER BY id DESC
    ");
    $stmt->execute([
        "%" . $search . "%",
        "%" . $search . "%"
    ]);
    return $stmt->fetchAll();
}
```

## Výpis podle žánru

```php
function getBooksByGenre($sql, $genre_id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE genre_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$genre_id]);
    return $stmt->fetchAll();
}
```

## Search název/autor + povinný žánr

```php
function searchBooksByTextAndGenre($sql, $search, $genre_id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE (title LIKE ? OR author LIKE ?)
        AND genre_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([
        "%" . $search . "%",
        "%" . $search . "%",
        $genre_id
    ]);
    return $stmt->fetchAll();
}
```

## Search název/autor + volitelný žánr

Když `$genre_id` je prázdný, zobrazí všechny žánry. Když není prázdný, filtruje podle žánru.

```php
function searchBooksAdvanced($sql, $search, $genre_id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE (title LIKE ? OR author LIKE ?)
        AND (? = '' OR genre_id = ?)
        ORDER BY id DESC
    ");
    $stmt->execute([
        "%" . $search . "%",
        "%" . $search . "%",
        $genre_id,
        $genre_id
    ]);
    return $stmt->fetchAll();
}
```

---

# C) JOIN

## Knihy s názvem žánru

```php
function getBooksWithGenre($sql) {
    $stmt = $sql->prepare("
        SELECT books.*, genres.name AS genre
        FROM books
        JOIN genres ON books.genre_id = genres.id
        ORDER BY books.id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Knihy s žánrem a kontaktem na uživatele

```php
function getBooksFull($sql) {
    $stmt = $sql->prepare("
        SELECT books.*, genres.name AS genre, users.name AS user_name, users.email AS contact
        FROM books
        JOIN genres ON books.genre_id = genres.id
        JOIN users ON books.user_id = users.id
        ORDER BY books.id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Detail knihy s žánrem a kontaktem

```php
function getBookDetail($sql, $id) {
    $stmt = $sql->prepare("
        SELECT books.*, genres.name AS genre, users.name AS user_name, users.email AS contact
        FROM books
        JOIN genres ON books.genre_id = genres.id
        JOIN users ON books.user_id = users.id
        WHERE books.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

## Full search s JOIN

```php
function searchBooksFull($sql, $search, $genre_id) {
    $stmt = $sql->prepare("
        SELECT books.*, genres.name AS genre, users.name AS user_name, users.email AS contact
        FROM books
        JOIN genres ON books.genre_id = genres.id
        JOIN users ON books.user_id = users.id
        WHERE (books.title LIKE ? OR books.author LIKE ?)
        AND (? = '' OR books.genre_id = ?)
        ORDER BY books.id DESC
    ");
    $stmt->execute([
        "%" . $search . "%",
        "%" . $search . "%",
        $genre_id,
        $genre_id
    ]);
    return $stmt->fetchAll();
}
```

---

# D) Detail žánru / kategorie

## Načtení žánru

```php
function getGenre($sql, $id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM genres
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

## Knihy v daném žánru

```php
function getBooksInGenre($sql, $genre_id) {
    $stmt = $sql->prepare("
        SELECT books.*, users.name AS user_name, users.email AS contact
        FROM books
        JOIN users ON books.user_id = users.id
        WHERE books.genre_id = ?
        ORDER BY books.id DESC
    ");
    $stmt->execute([$genre_id]);
    return $stmt->fetchAll();
}
```

---

# E) INSERT / UPDATE / DELETE

## Přidání knihy

```php
function addBook($sql, $title, $author, $description, $publication_year, $isbn, $cover_image, $genre_id, $user_id) {
    $stmt = $sql->prepare("
        INSERT INTO books (
            title, author, description, publication_year, isbn, cover_image, genre_id, user_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $title,
        $author,
        $description,
        $publication_year,
        $isbn,
        $cover_image,
        $genre_id,
        $user_id
    ]);
}
```

## Edit knihy

```php
function editBook($sql, $title, $author, $description, $publication_year, $isbn, $genre_id, $id) {
    $stmt = $sql->prepare("
        UPDATE books
        SET title = ?, author = ?, description = ?, publication_year = ?, isbn = ?, genre_id = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $title,
        $author,
        $description,
        $publication_year,
        $isbn,
        $genre_id,
        $id
    ]);
}
```

## Smazání knihy

```php
function deleteBook($sql, $id) {
    $stmt = $sql->prepare("
        DELETE FROM books
        WHERE id = ?
    ");
    return $stmt->execute([$id]);
}
```

## Edit/mazání pouze vlastní knihy

```php
function editOwnBook($sql, $title, $author, $description, $publication_year, $isbn, $genre_id, $id, $user_id) {
    $stmt = $sql->prepare("
        UPDATE books
        SET title = ?, author = ?, description = ?, publication_year = ?, isbn = ?, genre_id = ?
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([
        $title, $author, $description, $publication_year, $isbn, $genre_id, $id, $user_id
    ]);
}

function deleteOwnBook($sql, $id, $user_id) {
    $stmt = $sql->prepare("
        DELETE FROM books
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$id, $user_id]);
}
```

---

# F) Edit formulář a select

## Načti knihu pro edit

```php
function getBookForEdit($sql, $id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

## Načti žánry do selectu

```php
function getGenres($sql) {
    $stmt = $sql->prepare("
        SELECT *
        FROM genres
        ORDER BY name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Selected hodnota

```php
<select name="genre_id">
    <?php foreach ($genres as $genre): ?>
        <option value="<?= $genre['id'] ?>"
            <?= $genre['id'] == $book['genre_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($genre['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
```

---

# G) UNIQUE hodnoty

Typicky unikátní hodnoty:

```text
isbn
email
ic
dic
rodne_cislo
interni_cislo
```

## Kontrola ISBN při přidání

```php
function isIsbnTaken($sql, $isbn) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE isbn = ?
    ");
    $stmt->execute([$isbn]);
    return $stmt->fetch();
}
```

## Kontrola ISBN při editaci

```php
function isIsbnTakenByAnotherBook($sql, $isbn, $id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM books
        WHERE isbn = ? AND id != ?
    ");
    $stmt->execute([$isbn, $id]);
    return $stmt->fetch();
}
```

---

# H) COUNT / GROUP BY / HAVING / LEFT JOIN

Toto je důležité pro databázovou část zkoušky.

## COUNT všech knih

```php
function countAllBooks($sql) {
    $stmt = $sql->prepare("
        SELECT COUNT(*) AS book_count
        FROM books
    ");
    $stmt->execute();
    return $stmt->fetch();
}
```

## COUNT knih podle žánru

```php
function countBooksByGenre($sql) {
    $stmt = $sql->prepare("
        SELECT genres.name, COUNT(books.id) AS book_count
        FROM genres
        LEFT JOIN books ON books.genre_id = genres.id
        GROUP BY genres.id, genres.name
        ORDER BY book_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

Vysvětlení:

```text
COUNT(books.id) = spočítá knihy
GROUP BY genres.id, genres.name = udělá skupinu pro každý žánr
LEFT JOIN = zobrazí i žánry bez knih
```

## HAVING – žánry alespoň se 2 knihami

```php
function getGenresWithAtLeastTwoBooks($sql) {
    $stmt = $sql->prepare("
        SELECT genres.name, COUNT(books.id) AS book_count
        FROM genres
        JOIN books ON books.genre_id = genres.id
        GROUP BY genres.id, genres.name
        HAVING COUNT(books.id) >= 2
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

`WHERE` filtruje řádky před seskupením.  
`HAVING` filtruje skupiny po `GROUP BY`.

## Žánry bez knih

```php
function getGenresWithoutBooks($sql) {
    $stmt = $sql->prepare("
        SELECT genres.*
        FROM genres
        LEFT JOIN books ON books.genre_id = genres.id
        WHERE books.id IS NULL
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Počet knih podle uživatele

```php
function countBooksByUser($sql) {
    $stmt = $sql->prepare("
        SELECT users.name, users.email, COUNT(books.id) AS book_count
        FROM users
        LEFT JOIN books ON books.user_id = users.id
        GROUP BY users.id, users.name, users.email
        ORDER BY book_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

---

# I) Školní systém ze zadání

## Atomizace adresy

Špatně:

```sql
address VARCHAR(255)
```

Správně:

```sql
street VARCHAR(100),
house_number VARCHAR(20),
city VARCHAR(100),
zip_code VARCHAR(20),
country VARCHAR(100)
```

## Tabulky

```text
schools
students
teachers
subjects
student_subjects
teacher_subjects
```

`student_subjects` a `teacher_subjects` jsou spojovací tabulky pro vztahy M:N.

---

## Návrh schools

```sql
CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    director_first_name VARCHAR(100) NOT NULL,
    director_last_name VARCHAR(100) NOT NULL,
    street VARCHAR(100) NOT NULL,
    house_number VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    ic VARCHAR(20) UNIQUE,
    dic VARCHAR(30) UNIQUE
);
```

## Návrh students

```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    street VARCHAR(100) NOT NULL,
    house_number VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    birth_number VARCHAR(20) UNIQUE,
    phone VARCHAR(30),
    FOREIGN KEY (school_id) REFERENCES schools(id)
);
```

## Návrh teachers

```sql
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    street VARCHAR(100) NOT NULL,
    house_number VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    birth_number VARCHAR(20) UNIQUE,
    phone VARCHAR(30),
    internal_number INT UNIQUE,
    FOREIGN KEY (school_id) REFERENCES schools(id)
);
```

## Návrh subjects

```sql
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    FOREIGN KEY (school_id) REFERENCES schools(id)
);
```

## Spojovací tabulka student_subjects

```sql
CREATE TABLE student_subjects (
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (student_id, subject_id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
```

## Spojovací tabulka teacher_subjects

```sql
CREATE TABLE teacher_subjects (
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
```

---

# J) Školní dotazy

## Seznam škol

```php
function getSchools($sql) {
    $stmt = $sql->prepare("
        SELECT *
        FROM schools
        ORDER BY name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Detail školy

```php
function getSchool($sql, $id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM schools
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

## Předměty dané školy

```php
function getSubjectsBySchool($sql, $school_id) {
    $stmt = $sql->prepare("
        SELECT *
        FROM subjects
        WHERE school_id = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$school_id]);
    return $stmt->fetchAll();
}
```

## Studenti daného předmětu

```php
function getStudentsBySubject($sql, $subject_id) {
    $stmt = $sql->prepare("
        SELECT students.*
        FROM students
        JOIN student_subjects ON students.id = student_subjects.student_id
        WHERE student_subjects.subject_id = ?
        ORDER BY students.last_name ASC, students.first_name ASC
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}
```

## Učitelé daného předmětu

```php
function getTeachersBySubject($sql, $subject_id) {
    $stmt = $sql->prepare("
        SELECT teachers.*
        FROM teachers
        JOIN teacher_subjects ON teachers.id = teacher_subjects.teacher_id
        WHERE teacher_subjects.subject_id = ?
        ORDER BY teachers.last_name ASC, teachers.first_name ASC
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}
```

## Přidání studenta na předmět

```php
function addStudentToSubject($sql, $student_id, $subject_id) {
    $stmt = $sql->prepare("
        INSERT INTO student_subjects (student_id, subject_id)
        VALUES (?, ?)
    ");
    return $stmt->execute([$student_id, $subject_id]);
}
```

## Přidání učitele k předmětu

```php
function addTeacherToSubject($sql, $teacher_id, $subject_id) {
    $stmt = $sql->prepare("
        INSERT INTO teacher_subjects (teacher_id, subject_id)
        VALUES (?, ?)
    ");
    return $stmt->execute([$teacher_id, $subject_id]);
}
```

---

# K) Školní search / UNIQUE / statistiky

## Search školy podle názvu, města nebo IČ

```php
function searchSchools($sql, $search) {
    $stmt = $sql->prepare("
        SELECT *
        FROM schools
        WHERE name LIKE ?
           OR city LIKE ?
           OR ic LIKE ?
        ORDER BY name ASC
    ");
    $stmt->execute([
        "%" . $search . "%",
        "%" . $search . "%",
        "%" . $search . "%"
    ]);
    return $stmt->fetchAll();
}
```

## Kontrola IČ

```php
function isIcTaken($sql, $ic) {
    $stmt = $sql->prepare("
        SELECT *
        FROM schools
        WHERE ic = ?
    ");
    $stmt->execute([$ic]);
    return $stmt->fetch();
}
```

## Počet předmětů v každé škole

```php
function countSubjectsBySchool($sql) {
    $stmt = $sql->prepare("
        SELECT schools.name, COUNT(subjects.id) AS subject_count
        FROM schools
        LEFT JOIN subjects ON subjects.school_id = schools.id
        GROUP BY schools.id, schools.name
        ORDER BY subject_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Počet studentů na každém předmětu

```php
function countStudentsBySubject($sql) {
    $stmt = $sql->prepare("
        SELECT subjects.name, COUNT(student_subjects.student_id) AS student_count
        FROM subjects
        LEFT JOIN student_subjects ON student_subjects.subject_id = subjects.id
        GROUP BY subjects.id, subjects.name
        ORDER BY student_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

## Předměty bez studentů

```php
function getSubjectsWithoutStudents($sql) {
    $stmt = $sql->prepare("
        SELECT subjects.*
        FROM subjects
        LEFT JOIN student_subjects ON student_subjects.subject_id = subjects.id
        WHERE student_subjects.student_id IS NULL
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

---

# Rychlý slovníček

```text
SELECT      = vyber data
FROM        = z jaké tabulky
WHERE       = podmínka
LIKE        = hledání části textu
ORDER BY    = řazení
DESC        = sestupně
ASC         = vzestupně
LIMIT       = omezení počtu výsledků
JOIN        = propojení tabulek
LEFT JOIN   = zachová i řádky bez vazby
COUNT       = spočítá počet
GROUP BY    = seskupí výsledky
HAVING      = podmínka pro skupiny
INSERT      = přidání
UPDATE      = úprava
DELETE      = mazání
PRIMARY KEY = hlavní identifikátor
FOREIGN KEY = cizí klíč
UNIQUE      = hodnota se nesmí opakovat
```
