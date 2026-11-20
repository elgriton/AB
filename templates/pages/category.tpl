<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} — Pinch of Flavor</title>
</head>
<body>
    <header>
        <p><a href="index.php">Pinch of Flavor</a></p>
        <p><a href="index.php">Home</a> / {$category.name}</p>
        <h1>{$category.name}</h1>
        <p>{$category.description}</p>
    </header>

    <main>
        <section aria-labelledby="recipe-list-title">
            <h2 id="recipe-list-title">Recipes in this category</h2>
            <p>Total recipes: {$total}</p>

            <form method="get">
                <input type="hidden" name="category" value="{$category.slug}">
                <label for="sort">Sort by</label>
                <select id="sort" name="sort">
                    <option value="date" {if $sort == 'date'}selected{/if}>Newest first</option>
                    <option value="views" {if $sort == 'views'}selected{/if}>Most viewed</option>
                </select>
                <button type="submit">Apply</button>
            </form>

            {if $posts}
                {foreach $posts as $post}
                    <article>
                        <h3>{$post.title}</h3>
                        <p>{$post.excerpt}</p>
                        <p>
                            Published:
                            <time datetime="{$post.published_at}">{$post.publishedLabel}</time>
                            · Views: {$post.views}
                        </p>
                    </article>
                {/foreach}
            {else}
                <p>There are no recipes in this category yet.</p>
            {/if}

            {if $hasMultiplePages}
                <nav aria-label="Category pages">
                    <p>Pages:</p>
                    <ol>
                        {foreach $pagination as $item}
                            <li>
                                {if $item.current}
                                    <span aria-current="page">{$item.number}</span>
                                {else}
                                    <a href="{$item.url}">{$item.number}</a>
                                {/if}
                            </li>
                        {/foreach}
                    </ol>
                </nav>
            {/if}
        </section>
    </main>
</body>
</html>