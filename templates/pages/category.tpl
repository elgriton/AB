{extends file="layouts/main.tpl"}

{block name=content}
    <div class="page-intro">
        <a class="back-link" href="index.php">← Home</a>
        <span class="slash">/</span>
        <span class="eyebrow">{$category.name}</span>
        <h1>{$category.name}</h1>
        <p>{$category.description}</p>
    </div>

    <div class="toolbar">
        <span class="result-count">
            {$total}
            {if $total == 1}
                recipe
            {elseif $total > 1 && $total < 5}
                recipes
            {else}
                recipes
            {/if}
        </span>

        <form method="get" class="sort-form">
            <input type="hidden" name="category" value="{$category.slug}">
            <label for="sort">Sort by:</label>
            <select id="sort" name="sort" onchange="this.form.submit()">
                <option value="date" {if $sort == 'date'}selected{/if}>
                    Newest first
                </option>
                <option value="views" {if $sort == 'views'}selected{/if}>
                    Most popular
                </option>
            </select>
            <noscript>
                <button type="submit">Apply</button>
            </noscript>
        </form>
    </div>

    {if $posts}
        <div class="post-grid">
            {foreach $posts as $post}
                {include file="partials/post-card.tpl" post=$post headingLevel=2}
            {/foreach}
        </div>

        {if $pages > 1}
            <nav class="pagination" aria-label="Pages">
                {if $pages > 5}
                    {if $page > 1}
                        <a class="pagination-arrow" href="{$previousPageUrl}" rel="prev">← Previous</a>
                    {else}
                        <span class="pagination-arrow is-disabled" aria-disabled="true">← Previous</span>
                    {/if}
                {/if}

                {foreach $pagination as $item}
                    {if $item.current}
                        <span class="current" aria-current="page">{$item.number}</span>
                    {else}
                        <a href="{$item.url}">{$item.number}</a>
                    {/if}
                {/foreach}

                {if $pages > 5}
                    {if $page < $pages}
                        <a class="pagination-arrow" href="{$nextPageUrl}" rel="next">Next →</a>
                    {else}
                        <span class="pagination-arrow is-disabled" aria-disabled="true">Next →</span>
                    {/if}
                {/if}
            </nav>
        {/if}
    {else}
        <div class="empty-state">
            <h2>No recipes yet</h2>
            <p>В этой подборке пока нет recipes.</p>
        </div>
    {/if}
{/block}