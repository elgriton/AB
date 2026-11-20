<article>
    <h3><a href="?post={$post.slug}">{$post.title}</a></h3>
    <p>{$post.excerpt}</p>
    <p>
        Published:
        <time datetime="{$post.published_at|replace:' ':'T'}">{$post.publishedLabel}</time>
        · Views: {$post.views}
    </p>
</article>