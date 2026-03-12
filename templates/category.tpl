{extends file="layout.tpl"}
{block name="title"}{$category->name|escape} — Bunny Blog{/block}
{block name="content"}
<h1 class="page-title">{$category->name|escape}</h1>
{if $category->description}
    <p class="category-desc">{$category->description|escape}</p>
{/if}

<div class="toolbar">
    <span class="total">Всего статей: {$paginator->totalItems}</span>
    <div class="sort">
        <span>Сортировка:</span>
        <a href="?sort=date{if $paginator->currentPage > 1}&page={$paginator->currentPage}{/if}" class="{if $sort == 'date'}active{/if}">по дате</a>
        <a href="?sort=views{if $paginator->currentPage > 1}&page={$paginator->currentPage}{/if}" class="{if $sort == 'views'}active{/if}">по просмотрам</a>
    </div>
</div>

{if $articles}
    <ul class="articles-list">
        {foreach $articles as $art}
            <li class="article-card">
                <a href="{$base_url}/article/{$art->slug}" class="article-link">
                    {if $art->image}
                        <img src="{$art->image|escape}" alt="" class="article-thumb">
                    {/if}
                    <div class="article-info">
                        <h3 class="article-title">{$art->title|escape}</h3>
                        {if $art->description}
                            <p class="article-desc">{$art->description|escape}</p>
                        {/if}
                        <span class="article-meta">{$art->publishedAt->format('d.m.Y')} · {$art->views} просмотров</span>
                    </div>
                </a>
            </li>
        {/foreach}
    </ul>

    {if $paginator->getTotalPages() > 1}
        <nav class="pagination">
            {if $paginator->hasPrev()}
                <a href="?sort={$sort}&page={$paginator->getPrevPage()}" class="page-link page-prev">←</a>
            {/if}
            {foreach $paginator->getPages() as $p}
                {if $p === null}
                    <span class="page-ellipsis">…</span>
                {elseif $p === $paginator->currentPage}
                    <span class="page-link page-current">{$p}</span>
                {else}
                    <a href="?sort={$sort}&page={$p}" class="page-link">{$p}</a>
                {/if}
            {/foreach}
            {if $paginator->hasNext()}
                <a href="?sort={$sort}&page={$paginator->getNextPage()}" class="page-link page-next">→</a>
            {/if}
        </nav>
    {/if}
{else}
    <p class="empty">В этой категории пока нет статей.</p>
{/if}
{/block}
