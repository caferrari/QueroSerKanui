<?php

class WordRank
{

    protected $terms;
    protected $topTerms;

    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException('Arquivo não encontrado');
        }

        $contents = file_get_contents($filename);
        preg_match_all('@[a-z]{2,}@', strtolower($contents), $words);

        $this->terms = array_shift($words);
    }

    public function getTopTerms() 
    {
        if (is_array($this->topTerms)) {
            return $this->topTerms;
        }

        $termQuantity = array_count_values($this->terms);

        $this->topTerms = array();
        foreach ($termQuantity as $term => $quantity) {
            $this->topTerms[] = array($term, $quantity);
        }

        usort($this->topTerms, function($a, $b) {
            
            // if the quantity is the same we sort by the term
            if ($a[1] == $b[1]) {
                return $a[0] > $b[0] ? 1 : -1;
            }

            return $a[1] > $b[1] ? -1 : 1;
        });

        return $this->topTerms;
    }

    public function printTopTerms()
    {
        $topTerms = $this->getTopTerms();
        foreach ($topTerms as $term) {
            echo "{$term[0]} {$term[1]}" . PHP_EOL;
        }
    }

}

$wr = new WordRank(__DIR__ . '/data.txt');

$wr->printTopTerms();