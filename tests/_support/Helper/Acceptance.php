<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public function hasEnquiryEntry(int $count) {
        $file = fopen(__DIR__.'/../../../enquiry.csv', 'r');
        $line = 0;
        while (!feof($file)) {
            if (fgets($file) !== false) {
                $line++;
            }
        }
        fclose($file);
        $this->assertEquals($count, $line);
    }

    public function ensureNoEnquiryEntry() {
        unlink(__DIR__.'/../../../enquiry.csv');
    }
}
