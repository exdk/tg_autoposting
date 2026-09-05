<?php

class TextProcessor {

    /**
     * Делает саммари новости
     * @param string $title
     * @param string $text
     * @return string|null
     */
    public function summarize(string $title, string $text): ?string {
        $title = trim(strip_tags($title));
        $desc  = trim(strip_tags($text));

        $sentences = $this->splitSentences($desc);

        // Если заголовок обрывается тире/многоточием → заменяем на первую фразу description
        if (preg_match('/(—|…|-)$/u', $title)) {
            if (!empty($sentences[0]) && mb_strlen($sentences[0]) > 20) {
                $title = ucfirst(trim($sentences[0]));
            }
        }

        // Формируем саммари: заголовок + первая фраза
        $summary = $title;
        if (!empty($sentences[0]) && mb_strlen($sentences[0]) > 20) {
            $summary .= " — " . trim($sentences[0]);
        }

        // Ограничение длины
        $summary = mb_substr($summary, 0, 300);

        // Очистка хвостов и мусора
        $summary = $this->cleanSummary($summary);

        // Фильтр мусора: слишком короткие или пустые новости
        if (mb_strlen($summary) < 15) {
            return null;
        }

        return $summary;
    }

    /**
     * Создаёт хэш новости для дедупа
     */
    public function makeHash(string $text): string {
        $norm = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
        return md5($norm);
    }

    /**
     * Проверяет, есть ли дубликат среди последних новостей
     */
    public function isDuplicate(string $text, array $recentSummaries): bool {
        foreach ($recentSummaries as $old) {
            similar_text($text, $old, $percent);
            if ($percent > 80) {
                return true;
            }
        }
        return false;
    }

    /**
     * Чистка хвостовых тире, многоточий и кликабельных «Подробнее»
     */
    private function cleanSummary(string $text): string {
        $text = preg_replace('/\s+—\s*$/u', '', $text);
        $text = preg_replace('/\s+-\s*$/u', '', $text);
        $text = preg_replace('/\.\.\.$/u', '', $text);
        $text = preg_replace('/Подробнее.*$/ui', '', $text);

        return trim($text);
    }

    /**
     * Умный разбор предложений
     * Точка + пробел + строчная → не разрываем
     */
    private function splitSentences(string $text): array {
        $text = strip_tags($text);
        $sentences = [];

        // Разделяем точка/воскл/вопрос + пробел + заглавная буква (не разрываем после строчной)
        $pattern = '/(?<=[.!?])\s+(?=\p{Lu})/u';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $sentences[] = $part;
            }
        }

        // Если ничего не получилось, возвращаем весь текст как одно "предложение"
        if (empty($sentences)) {
            $sentences[] = trim($text);
        }

        return $sentences;
    }
}
