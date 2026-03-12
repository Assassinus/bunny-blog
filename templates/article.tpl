{extends file="layout.tpl"}
{block name="title"}{$article->title|escape} — Bunny Blog{/block}
{block name="content"}
<article class="article-full">
    {if $article->image}
        <img src="{$article->image|escape}" alt="" class="article-image">
    {/if}
    <h1 class="article-title">{$article->title|escape}</h1>
    <div class="article-meta">
        {$article->publishedAt->format('d.m.Y')} · {$article->views} просмотров
        {if $article->categories}
            · Категории:
            {foreach $article->categories as $c name="cat"}
                <a href="{$base_url}/category/{$c->slug}">{$c->name|escape}</a>{if !$smarty.foreach.cat.last}, {/if}
            {/foreach}
        {/if}
    </div>
    {if $article->description}
        <p class="article-lead">{$article->description|escape}</p>
    {/if}
    <div class="article-body">
        {$article->body|escape|nl2br nofilter}
    </div>
</article>

{if $similar}
    <aside class="similar">
        <h2>Похожие статьи</h2>
        <ul class="similar-list">
            {foreach $similar as $art}
                <li>
                    <a href="{$base_url}/article/{$art->slug}" class="similar-link">
                        {if $art->image}
                            <img src="{$art->image|escape}" alt="" class="similar-thumb">
                        {/if}
                        <span class="similar-title">{$art->title|escape}</span>
                        <span class="similar-meta">{$art->publishedAt->format('d.m.Y')} · {$art->views} просмотров</span>
                    </a>
                </li>
            {/foreach}
        </ul>
    </aside>
{/if}
{/block}
