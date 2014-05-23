<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

require __DIR__ . '/../app/Application.php';

$app = new Application();

$checkAuth = function (Request $request) use ($app) {
    if (!$request->getSession()->has('user')) {
        return $app->redirect('/');
    }
};

$app->get('/', function (Application $app) {
    return $app->render('index.html.twig');
});

$app->post('/login', function (Application $app, Request $request) {
    $conn = $app->getConnection();
    $query = $conn->prepare('SELECT * FROM users WHERE username = s:username');
    $user = $query->execute([
        'username' => $request->get('username')
    ])->fetch();
    if (!$user) {
        throw new AccessDeniedHttpException();
    }
    if ($user['password'] != $app->encodePassword($request->get('password'), $user['salt'])) {
        throw new AccessDeniedHttpException();
    }
    $session = $request->getSession();
    $session->set('user', $user);

    return $app->redirect($app->url('books'));
})->bind('login');

$app->get('/logout', function (Application $app, Request $request) {
    $request->getSession()->clear();

    return $app->redirect('/');
});

$app->post('/register', function (Application $app, Request $request) {
    $conn = $app->getConnection();
    $query = $conn->prepare('INSERT INTO users (username, password, salt) VALUES (s:username, s:password, s:salt)');
    $salt = sha1(uniqid(time()));
    $query->execute([
        'username' => $request->get('username'),
        'password' => $app->encodePassword($request->get('password'), $salt),
        'salt'     => $salt
    ]);

    return $app->redirect($app->url('login'));
})->bind('register');

$app->get('/books', function (Application $app) {
    $conn = $app->getConnection();

    return $app->render('books.html.twig', [
        'books'   => $conn->query('SELECT b.*, a.name author FROM books b JOIN authors a ON a.id = b.author_id')->fetchAll(),
        'authors' => $conn->query('SELECT * FROM authors')->fetchAll()
    ]);
})->bind('books')->before($checkAuth);

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
})->before($checkAuth);

$app->post('/authors', function (Application $app, Request $request) {
    $query = $app->getConnection()->prepare('INSERT INTO authors (name) VALUES(s:name)');
    $query->execute([
        'name' => $request->get('name')
    ]);

    return $app->redirect('/books');
})->before($checkAuth);

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
})->bind('acts')->before($checkAuth);

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
})->before($checkAuth);

$app->run();
