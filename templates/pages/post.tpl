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
        <p>
            {if $post.categories}
                <a href="?category={$post.categories[0].slug}">← {$post.categories[0].name}</a>
            {else}
                <a href="index.php">← Home</a>
            {/if}
        </p>

        <nav aria-label="Recipe categories">
            {foreach $post.categories as $category}
                <a href="?category={$category.slug}">{$category.name}</a>
            {/foreach}
        </nav>

        <h1>{$post.title}</h1>
        <p>{$post.excerpt}</p>
        <p>
            Published:
            <time datetime="{$post.published_at}">{$post.publishedLabel}</time>
            · Views: {$post.views}
        </p>
    </header>

    <main>
        <article>
            <h2>Recipe</h2>
            <p>{$post.body}</p>

            {if $post.tags}
                <footer>
                    <h2>Tags</h2>
                    <ul>
                        {foreach $post.tags as $tag}
                            <li>#{$tag.name}</li>
                        {/foreach}
                    </ul>
                </footer>
            {/if}
        </article>

        {if $similarPosts}
            <section aria-labelledby="related-title">
                <h2 id="related-title">Related recipes</h2>
                {foreach $similarPosts as $post}
                    {include file="partials/post-summary.tpl" post=$post}
                {/foreach}
            </section>
        {/if}
    </main>
</body>
</html>