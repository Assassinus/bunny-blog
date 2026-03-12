{extends file="layout.tpl"}
{block name="title"}Главная — Bunny Blog{/block}
{block name="content"}
<h1 class="page-title">Главная</h1>
{if $categories}
    <div class="categories-list">
        {foreach $categories as $cat}
            <section class="category-block">
                <h2 class="category-title">{$cat->category->name|escape}</h2>
                {if $cat->category->description}
                    <p class="category-desc">{$cat->category->description|escape}</p>
                {/if}
                {if $cat->articles}
                    <ul class="articles-preview">
                        {foreach $cat->articles as $art}
                            <li class="article-preview-item">
                                <a href="{$base_url}/article/{$art->slug}" class="article-link">
                                    {if $art->image}
                                        <img src="{$art->image|escape}" alt="" class="article-thumb">
                                    {/if}
                                    <span class="article-title">{$art->title|escape}</span>
                                    <span class="article-meta">{$art->publishedAt->format('d.m.Y')} · {$art->views} просмотров</span>
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                {/if}
                <a href="{$base_url}/category/{$cat->category->slug}" class="btn btn-all">Все статьи</a>
            </section>
        {/foreach}
    </div>
{else}
    <p class="empty">Пока нет категорий со статьями.</p>
{/if}
{/block}
