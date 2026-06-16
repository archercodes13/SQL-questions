<?php

function getBooks($sql) {
    $stmt = $sql->prepare("SELECT * FROM books ORDER BY id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLatestBooks($sql) {
    $stmt = $sql->prepare("SELECT * FROM books ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getBook($sql, $id) {
    $stmt = $sql->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

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

function addBook($sql, $title, $author, $description, $publication_year, $isbn, $cover_image, $genre_id, $user_id) {
    $stmt = $sql->prepare("
        INSERT INTO books (title, author, description, publication_year, isbn, cover_image, genre_id, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$title, $author, $description, $publication_year, $isbn, $cover_image, $genre_id, $user_id]);
}

function editBook($sql, $title, $author, $description, $publication_year, $isbn, $genre_id, $id) {
    $stmt = $sql->prepare("
        UPDATE books
        SET title = ?, author = ?, description = ?, publication_year = ?, isbn = ?, genre_id = ?
        WHERE id = ?
    ");
    return $stmt->execute([$title, $author, $description, $publication_year, $isbn, $genre_id, $id]);
}

function deleteBook($sql, $id) {
    $stmt = $sql->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

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

function getSubjectsBySchool($sql, $school_id) {
    $stmt = $sql->prepare("SELECT * FROM subjects WHERE school_id = ? ORDER BY name ASC");
    $stmt->execute([$school_id]);
    return $stmt->fetchAll();
}

function getStudentsBySubject($sql, $subject_id) {
    $stmt = $sql->prepare("
        SELECT students.*
        FROM students
        JOIN student_subjects ON students.id = student_subjects.student_id
        WHERE student_subjects.subject_id = ?
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function getTeachersBySubject($sql, $subject_id) {
    $stmt = $sql->prepare("
        SELECT teachers.*
        FROM teachers
        JOIN teacher_subjects ON teachers.id = teacher_subjects.teacher_id
        WHERE teacher_subjects.subject_id = ?
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}
