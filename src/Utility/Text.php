<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace TikuwaApp\Utility;

use InvalidArgumentException;

/**
 * Text handling methods.
 */
class Text
{
    /**
     * Default transliterator.
     *
     * @var \Transliterator Transliterator instance.
     */
    protected static $_defaultTransliterator;

    /**
     * Default transliterator id string.
     *
     * @var string $_defaultTransliteratorId Transliterator identifier string.
     */
    protected static $_defaultTransliteratorId = 'Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove';

    /**
     * Default html tags who must not be count for truncate text.
     *
     * @var array
     */
    protected static $_defaultHtmlNoCount = [
        'style',
        'script',
    ];

    /**
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
     * $leftBound and $rightBound.
     *
     * @param string $data The data to tokenize.
     * @param string $separator The token to split the data on.
     * @param string $leftBound The left boundary to ignore separators in.
     * @param string $rightBound The right boundary to ignore separators in.
     * @return string|string[] Array of tokens in $data or original input if empty.
     */
    public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')')
    {
        if (empty($data)) {
            return [];
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = [];
        $length = mb_strlen($data);
        $open = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets = [
                mb_strpos($data, $separator, $offset),
                mb_strpos($data, $leftBound, $offset),
                mb_strpos($data, $rightBound, $offset),
            ];
            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1) {
                $buffer .= mb_substr($data, $offset, $tmpOffset - $offset);
                $char = mb_substr($data, $tmpOffset, 1);
                if (!$depth && $char === $separator) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $char;
                }
                if ($leftBound !== $rightBound) {
                    if ($char === $leftBound) {
                        $depth++;
                    }
                    if ($char === $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($char === $leftBound) {
                        if (!$open) {
                            $depth++;
                            $open = true;
                        } else {
                            $depth--;
                            $open = false;
                        }
                    }
                }
                $tmpOffset += 1;
                $offset = $tmpOffset;
            } else {
                $results[] = $buffer . mb_substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) && !empty($buffer)) {
            $results[] = $buffer;
        }

        if (!empty($results)) {
            return array_map('trim', $results);
        }

        return [];
    }
}
