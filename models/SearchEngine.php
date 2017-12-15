<?php
/**
 * Created by PhpStorm.
 * User: wsst17
 * Date: 15.12.17
 * Time: 9:16
 */

namespace app\models;


class SearchEngine
{
    public $VERSION = "1.0.0";
    private $morphyus;

    public function __construct()
    {
        $this->morphyus = new Morphyus();
    }


    /**
     * Выполняет индексирование текста
     *
     * @param $content Текст для индексирования
     * @param int $range Коэффициент значимости индексируемых данных
     * @return \stdClass Результат индексирования
     */
    public function makeIndex($content, $range = 1)
    {
        $index = new \stdClass();
        $index->range = $range;
        $index->words = [];

        // Выделение слов из текста //
        $words = $this->morphyus->getWords($content);

        foreach ($words as $word) {
            // Оценка значимости слова //
            $weight = $this->morphyus->weigh($word);

            if ($weight > 0) {
                // Количество слов в индексе //
                $length = count($index->words);

                // Проверка существования исходного слова в индексе //
                for ($i = 0; $i < $length; $i++) {
                    if ($index->words[$i]->source === $word) {
                        // Исходное слово уже есть в индексе //
                        $index->words[$i]->count++;
                        $index->words[$i]->range =
                            $range * $index->words[$i]->count * $index->words[$i]->weight;

                        // Обработка следующего слова //
                        continue 2;
                    }
                }

                // Если исходного слова еще нет в индексе //
                $lemma = $this->morphyus->lemmatize($word);

                if ($lemma) {
                    // Проверка наличия лемм в индексе //
                    for ($i = 0; $i < $length; $i++) {
                        // Если у сравниваемого слова есть леммы //
                        if ($index->words[$i]->basic) {
                            $difference = count(
                                array_diff($lemma, $index->words[$i]->basic)
                            );

                            // Если сравниваемое слово имеет менее двух отличных лемм //
                            if ($difference === 0) {
                                $index->words[$i]->count++;
                                $index->words[$i]->range =
                                    $range * $index->words[$i]->count * $index->words[$i]->weight;

                                // Обработка следующего слова //
                                continue 2;
                            }
                        }
                    }
                }

                // Если в индексе нет ни лемм, ни исходного слова, //
                // значит пора добавить его //
                $node = new \stdClass();
                $node->source = $word;
                $node->count = 1;
                $node->range = $range * $weight;
                $node->weight = $weight;
                $node->basic = $lemma;

                $index->words[] = $node;
            }
        }


        return $index;
    }

    /**
     * @param $target   Искомые данные
     * @param $index    Данные, в которых выполняется поиск
     * @return int      Суммарный ранг на основе найденных данных
     */
    public function search($target, $index)
    {
        $total_range = 0;

        // Перебор слов запроса //
        foreach ($target->words as $target_word) {
            // Перебор слов индекса //
            foreach ($index->words as $index_word) {
                if ($index_word->source === $target_word->source) {
                    $total_range += $index_word->range;
                } else if ($index_word->basic && $target_word->basic) {
                    // Если у искомого и индексированного слов есть леммы //
                    $index_count = count($index_word->basic);
                    $target_count = count($target_word->basic);

                    for ($i = 0; $i < $target_count; $i++) {
                        for ($j = 0; $j < $index_count; $j++) {
                            if ($index_word->basic[$j] === $target_word->basic[$i]) {
                                $total_range += $index_word->range;
                                continue 2;
                            }
                        }
                    }
                }
            }
        }

        return $total_range;
    }
}