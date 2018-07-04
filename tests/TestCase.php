<?php

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * Provide a random string
     * @param  int $n the length expected
     * @return string   A string of length $n
     */
    public function str_random($n)
    {
        $str = '';
        while (strlen($str) < $n) {
            $str .= md5(mt_rand(100000, 999999));
        }
        return substr($str, 0, $n);
    }

    public function base_path($filename = '')
    {
        return realpath(__DIR__ . '/..') . '/' . $filename;
    }

    public function see($needle, $haystack)
    {
        $this->assertTrue(0 <= stripos($haystack, $needle), "Could not find '{$needle}' in '{$haystack}'");
    }
}
