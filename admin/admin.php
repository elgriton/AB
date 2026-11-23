<?php
declare(strict_types=1);

// Дополнительная локальная админка: редактирование публикаций и добавление рубрик.

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
]);
session_start();

$config = is_file(dirname(__DIR__). '/config.php') ? require dirname(__DIR__) . '/config.php' : [];
if (!is_array($config) || !isset($config['host'], $config['port'], $config['database'], $config['username'], $config['password'])) {
    http_response_code(503);
    exit('Сначала настройте подключение к базе на главной странице.');
}

$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . ';charset=utf8mb4',
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Проверяет токен формы, чтобы другой сайт не мог незаметно отправить команду сохранения или удаления.
function verifyCsrf(): void
{
    if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Форма устарела. Обновите страницу и попробуйте снова.');
    }
}

function makeSlug(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $slug = strtolower((string) $ascii);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
    return $slug !== '' ? $slug : 'item-' . bin2hex(random_bytes(4));
}

$message = '';
if (isset($_GET['saved'])) {
    $message = 'Статья сохранена.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Статья удалена.';
} elseif (isset($_GET['category_saved'])) {
    $message = 'Рубрика добавлена.';
}

// Обрабатываем отправленные формы: создание рубрики, сохранение статьи или её удаление.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    // Создание новой рубрики; адрес формируется из введённого slug или названия.
    if ($action === 'save_category') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $slug = makeSlug(trim((string) ($_POST['slug'] ?? '')) ?: $name);
        if ($name === '') {
            $message = 'Укажите название рубрики.';
        } else {
            try {
                $statement = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
                $statement->execute([$name, $slug, $description]);
                header('Location: admin.php?page=categories&category_saved=1');
                exit;
            } catch (PDOException $exception) {
                $message = 'Не удалось добавить рубрику. Возможно, такой адрес уже используется.';
            }
        }
    // Сохраняем поля статьи и обновляем её связи с рубриками и тегами.
    } elseif ($action === 'save_post') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = makeSlug(trim((string) ($_POST['slug'] ?? '')) ?: $title);
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));
        $views = max(0, (int) ($_POST['views'] ?? 0));
        $publishedInput = trim((string) ($_POST['published_at'] ?? ''));
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['categories'] ?? [])), static fn($value) => $value > 0)));
        $tagNames = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) ($_POST['tags'] ?? ''))))));

        if ($title === '' || $excerpt === '' || $body === '' || !$categoryIds || $publishedInput === '') {
            $message = 'Заполните название, описание, текст, дату и выберите хотя бы одну рубрику.';
        } else {
            try {
                $publishedAt = (new DateTimeImmutable($publishedInput))->format('Y-m-d H:i:s');
                // Статья и связи сохраняются атомарно: при ошибке изменения будут отменены.
                $pdo->beginTransaction();
                if ($id > 0) {
                    $statement = $pdo->prepare('UPDATE posts SET title = ?, slug = ?, excerpt = ?, body = ?, image = ?, views = ?, published_at = ? WHERE id = ?');
                    $statement->execute([$title, $slug, $excerpt, $body, $image, $views, $publishedAt, $id]);
                    $checkPost = $pdo->prepare('SELECT 1 FROM posts WHERE id = ?');
                    $checkPost->execute([$id]);
                    if (!$checkPost->fetchColumn()) {
                        throw new RuntimeException('Статья не найдена.');
                    }
                    $postId = $id;
                    $pdo->prepare('DELETE FROM post_categories WHERE post_id = ?')->execute([$postId]);
                    $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?')->execute([$postId]);
                } else {
                    $statement = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, body, image, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $statement->execute([$title, $slug, $excerpt, $body, $image, $views, $publishedAt]);
                    $postId = (int) $pdo->lastInsertId();
                }

                $categoryExists = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
                $categoryLink = $pdo->prepare('INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)');
                foreach ($categoryIds as $categoryId) {
                    $categoryExists->execute([$categoryId]);
                    if ((int) $categoryExists->fetchColumn() === 0) {
                        throw new RuntimeException('Выбрана неизвестная рубрика.');
                    }
                    $categoryLink->execute([$postId, $categoryId]);
                }

                $tagInsert = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
                $tagLookup = $pdo->prepare('SELECT id FROM tags WHERE slug = ?');
                $tagLink = $pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)');
                foreach ($tagNames as $tagName) {
                    $tagSlug = makeSlug($tagName);
                    $tagInsert->execute([$tagName, $tagSlug]);
                    $tagLookup->execute([$tagSlug]);
                    $tagLink->execute([$postId, (int) $tagLookup->fetchColumn()]);
                }
                $pdo->commit();
                header('Location: admin.php?saved=1');
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = $exception->getMessage();
            }
        }
    } elseif ($action === 'delete_post') {
        $statement = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $statement->execute([(int) ($_POST['id'] ?? 0)]);
        header('Location: admin.php?deleted=1');
        exit;
    }
}

// По умолчанию показываем список статей; остальные экраны выбираются query-параметрами.
$page = (string) ($_GET['page'] ?? 'posts');
$showCategories = $page === 'categories';
$editId = (int) ($_GET['edit'] ?? 0);
$isEditingPost = !$showCategories && (isset($_GET['new']) || $editId > 0);
$categories = $pdo->query('SELECT id, name, description, slug FROM categories ORDER BY name')->fetchAll();

$post = ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'image' => '', 'views' => 0, 'published_at' => date('Y-m-d\TH:i')];
$selectedCategories = [];
$selectedTags = '';
if ($isEditingPost && $editId > 0) {
    $statement = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $statement->execute([$editId]);
    $post = $statement->fetch() ?: $post;
    $categoryStatement = $pdo->prepare('SELECT category_id FROM post_categories WHERE post_id = ?');
    $categoryStatement->execute([$editId]);
    $selectedCategories = array_map('intval', $categoryStatement->fetchAll(PDO::FETCH_COLUMN));
    $tagStatement = $pdo->prepare('SELECT t.name FROM tags t INNER JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = ? ORDER BY t.name');
    $tagStatement->execute([$editId]);
    $selectedTags = implode(', ', $tagStatement->fetchAll(PDO::FETCH_COLUMN));
}
$post['published_at'] = !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i');

$posts = $pdo->query("SELECT p.id, p.title, p.slug, p.published_at, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category_names FROM posts p LEFT JOIN post_categories pc ON pc.post_id = p.id LEFT JOIN categories c ON c.id = pc.category_id GROUP BY p.id ORDER BY p.published_at DESC, p.id DESC")->fetchAll();
$categoryCounts = $pdo->query('SELECT c.id, COUNT(DISTINCT pc.post_id) AS post_count FROM categories c LEFT JOIN post_categories pc ON pc.category_id = c.id GROUP BY c.id')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $showCategories ? 'Рубрики' : ($isEditingPost ? ($editId ? 'Редактировать статью' : 'Новая статья') : 'Статьи') ?> — Админка</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="top"><div class="top-inner"><a class="home-link" href="index.php">← На главную</a><nav><a href="admin.php" class="<?= !$showCategories ? 'active' : '' ?>">Статьи</a><a href="admin.php?page=categories" class="<?= $showCategories ? 'active' : '' ?>">Рубрики</a></nav></div></header>
<main class="admin">
    <?php if ($message): ?><p class="notice"><?= h($message) ?></p><?php endif; ?>

    <?php if ($showCategories): ?>
        <div class="page-heading"><div><h1>Рубрики</h1><p>Управление разделами блога</p></div></div>
        <div class="layout category-layout">
            <section class="panel"><h2>Все рубрики</h2><table><thead><tr><th>Название</th><th>Адрес</th><th>Статей</th></tr></thead><tbody>
            <?php foreach ($categories as $category): ?><tr><td><strong><?= h($category['name']) ?></strong><?php if ($category['description'] !== ''): ?><div class="muted"><?= h($category['description']) ?></div><?php endif; ?></td><td><code><?= h($category['slug']) ?></code></td><td><?= (int) ($categoryCounts[$category['id']] ?? 0) ?></td></tr><?php endforeach; ?>
            <?php if (!$categories): ?><tr><td colspan="3" class="empty">Рубрик пока нет.</td></tr><?php endif; ?>
            </tbody></table></section>
            <section class="panel"><h2>Добавить рубрику</h2><form method="post"><input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>"><input type="hidden" name="action" value="save_category">
                <label>Название<input name="name" maxlength="150" required></label>
                <label>Адрес (slug)<input name="slug" maxlength="160" placeholder="Создаётся из названия, если оставить пустым"></label>
                <label>Описание<textarea name="description" rows="5"></textarea></label>
                <button type="submit">Добавить рубрику</button>
            </form></section>
        </div>
    <?php elseif ($isEditingPost): ?>
        <div class="page-heading"><div><h1><?= $editId ? 'Редактировать статью' : 'Добавить статью' ?></h1><p><a href="admin.php">← К списку статей</a></p></div></div>
        <section class="panel editor-panel"><form method="post"><input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>"><input type="hidden" name="action" value="save_post"><input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
            <label>Название<input name="title" maxlength="220" required value="<?= h((string) $post['title']) ?>"></label>
            <label>Адрес (slug)<input name="slug" maxlength="240" value="<?= h((string) $post['slug']) ?>" placeholder="Создаётся из названия, если оставить пустым"></label>
            <label>Краткое описание<textarea name="excerpt" required rows="3"><?= h((string) $post['excerpt']) ?></textarea></label>
            <label>Текст статьи<textarea name="body" required rows="12"><?= h((string) $post['body']) ?></textarea></label>
            <label>Изображение (URL или путь)<input name="image" maxlength="500" value="<?= h((string) $post['image']) ?>"></label>
            <div class="form-row"><label>Дата публикации<input type="datetime-local" name="published_at" required value="<?= h((string) $post['published_at']) ?>"></label><label>Просмотры<input type="number" min="0" name="views" value="<?= (int) $post['views'] ?>"></label></div>
            <fieldset><legend>Рубрики</legend><?php foreach ($categories as $category): ?><label class="check"><input type="checkbox" name="categories[]" value="<?= (int) $category['id'] ?>" <?= in_array((int) $category['id'], $selectedCategories, true) ? 'checked' : '' ?>><?= h($category['name']) ?></label><?php endforeach; ?><?php if (!$categories): ?><p>Сначала <a href="admin.php?page=categories">добавьте рубрику</a>.</p><?php endif; ?></fieldset>
            <label>Теги <small>(через запятую)</small><input name="tags" value="<?= h($selectedTags) ?>" placeholder="Breakfast, Cottage cheese"></label>
            <button type="submit">Сохранить статью</button>
        </form></section>
    <?php else: ?>
        <div class="page-heading"><div><h1>Статьи</h1><p>Управление публикациями блога</p></div><a class="button" href="admin.php?new=1"><span aria-hidden="true">＋</span> Добавить статью</a></div>
        <section class="panel table-panel"><table><thead><tr><th>Название</th><th>Рубрики</th><th>Дата публикации</th><th class="action-col">Действия</th></tr></thead><tbody>
        <?php foreach ($posts as $item): ?><tr><td><strong><?= h($item['title']) ?></strong><div class="muted">/<?= h($item['slug']) ?>/</div></td><td><?= h((string) ($item['category_names'] ?? '—')) ?></td><td class="date-cell"><?= h(date('d.m.Y H:i', strtotime($item['published_at']))) ?></td><td class="action-cell"><a class="icon-button" href="admin.php?edit=<?= (int) $item['id'] ?>" aria-label="Редактировать: <?= h($item['title']) ?>" title="Редактировать"><span aria-hidden="true">✎</span></a><form method="post" class="inline-form" onsubmit="return confirm('Удалить статью «<?= h($item['title']) ?>»?')"><input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>"><input type="hidden" name="action" value="delete_post"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="icon-button danger" aria-label="Удалить: <?= h($item['title']) ?>" title="Удалить" type="submit"><span aria-hidden="true">×</span></button></form></td></tr><?php endforeach; ?>
        <?php if (!$posts): ?><tr><td colspan="4" class="empty">Статей пока нет. Добавьте первую публикацию.</td></tr><?php endif; ?>
        </tbody></table></section>
    <?php endif; ?>
</main>
</body>
</html>







