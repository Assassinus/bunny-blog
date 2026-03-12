{extends file="layout.tpl"}
{block name="title"}{if $error_code == 404}Страница не найдена{else}Ошибка сервера{/if} — Bunny Blog{/block}
{block name="content"}
<div class="error-page">
    <h1>{if $error_code == 404}404 — Страница не найдена{else}500 — Ошибка сервера{/if}</h1>
    <p>{$message|escape}</p>
    <a href="{$base_url}/" class="btn">На главную</a>
</div>
{/block}
