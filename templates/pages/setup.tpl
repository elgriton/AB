<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 32px 16px; background: #f4f6f8; color: #202832; font: 16px/1.5 Arial, sans-serif; }
        main { max-width: 560px; margin: 6vh auto; padding: 28px; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px #17212b12; }
        h1 { margin-top: 0; font-size: 24px; }
        label { display: block; margin: 14px 0 5px; font-weight: 600; }
        input { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #cbd2d9; border-radius: 6px; font: inherit; }
        button { margin-top: 20px; padding: 11px 16px; border: 0; border-radius: 6px; background: #276749; color: #fff; font: inherit; cursor: pointer; }
        .message { padding: 12px; border-radius: 6px; background: #e7f6ec; }
        .error { background: #fff0ee; color: #8c2f24; }
    </style>
</head>
<body>
<main>
    <h1>Настройка базы данных</h1>
    <p>Укажите параметры MySQL. Если базы ещё нет, она будет создана автоматически.</p>

    {if $error}
        <p class="message error">{$error}</p>
    {/if}

    <form method="post">
        <input type="hidden" name="install_database" value="1">

        <label for="host">Сервер</label>
        <input id="host" name="host" value="{$values.host}" required>

        <label for="port">Порт</label>
        <input id="port" name="port" type="number" min="1" max="65535" value="{$values.port}" required>

        <label for="database">Имя базы данных</label>
        <input id="database" name="database" value="{$values.database}" required>

        <label for="username">Пользователь</label>
        <input id="username" name="username" value="{$values.username}" required>

        <label for="password">Пароль</label>
        <input id="password" name="password" type="password" value="{$values.password}">

        <button type="submit">Подключиться и подготовить базу</button>
    </form>
</main>
</body>
</html>