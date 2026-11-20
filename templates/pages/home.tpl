<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} — Pinch of Flavor</title>
</head>
<body>
    <header>
        <p>Pinch of Flavor</p>
        <h1>Recipes for every day</h1>
        <p>Collections and recent recipes loaded from the database.</p>
    </header>

    <main>
        {if $sections}
            {foreach $sections as $section}
                <section>
                    <header>
                        <h2>{$section.name}</h2>
                        <p>{$section.description}</p>
                        <p><small>Category slug: {$section.slug}</small></p>
                    </header>

                    {if $section.posts}
                        {foreach $section.posts as $post}
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
                        <p>No recipes in this category yet.</p>
                    {/if}
                </section>
            {/foreach}
        {else}
            <section>
                <h2>No recipes yet</h2>
                <p>There are no categories with published recipes.</p>
            </section>
        {/if}
    </main>
</body>
</html>