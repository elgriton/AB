{extends file="layouts/main.tpl"}

{block name=content}
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">HOMEMADE AND DELICIOUS</span>
            <h1>
                Easy recipes<br>
                for every day
            </h1>
            <p>
                Tested recipes, seasonal ingredients, and tips for
                delicious home cooking.
            </p>
        </div>

        <div class="hero-mark" aria-hidden="true">
            <span>✦</span>
        </div>
    </section>

    {if $sections}
        {foreach $sections as $section}
            <section class="category-section">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">COLLECTION</span>
                        <h2>{$section.name}</h2>
                        <p>{$section.description}</p>
                    </div>
                    <a class="text-link" href="?category={$section.slug}">
                        Browse recipes <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="post-grid">
                    {foreach $section.posts as $post}
                        {include file="partials/post-card.tpl" post=$post headingLevel=3}
                    {/foreach}
                </div>
            </section>
        {/foreach}
    {else}
        <div class="empty-state">
            <h2>No recipes yet</h2>
            <p>There are no recipes in these collections yet.</p>
        </div>
    {/if}
{/block}