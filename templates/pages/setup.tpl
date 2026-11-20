{extends file="layouts/main.tpl"}

{block name=content}
    <section class="setup-panel">
        <span class="eyebrow">FIRST-TIME SETUP</span>
        <h1>Database connection</h1>
        <p>
            Enter your MySQL details. If the database does not exist, the app will create it,
            подготовит таблицы и добавит sample recipes.
        </p>

        {if $error}
            <div class="error-message">{$error}</div>
        {/if}

        <form method="post" class="setup-form">
            <input type="hidden" name="install_database" value="1">

            <label>
                Host
                <input name="host" required value="{$values.host}">
            </label>

            <div class="form-row">
                <label>
                    Порт
                    <input
                        name="port"
                        type="number"
                        min="1"
                        max="65535"
                        required
                        value="{$values.port}"
                    >
                </label>
                <label>
                    Database name
                    <input
                        name="database"
                        required
                        pattern="[A-Za-z0-9_]+"
                        value="{$values.database}"
                    >
                </label>
            </div>

            <label>
                Username
                <input name="username" required value="{$values.username}">
            </label>

            <label>
                Password
                <input name="password" type="password" autocomplete="new-password">
            </label>

            <button class="button" type="submit">
                Connect and install
            </button>
        </form>
    </section>
{/block}
