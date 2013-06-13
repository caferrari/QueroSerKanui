<?php

class DisplayDigit
{

    protected $led = 0;

    public function addBit($bit)
    {
        $this->led |= $bit;
    }

    public function getString()
    {

        $digits = array(
            63 => '0',
            18 => '1',
            79 => '2',
            91 => '3',
            114 => '4',
            121 => '5',
            125 => '6',
            19 => '7',
            127 => '8',
            123 => '9'
        );

        if (!isset($digits[$this->led])) {
            throw new \RuntimeException('Invalid Digit: ' . decbin($this->led));
        }

        return $digits[$this->led];
    }
}

class DisplayLine
{

    private $digits = array();
    private $processedLines = 0;
    private $error = false;

    public function __construct()
    {
        for ($x = 0; $x < 9; $x++) {
            $this->digits[$x] = new DisplayDigit;
        }
    }

    public function addLine($line)
    {

        if ($this->error) {
            return;
        }

        $line = str_replace("\n", '', $line);

        $line = str_pad($line, 27, " ", STR_PAD_RIGHT);
        $line = substr($line, 0, 27);

        try {
            $this->processedLines++;
            if (1 == $this->processedLines) {
                return $this->parseSegment1($line);
            }

            if (2 == $this->processedLines) {
                return $this->parseSegment267($line);
            }

            if (3 == $this->processedLines) {
                return $this->parseSegment345($line);
            }
        } catch (Exception $e) {
            $this->error = true;
        }


    }

    protected function parseSegment1($line)
    {
        $segments = str_split($line, 3);

        foreach ($segments as $digit => $segment) {
            if (!preg_match('@^ [_ ] $@', $segment)) {
                throw new RuntimeException("Invalid Segment: ($segment)");
            }
            if ($segment[1] == '_') {
                $this->digits[$digit]->addBit(1);
            }
        }
    }

    protected function parseSegment267($line)
    {

        $segments = str_split($line, 3);
        foreach ($segments as $digit => $segment) {

            if (!preg_match('@^[| ][_ ][| ]$@', $segment)) {
                throw new RuntimeException('Invalid Segment');
            }

            if ($segment[0] == '|') {
                $this->digits[$digit]->addBit(32);
            }

            if ($segment[1] == '_') {
                $this->digits[$digit]->addBit(64);
            }

            if ($segment[2] == '|') {
                $this->digits[$digit]->addBit(2);
            }
        }
    }

    protected function parseSegment345($line)
    {

        $segments = str_split($line, 3);
        foreach ($segments as $digit => $segment) {

            if (!preg_match('@^[| ][_ ][| ]$@', $segment)) {
                throw new RuntimeException('Invalid Segment');
            }

            if ($segment[0] == '|') {
                $this->digits[$digit]->addBit(4);
            }

            if ($segment[1] == '_') {
                $this->digits[$digit]->addBit(8);
            }

            if ($segment[2] == '|') {
                $this->digits[$digit]->addBit(16);
            }
        }
    }

    public function getString()
    {

        if ($this->error) {
            return "/!\\erro de formato/!\\";
        }

        $number = '';
        try {
            foreach ($this->digits as $digit) {
                $number .= $digit->getString();
            }
        } catch (Exception $e) {
            return "/!\\erro de formato/!\\";
        }


        return $number;
    }

}

class DisplayParser
{

    private $contents;

    private $displayLines = array();

    public function __construct($filename)
    {
        $this->contents = file($filename);
    }

    public function parse()
    {

        $this->displayLines[] = new DisplayLine;

        $c = 0;
        foreach ($this->contents as $content) {

            if (++$c == 4) {
                $c = 0;
                $this->displayLines[] = new DisplayLine;
                continue;
            }

            $displayLine = end($this->displayLines);

            $displayLine->addLine($content);

        }

    }

    public function getStrings() {
        $string = '';
        foreach ($this->displayLines as $line) {
            $string .= $line->getString() . PHP_EOL;
        }
        return $string;
    }


}


$parser = new DisplayParser(__DIR__ . '/data.txt');
$parser->parse();

echo $parser->getStrings();