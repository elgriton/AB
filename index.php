<?php

declare(strict_types=1);


/** Создаём таблицы и связи между постами, рубриками и тегами. Индексируем по дате, просмотрам, слагам */

final class SchemaManager
{
    public function create(PDO $pdo): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(160) NOT NULL UNIQUE,
                description TEXT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS tags (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(110) NOT NULL UNIQUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(220) NOT NULL,
                slug VARCHAR(240) NOT NULL UNIQUE,
                excerpt TEXT NOT NULL,
                body MEDIUMTEXT NOT NULL,
                image VARCHAR(500) NOT NULL DEFAULT '',
                views INT UNSIGNED NOT NULL DEFAULT 0,
                published_at DATETIME NOT NULL,
                INDEX idx_posts_published (published_at),
                INDEX idx_posts_views (views)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS post_categories (
                post_id INT UNSIGNED NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (post_id, category_id),
                CONSTRAINT fk_post_categories_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS post_tags (
                post_id INT UNSIGNED NOT NULL,
                tag_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (post_id, tag_id),
                CONSTRAINT fk_post_tags_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
    }
}



/** Заполняет пустую базу демонстрационными рубриками, статьями и тегами. */
final class DatabaseSeeder
{
    /** заполняем данными */
    public function seedIfEmpty(PDO $pdo): int
    {
        if ((int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn() > 0) {
            return 0;
        }

        $categories = [
            ['breakfast', 'Breakfast', 'A delicious start to the day: porridge, eggs, and homemade bakes.'],
            ['main-dishes', 'Main dishes', 'Comforting dishes for family lunches and dinners.'],
            ['desserts', 'Desserts', 'Homemade bakes and sweets to enjoy with tea.'],
        ];
        $posts = [
            [
                'fluffy-pancakes',
                'Fluffy cottage cheese pancakes',
                'Tender cottage cheese pancakes with golden edges, perfect for breakfast.',
                'Mix 400 g cottage cheese with an egg, 2 tablespoons sugar, and a pinch of salt. Stir in 3 tablespoons flour. Shape into small pancakes, dust with flour, and fry over medium heat for 3 minutes per side. Serve with sour cream and berries.',
                '/assets/images/food.svg',
                'breakfast',
                ['cottage-cheese', 'breakfast'],
                '2026-11-18 10:00:00',
            ],
            [
                'baked-oatmeal',
                'Baked apple oatmeal',
                'A warm breakfast that is easy to make for several servings at once.',
                'Combine 180 g oats, 250 ml milk, an egg, a chopped apple, and a teaspoon of honey. Bake in a dish for 25 minutes at 180°C. Finish with cinnamon.',
                '/assets/images/food.svg',
                'breakfast',
                ['oatmeal', 'apple'],
                '2026-11-16 09:00:00',
            ],
            [
                'spinach-omelette',
                'Spinach and cheese omelet',
                'A fluffy omelet with greens for a quick breakfast or light dinner.',
                'Whisk 3 eggs with 50 ml milk, salt, and pepper. Wilt a handful of spinach in butter, pour in the eggs, top with grated cheese, cover, and cook over low heat for 6–8 minutes.',
                '/assets/images/food.svg',
                'breakfast',
                ['eggs', 'spinach'],
                '2026-11-14 08:30:00',
            ],
            [
                'creamy-pasta',
                'Creamy pasta',
                'A quick dinner with a silky sauce, Parmesan, and fragrant garlic.',
                'Отварите 250 г пасты до состояния аль денте. На сковороде прогрейте ложку масла и зубчик чеснока, влейте 200 мл сливок. Добавьте соль, перец и тёртый сыр. Перемешайте соус с пастой и подавайте горячим.',
                '/assets/images/food.svg',
                'main-dishes',
                ['pasta', 'dinner'],
                '2026-11-19 11:00:00',
            ],
            [
                'roasted-chicken',
                'Oven-roasted chicken and vegetables',
                'A complete meal cooked on one tray with very little effort.',
                'Нарежьте картофель, морковь и сладкий перец. Смешайте с куриными бёдрами, ложкой масла, паприкой, солью и тимьяном. Запекайте 40–45 минут при 200 °C до румяной корочки.',
                '/assets/images/food.svg',
                'main-dishes',
                ['chicken', 'oven'],
                '2026-11-17 13:00:00',
            ],
            [
                'lentil-soup',
                'Fragrant red lentil soup',
                'A hearty warming soup with tomatoes and gentle spices.',
                'Обжарьте лук и морковь, добавьте чайную ложку паприки и 200 г промытой красной чечевицы. Влейте 800 мл бульона и банку протёртых томатов. Варите около 20 минут до мягкости чечевицы. Посолите и добавьте лимонный сок.',
                '/assets/images/food.svg',
                'main-dishes',
                ['lentils', 'soup'],
                '2026-11-15 12:00:00',
            ],
            [
                'apple-pie',
                'Apple cake for tea',
                'A tender homemade apple cake with cinnamon.',
                'Взбейте 2 eggs со 150 г сахара, добавьте 100 г растопленного масла, 180 г муки и чайную ложку разрыхлителя. Вмешайте 3 нарезанных яблока. Выпекайте в форме 35 минут при 180 °C. Дайте пирогу немного остыть и посыпьте корицей.',
                '/assets/images/food.svg',
                'desserts',
                ['apples', 'baking'],
                '2026-11-18 15:00:00',
            ],
            [
                'chocolate-muffins',
                'Chocolate muffins',
                'Soft cocoa muffins, perfect to take along or serve with coffee.',
                'Смешайте 200 г муки, 30 г какао, 120 г сахара и чайную ложку разрыхлителя. Отдельно соедините яйцо, 150 мл молока и 60 мл масла. Объедините смеси, разложите по формочкам и выпекайте 20 минут при 180 °C.',
                '/assets/images/food.svg',
                'desserts',
                ['chocolate', 'baking'],
                '2026-11-13 16:00:00',
            ],
            [
                'berry-yogurt',
                'No-bake berry dessert',
                'A light layered dessert with yogurt, berries, and a crunchy base.',
                'Измельчите 100 г песочного печенья и распределите по стаканам. Смешайте 250 г густого йогурта с ложкой мёда. Выложите слоями йогурт и berries, охладите 30 минут. Украсьте мятой перед подачей.',
                '/assets/images/food.svg',
                'desserts',
                ['berries', 'no-bake'],
                '2026-11-12 14:00:00',
            ],
            [
                'lemon-cake',
                'Lemon loaf cake',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'honey-cookies',
                'Honey cookies',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'chocolate-brownie',
                'Chocolate brownies',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'cottage-cheese-casserole',
                'Cottage cheese bake',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'banana-bread',
                'Banana bread',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'carrot-cake',
                'Carrot cake',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'shortbread',
                'Shortbread cookies',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'berry-panna-cotta',
                'Berry panna cotta',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'pear-crumble',
                'Pear crumble',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'tiramisu-cups',
                'Tiramisu cups',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'coconut-balls',
                'Coconut bites',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'berry-tart',
                'Seasonal berry tart',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'semolina-pudding',
                'Semolina pudding',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'crepe-curd-cream',
                'Crepes with cottage cheese cream',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'homemade-ice-cream',
                'Homemade ice cream',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'nut-meringue',
                'Nut meringues',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
            [
                'cherry-cheesecake',
                'Cherry cheesecake',
                'An easy homemade dessert made with everyday ingredients.',
                'Mix the ingredients until smooth, pour into a baking dish, and bake at 180°C until done. Let cool before serving. Garnish with berries or powdered sugar.',
                '/assets/images/food.svg',
                'desserts',
                ['dessert', 'tea-time'],
                '2026-11-11 12:00:00',
            ],
        ];

        // Связи и записи добавляются одной транзакцией, чтобы при ошибке не оставить неполный набор данных.
        $pdo->beginTransaction();
        try {
            // заполняем рубрику 
            $categoryInsert = $pdo->prepare('INSERT IGNORE INTO categories (slug, name, description) VALUES (?, ?, ?)');
            foreach ($categories as [$slug, $name, $description]) {
                $categoryInsert->execute([$slug, $name, $description]);
            }



            // заполняем посты
            $categoryIds = $pdo->query('SELECT slug, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
            $postInsert = $pdo->prepare('INSERT INTO posts (slug, title, excerpt, body, image, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $categoryLink = $pdo->prepare('INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)');
            $tagInsert = $pdo->prepare('INSERT IGNORE INTO tags (slug, name) VALUES (?, ?)');
            $tagLookup = $pdo->prepare('SELECT id FROM tags WHERE slug = ?');
            $tagLink = $pdo->prepare('INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)');

            foreach ($posts as $index => [$slug, $title, $excerpt, $body, $image, $categorySlug, $tagSlugs, $publishedAt]) {
                $postInsert->execute([$slug, $title, $excerpt, $body, $image, ($index + 1) * 17, $publishedAt]);  //  ($index + 1) * 17 - просмотры
                $postId = (int) $pdo->lastInsertId();
                // подключаем к рубрике
                $categoryLink->execute([$postId, $categoryIds[$categorySlug]]);
                foreach ($tagSlugs as $tagSlug) {
                    $tagName = ucfirst(str_replace('-', ' ', $tagSlug));
                    $tagInsert->execute([$tagSlug, $tagName]);
                    $tagLookup->execute([$tagSlug]);
                    // подключем теги
                    $tagLink->execute([$postId, (int) $tagLookup->fetchColumn()]);
                }
            }

            $pdo->commit();
            return count($posts);
        } catch (\Throwable $exception) {
            // откатываем  
            $pdo->rollBack();
            throw $exception;
        }
    }
}








// смотрим конфиг если он есть
$configPath = __DIR__ . '/config.php';
$config = [];
if (is_file($configPath)) {
    try {
        $loadedConfig = require $configPath;
        if (is_array($loadedConfig)) {
            $config = $loadedConfig;
        }
    } catch (Throwable $exception) {
        $configError = 'Не удалось прочитать config.php: ' . $exception->getMessage();
    }
}

// Подключаемся к базе. Имя базы добавляется в только по запросу.
function connectToMysql(array $settings, bool $withDatabase = true): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $settings['host'], (int) $settings['port']);
    if ($withDatabase) {
        $dsn .= ';dbname=' . $settings['database'];
    }
    return new PDO($dsn, (string) $settings['username'], (string) $settings['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

// Сохраняем настройки подключения в config.php.
function saveDatabaseConfig(string $path, array $settings): void
{
    $contents = "<?php\n// Параметры подключения к базе данных.\nreturn " . var_export($settings, true) . ";\n";
    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Не удалось записать config.php. Проверьте права на запись.');
    }
}

// Создаем таблицы заполняем данными
function prepareDatabase(PDO $pdo): int
{
    (new SchemaManager())->create($pdo);
    return (new DatabaseSeeder())->seedIfEmpty($pdo);
}

// дефолтное подключение 
$values = [
    'host' => (string) ($config['host'] ?? '127.0.0.1'),
    'port' => (string) ($config['port'] ?? '3306'),
    'database' => (string) ($config['database'] ?? 'ab_test'),
    'username' => (string) ($config['username'] ?? 'root'),
    'password' => (string) ($config['password'] ?? ''),
];

$error = $configError ?? '';
$connected = false;

// Обрабатывает отправку мастера: создаёт базу, таблицы, начальные записи и config.php.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_database'])) {
    $values = [
        'host' => trim((string) ($_POST['host'] ?? '')),
        'port' => trim((string) ($_POST['port'] ?? '')),
        'database' => trim((string) ($_POST['database'] ?? '')),
        'username' => trim((string) ($_POST['username'] ?? '')),
        'password' => (string) ($_POST['password'] ?? ''),
    ];

    $validation =  !preg_match('/^[a-zA-Z0-9.:-]+$/', $values['host'])  // имя 
                   || !ctype_digit($values['port']) // числа 
                   || (int) $values['port'] < 1
                   || (int) $values['port'] > 65535 // на дурака
                   || !preg_match('/^[a-zA-Z0-9_]+$/', $values['database']) // база
                   || $values['username'] === '';

    if ($validation) {
        $error = 'Проверьте адрес, порт, имя базы и пользователя. В имени базы разрешены латинские буквы, цифры и подчёркивание.';
    } else {
        $values['port'] = (int) $values['port'];
        // создаем базу.
        try {
            $server = connectToMysql($values, false);
            $server->exec('CREATE DATABASE IF NOT EXISTS `' . $values['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = connectToMysql($values);
            $seededCount = prepareDatabase($pdo);
            saveDatabaseConfig($configPath, $values);
            header('Location: index.php?database=ready&seeded=' . $seededCount);
            exit;
        } catch (Throwable $exception) {
            $error = 'Не удалось подготовить базу данных: ' . $exception->getMessage();
        }
    }
}

// ок 
if ($error === '' && isset($config['host'], $config['port'], $config['database'], $config['username'], $config['password'])) {
    try {
        $pdo = connectToMysql($config);
        prepareDatabase($pdo);
        $connected = true;
    } catch (Throwable $exception) {
        $error = 'Нет подключения к базе или не удалось подготовить таблицы: ' . $exception->getMessage();
    }
}


function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="ru">
    <body>
        <main>
        <?php if ($connected): ?>
            <h1>База данных подключена</h1>
            <p class="message">Соединение с MySQL работает. Таблицы и начальные данные проверены.</p>
        <?php else: ?>
            <h1>Настройка базы данных</h1>
            <p>Укажите параметры MySQL. Если базы ещё нет, она будет создана автоматически.</p>
            <?php if ($error !== ''): ?>
                <p class="message error"><?= escapeHtml($error) ?></p>
            <?php endif; ?>

            <form method="post">
                <label for="host">Сервер</label>
                <input id="host" name="host" value="<?= escapeHtml($values['host']) ?>" required>

                <label for="port">Порт</label>
                <input id="port" name="port" type="number" min="1" max="65535"
                    value="<?= escapeHtml((string) $values['port']) ?>" required>

                <label for="database">Имя базы данных</label>
                <input id="database" name="database" value="<?= escapeHtml($values['database']) ?>" required>

                <label for="username">Пользователь</label>
                <input id="username" name="username" value="<?= escapeHtml($values['username']) ?>" required>

                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" value="<?= escapeHtml($values['password']) ?>">

                <button type="submit" name="install_database" value="1">
                    Подключиться и подготовить базу
                </button>
            </form>
        <?php endif; ?>
        </main>
    </body>
</html>


