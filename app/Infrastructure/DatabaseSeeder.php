<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
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
