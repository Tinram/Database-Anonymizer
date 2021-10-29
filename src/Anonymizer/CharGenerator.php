<?php

declare(strict_types=1);

namespace Anonymizer;

final class CharGenerator
{
    /**
        * Generate character sequences for pseudo data.
        *
        * Email, number, text, and gibberish output.
        * Alpha uppercase and fixed data output available via the methods.
        *
        * @author          Martin Latter
        * @copyright       Martin Latter 05/07/2021
        * @version         0.07
        * @license         GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link            https://github.com/Tinram/Database-Anonymizer.git
        * @package         Anonymizer
    */

    public static function generateEmail(int $iLength = 10, string $sType = 'alpha'): string
    {
        return self::generateRandomString($iLength, $sType) . '@' . self::generateRandomString(6, $sType) . '.com';
    }

    public static function generateName(int $iLength = 10, string $sType = 'alpha'): string
    {
        return self::generateRandomString($iLength, $sType);
    }

    public static function generateNumber(int $iLength = 10): int
    {
        return (int) self::generateRandomString($iLength, 'number');
    }

    public static function generateText(int $iLength = 30, string $sType = 'alpha'): string
    {
        return self::generateRandomString($iLength, $sType);
    }

    public static function generateDate(int $iLength = 10, string $sType = 'date', string $sFormat = ''): string
    {
        return self::generateRandomString($iLength, $sType, $sFormat);
    }

    public static function generateYear(int $iStart = 1900, int $iEnd = 0): int
    {
        if ($iEnd === 0)
        {
            $iEnd = (int) date('Y');
        }

        return mt_rand($iStart, $iEnd);
    }

    /**
        * Generate character string according to $sType parameter choice.
        *
        * @param   integer $iLength, length of character string to generate
        * @param   string $sType, choice of character generator
        * @return  string
    */
    private static function generateRandomString(int $iLength = 10, string $sType = 'alpha', string $sFormat = 'day'): string
    {
        $sOutput = '';
        $sConsonants = '';
        $sVowels = '';
        $sChars = '';
        $iLowNum = 0;
        $iHighNum = 0;

        switch ($sType)
        {
            case 'alpha':
            case 'alpha_lower':
            case 'text':
                $sChars = join('', range('a', 'z'));
            break;

            case 'alpha_upper':
                $sChars = join('', range('A', 'Z'));
            break;

            case 'numeric':
            case 'number':
                $sChars1 = $sChars2 = '';

                $l = $iLength - 1;

                while ($l--)
                {
                    $sChars1 .= '9';
                }

                $sChars2 = $sChars1 . '9';
                $iLowNum = (int) $sChars1;
                $iHighNum = (int) $sChars2;

            break;

            case 'gibberish':
                $sChars = join('', range('a', 'z'));
                $v = ['a','e','i','o','u'];
                $sConsonants = str_replace($v, '', $sChars);
                $sVowels = join('', $v);
            break;

            case 'date':
            case 'timestamp':
                $sStart = '1970-01-01';
                $iMin = strtotime($sStart);
                $iVal = mt_rand($iMin, time());
                $sFormat = ($sFormat === 'day') ? 'Y-m-d' : 'Y-m-d H:i:s';
                $sChars = date($sFormat, $iVal);
            break;

            case 'fixed':
            default:
                $sChars = 'x';
        }

        switch ($sType)
        {
            case 'gibberish':
                $iLength = (int) ceil($iLength * 0.5);

                while ($iLength--)
                {
                    $sOutput .= $sConsonants[mt_rand(0, strlen($sConsonants) - 1)];
                    $sOutput .= $sVowels[mt_rand(0, strlen($sVowels) - 1)];
                }

            break;

            case 'fixed':
                while ($iLength--)
                {
                    $sOutput .= $sChars; # fixed character format for speed tests - dispensing with random number generation
                }

            break;

            case 'numeric':
            case 'number':
                $iNum = mt_rand($iLowNum, $iHighNum);
                $sOutput .= (string) $iNum;

            break;

            case 'date':
                 $sOutput .= $sChars;

            break;

            default:
                while ($iLength--)
                {
                    $sOutput .= $sChars[mt_rand(0, strlen($sChars) - 1)];
                }
        }

        return $sOutput;
    }

    /**
        * Example output.
        *
        * @return  string
    */
    public static function test(): string
    {
        return join(PHP_EOL,
        [
            self::generateEmail(20, 'alpha'),
            self::generateEmail(10, 'gibberish'),
            self::generateName(15, 'gibberish'),
            self::generateText(30, 'alpha_upper'),
            self::generateText(30, 'fixed'),
            self::generateNumber(16),
            self::generateDate(20, 'date', 'day'),
            self::generateDate(20, 'date', 'timestamp'),
            self::generateYear(1970, 2021)
        ]);
    }
}
