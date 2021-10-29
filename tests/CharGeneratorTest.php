<?php

declare(strict_types=1);

namespace Anonymizer;

use PHPUnit\Framework\TestCase;

require 'src/autoloader.php';

final class CharGeneratorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        echo CharGenerator::test();
        echo PHP_EOL . PHP_EOL;
    }

    public function testEmailAlpha(): void
    {
        $this->assertMatchesRegularExpression('/[a-z]+/', CharGenerator::generateEmail(20, 'alpha'));
    }

    public function testEmailGibberish(): void
    {
        $this->assertMatchesRegularExpression('/[a-z]+/', CharGenerator::generateEmail(10, 'gibberish'));
    }

    public function testNameGibberish(): void
    {
        $this->assertMatchesRegularExpression('/[a-z]+/', CharGenerator::generateName(15, 'gibberish'));
    }

    public function testAlphaUpper(): void
    {
        $this->assertMatchesRegularExpression('/[A-Z]+/', CharGenerator::generateText(30, 'alpha_upper'));
    }

    public function testFixed(): void
    {
        $this->assertMatchesRegularExpression('/x+/', CharGenerator::generateText(30, 'fixed'));
    }

    public function testInteger(): void
    {
        $this->assertStringMatchesFormat('%d', CharGenerator::generateNumber(16));
    }

    public function testInteger2(): void
    {
        $this->assertMatchesRegularExpression('/[0-9]{15}/', CharGenerator::generateNumber(16)); # 16=15 in app to prevent MySQL column overflow
    }

    public function testDay(): void
    {
        $this->assertMatchesRegularExpression('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', CharGenerator::generateDate(20, 'date', 'day'));
    }

    public function testYear(): void
    {
        $this->assertMatchesRegularExpression('/[0-9]{4}/', CharGenerator::generateYear(1900, 2030));
    }

    public function testTimestamp(): void
    {
        $this->assertMatchesRegularExpression('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}/', CharGenerator::generateDate(20, 'date', 'timestamp'));
    }
}
