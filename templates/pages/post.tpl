{extends file="layouts/main.tpl"}

{block name=content}
    <article class="article-page">
        <a class="back-link" href="{if $post.categories}?category={$post.categories[0].slug}{else}index.php{/if}">
            ← {if $post.categories}{$post.categories[0].name}{else}Home{/if}
        </a>

        <header class="article-heading">
            <div class="article-categories">
                {foreach $post.categories as $category}
                    <a href="?category={$category.slug}">{$category.name}</a>
                {/foreach}
            </div>

            <h1>{$post.title}</h1>
            <p class="article-lead">{$post.excerpt}</p>
            <div class="article-meta">
                <time datetime="{$post.published_at|replace:' ':'T'}">{$post.publishedLabel}</time>
                <span>Views: {$post.views}</span>
            </div>
        </header>

        {if $post.imageUrl}
            <figure class="article-cover">
                <img src="{$post.imageUrl}" alt="{$post.title|escape:'html'}">
            </figure>
        {/if}

        <div class="article-body">{$post.body}</div>

        {if $post.tags}
            <div class="tag-list">
                {foreach $post.tags as $tag}
                    <span>#{$tag.name}</span>
                {/foreach}
            </div>
        {/if}
    </article>

    {if $similarPosts}
        <section class="similar-section">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">MORE IDEAS FOR YOUR MENU</span>
                    <h2>Related recipes</h2>
                </div>
            </div>

            <div class="post-grid">
                {foreach $similarPosts as $similar}
                    {include file="partials/post-card.tpl" post=$similar headingLevel=3}
                {/foreach}
            </div>
        </section>
    {/if}
{/block}
