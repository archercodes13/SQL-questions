
## Posledních 10 knih

```sql
SELECT *
FROM books
ORDER BY id DESC
LIMIT 10;
```

## Search podle názvu nebo autora

```sql
SELECT *
FROM books
WHERE title LIKE ? OR author LIKE ?
ORDER BY id DESC;
```

## Search podle názvu/autora a volitelného žánru

```sql
SELECT *
FROM books
WHERE (title LIKE ? OR author LIKE ?)
AND (? = '' OR genre_id = ?)
ORDER BY id DESC;
```

## Knihy s žánrem a kontaktem

```sql
SELECT books.*, genres.name AS genre, users.name AS user_name, users.email AS contact
FROM books
JOIN genres ON books.genre_id = genres.id
JOIN users ON books.user_id = users.id
ORDER BY books.id DESC;
```

## Počet knih v každém žánru

```sql
SELECT genres.name, COUNT(books.id) AS book_count
FROM genres
LEFT JOIN books ON books.genre_id = genres.id
GROUP BY genres.id, genres.name
ORDER BY book_count DESC;
```

## Žánry bez knih

```sql
SELECT genres.*
FROM genres
LEFT JOIN books ON books.genre_id = genres.id
WHERE books.id IS NULL;
```

## Žánry s alespoň 2 knihami

```sql
SELECT genres.name, COUNT(books.id) AS book_count
FROM genres
JOIN books ON books.genre_id = genres.id
GROUP BY genres.id, genres.name
HAVING COUNT(books.id) >= 2;
```

## Studenti daného předmětu

```sql
SELECT students.*
FROM students
JOIN student_subjects ON students.id = student_subjects.student_id
WHERE student_subjects.subject_id = ?;
```

## Učitelé daného předmětu

```sql
SELECT teachers.*
FROM teachers
JOIN teacher_subjects ON teachers.id = teacher_subjects.teacher_id
WHERE teacher_subjects.subject_id = ?;
```
