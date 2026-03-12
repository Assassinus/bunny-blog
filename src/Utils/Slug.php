<?php

declare(strict_types=1);

namespace App\Utils;

final class Slug
{
    private const TRANSLITERATION = [
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',
        'е' => 'e',  'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',  'и' => 'i',
        'й' => 'y',  'к' => 'k',  'л' => 'l',  'м' => 'm',  'н' => 'n',
        'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',
        'у' => 'u',  'ф' => 'f',  'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ъ' => '',  'ы' => 'y',  'ь' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
    ];

    public static function fromString(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, self::TRANSLITERATION);
        $value = preg_replace('/[^a-z0-9\s\-]/u', '', $value) ?? $value;
        $value = preg_replace('/[\s\-]+/', '-', $value) ?? $value;
        return trim($value, '-') ?: 'slug';
    }

    /**
     * @param string $value
     * @return string|null
     */
    public static function validate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || strlen($value) > 255 || !preg_match('/^[a-z0-9\-]+$/', $value)) {
            return null;
        }
        return $value;
    }
}
