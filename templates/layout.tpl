{* Base layout *}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{block name="title"}Блог{/block}</title>
    <link rel="stylesheet" href="{$base_url}/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="{$base_url}/" class="logo">Bunny Blog</a>
        </div>
    </header>
    <main class="main">
        <div class="container">
            {block name="content"}{/block}
        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {$smarty.now|date_format:"%Y"} Bunny Blog</p>
        </div>
    </footer>
</body>
</html>
