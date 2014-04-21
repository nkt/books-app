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
        'books'   => $conn->query('SELECT b.*, a.name author FROM books b JOIN authors a ON a.id = b.author_id'),
        'authors' => $conn->query('SELECT * from authors')->fetchAll()
    ]);
});
$app->post('/books', function (Application $app, Request $request) {
    $stmt = $app->getConnection()->prepare(
        'INSERT INTO books (name, author_id, price, count) VALUES(s:name, i:author, f:price, i:count)'
    );
    $stmt->execute([
        'name'   => $request->request->get('name'),
        'author' => $request->request->get('author'),
        'price'  => $request->request->get('price'),
        'count'  => $request->request->get('count'),
    ]);

    return $app->redirect('/books');
});

$app->post('/authors', function (Application $app, Request $request) {
    $stmt = $app->getConnection()->prepare('INSERT INTO authors (name) VALUES(s:name)');
    $stmt->execute(['name' => $request->request->get('name'),]);

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
});

$app->run();
