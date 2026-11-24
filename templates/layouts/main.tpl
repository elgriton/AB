<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="description" content="{$metaDescription|default:'Pinch of Flavor recipes'|escape:'html'}">

    <title>{$metaTitle|default:$title|escape:'html'} — Pinch of Flavor</title>
    {if $canonicalUrl}<link rel="canonical" href="{$canonicalUrl|escape:'html'}">{/if}
    <meta property="og:type" content="{$ogType|default:'website'|escape:'html'}">
    <meta property="og:title" content="{$metaTitle|default:$title|escape:'html'}">
    <meta property="og:description" content="{$metaDescription|default:'Pinch of Flavor recipes'|escape:'html'}">
    {if $canonicalUrl}<meta property="og:url" content="{$canonicalUrl|escape:'html'}">{/if}
    <meta property="og:site_name" content="Pinch of Flavor">
    {if $ogImageUrl}<meta property="og:image" content="{$ogImageUrl|escape:'html'}">{/if}
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/site.css?v=scss-20261124">
    <link rel="stylesheet" href="/assets/css/site-desktop.css?v=scss-20261124" media="(min-width: 581px)">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="/index.php">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>
                    Pinch of Flavor
                    <small>HOME COOKING RECIPES</small>
                </span>
            </a>

            <nav class="main-nav">
                <a href="/index.php">Home</a>
                {foreach $navigation as $item}
                    <a href="?category={$item.slug}">{$item.name}</a>
                {/foreach}
            </nav>
        </div>
    </header>

    <main class="site-main">
        {block name=content}{/block}
    </main>

    <footer class="site-footer">
        <div>
            <span>Pinch of Flavor</span>
            <span>Recipes worth making again</span>
        </div>
    </footer>
</body>
</html>
