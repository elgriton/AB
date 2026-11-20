<article class="post-card">
    {if $post.imageUrl}
        <a class="card-image" href="?post={$post.slug}">
            <img src="{$post.imageUrl}" alt="{$post.title|escape:'html'}" loading="lazy">
        </a>
    {/if}

    <div class="card-content">
        <div class="card-meta">
            <time datetime="{$post.published_at|replace:' ':'T'}">{$post.publishedLabel}</time>
            <span>◉ {$post.views}</span>
        </div>
        {if $headingLevel == 2}<h2>{else}<h3>{/if}
            <a href="?post={$post.slug}">{$post.title}</a>
        {if $headingLevel == 2}</h2>{else}</h3>{/if}
        <p>{$post.excerpt}</p>
        <a class="read-link" href="?post={$post.slug}">
            View recipe <span aria-hidden="true">↗</span>
        </a>
    </div>
</article>
