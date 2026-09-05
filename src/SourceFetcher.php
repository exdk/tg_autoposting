<?php

class SourceFetcher {
    private array $sources;

    public function __construct(array $sources) {
        $this->sources = $sources;
    }

	public function fetch(int $minutes = 10): array {
		$newsList = [];
		$cutoff = time() - ($minutes * 60);

		foreach ($this->sources as $url) {
			$rss = @simplexml_load_file($url);
			if (!$rss) continue;

			foreach ($rss->channel->item as $item) {
				$title = (string)$item->title;
				$desc  = (string)$item->description;
				$pubDate = strtotime((string)$item->pubDate);

				// если новость старше cutoff → пропускаем
				if ($pubDate < $cutoff) continue;

				$newsList[] = [
					'title' => $title,
					'text'  => $desc,
					'time'  => $pubDate
				];
			}
		}

		return $newsList;
	}

}
