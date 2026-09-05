<?php

// простой автолоадер
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/src/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

$config = require __DIR__ . '/config.php';

$db = new Database($config['db']);
$fetcher = new SourceFetcher($config['sources']);
$processor = new TextProcessor();
$poster = new TelegramPoster($config['telegram']['token'], $config['telegram']['chat_id']);

$newsList = $fetcher->fetch();

foreach ($newsList as $news) {
    $summary = $processor->summarize($news['title'], $news['text']);
	if ($summary === null) continue; // пропускаем мусорные новости

    $hash = $processor->makeHash($summary);

    if ($db->newsExists($hash)) continue;

    $recent = $db->getRecentNews();
    if ($processor->isDuplicate($summary, $recent)) continue;

    $db->insertNews($news['title'], $summary, $hash);
    $poster->post($summary);

    // чтобы не получить 429 от Telegram
    sleep(2.2);
}

