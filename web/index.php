<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../app/Application.php';

$app = new Application();

$app->get('/', function (Application $app) {
    return $app->redirect('/books');
});

$app->get('/books', function (Application $app) {
    $conn = $app->getConnection();

    return $app->render('books.html.twig', [
        'books'   => $conn->query('SELECT b.*, a.name author FROM books b JOIN authors a ON a.id = b.author_id')->fetchAll(),
        'authors' => $conn->query('SELECT * FROM authors')->fetchAll()
    ]);
})->bind('books');

$app->post('/books', function (Application $app, Request $request) {
    $query = $app->getConnection()->prepare(
        'INSERT INTO books (name, author_id, price, count) VALUES(s:name, i:author, f:price, i:count)'
    );
    $query->execute([
        'name'   => $request->get('name'),
        'author' => $request->get('author'),
        'price'  => $request->get('price'),
        'count'  => $request->get('count'),
    ]);

    return $app->redirect('/books');
});

$app->post('/authors', function (Application $app, Request $request) {
    $query = $app->getConnection()->prepare('INSERT INTO authors (name) VALUES(s:name)');
    $query->execute([
        'name' => $request->get('name')
    ]);

    return $app->redirect('/books');
});

$app->get('/acts', function (Application $app) {
    $conn = $app->getConnection();
    $acts = [];
    $actBooks = $conn->query('
         SELECT ab.act_id, ab.count, (ab.count * ab.price) price, b.name name, a.name author
         FROM act_books ab
         JOIN books b ON b.id = ab.book_id
         JOIN authors a ON a.id = b.author_id
    ')->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($conn->query('SELECT * FROM acts')->fetchAll(\PDO::FETCH_ASSOC) as $act) {
        $books = [];
        foreach ($actBooks as $book) {
            if ($act['id'] == $book['act_id']) {
                $books[] = $book;
            }
        }
        $act['books'] = $books;
        $acts[] = $act;
    }

    return $app->render('acts.html.twig', [
        'acts' => $acts
    ]);
})->bind('acts');

$app->post('/acts', function (Application $app, Request $request) {
    $conn = $app->getConnection();
    $query = $conn->prepare('INSERT INTO acts (reason) VALUES (s:reason)');
    $query->execute([
        'reason' => $request->get('reason')
    ]);
    $actId = $conn->lastInsertId();

    $query = $conn->prepare('INSERT INTO act_books (act_id, book_id, count) VALUES (i:act, i:book, i:count)');
    foreach ($request->get('books') as $book) {
        $query->execute([
            'act'   => $actId,
            'book'  => $book['id'],
            'count' => $book['count']
        ]);
    }

    return $app->json();
});

$app->run();
