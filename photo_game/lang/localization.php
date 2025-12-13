<?php

class Localization {
    private static $instance = null;
    private $locale;
    private $translations = [];
    private $fallbackLocale = 'pl';

    private function __construct($locale = 'pl') {
        $this->locale = $locale;
        $this->load($locale);
    }

    public static function getInstance($locale = null) {
        if (self::$instance === null) {
            self::$instance = new self($locale ?? 'pl');
        } elseif ($locale !== null && self::$instance->locale !== $locale) {
            self::$instance->setLocale($locale);
        }
        return self::$instance;
    }

    private function load($locale) {
        $file = __DIR__ . "/$locale.arb";

        if (!file_exists($file)) {
            $file = __DIR__ . "/$this->fallbackLocale.arb";
        }

        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            $this->translations = [];
            foreach ($data as $key => $value) {
                if (strpos($key, '@') !== 0) {
                    $this->translations[$key] = $value;
                }
            }
        }
    }

    public function get($key, $params = []) {
        $text = $this->translations[$key] ?? $key;

        // Jeśli params to tablica numeryczna, wyciągnij nazwy z {placeholder}
        if (!empty($params) && array_keys($params) === range(0, count($params) - 1)) {
            // Znajdź wszystkie {placeholder} w tekście
            preg_match_all('/{(\w+)}/', $text, $matches);
            if (!empty($matches[1])) {
                $placeholders = $matches[1];
                // Zmapuj wartości do placeholderów
                $namedParams = [];
                foreach ($placeholders as $index => $placeholder) {
                    if (isset($params[$index])) {
                        $namedParams[$placeholder] = $params[$index];
                    }
                }
                $params = $namedParams;
            }
        }

        // Podstaw parametry
        foreach ($params as $param => $value) {
            $text = str_replace("{{$param}}", $value, $text);
        }

        return $text;
    }

    /**
     * Określa formę liczby mnogiej dla polskiego
     */
    private function getPluralFormPL($count) {
        $count = (int)$count;

        if ($count == 1) {
            return 'one';
        }

        $mod10 = $count % 10;
        $mod100 = $count % 100;

        // 2-4, 22-24, 32-34, ... (ale nie 12-14)
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
            return 'few';
        }

        return 'other';
    }

    /**
     * Określa formę liczby mnogiej dla angielskiego
     */
    private function getPluralFormEN($count) {
        return (int)$count == 1 ? 'one' : 'other';
    }

    /**
     * Wybiera odpowiednią formę liczby mnogiej
     */
    private function getPluralForm($count) {
        switch ($this->locale) {
            case 'pl':
                return $this->getPluralFormPL($count);
            case 'en':
                return $this->getPluralFormEN($count);
            default:
                return $this->getPluralFormEN($count);
        }
    }

    public function plural($key, $count, $params = []) {
        $count = (int)$count;
        $text = $this->translations[$key] ?? $key;

        // Znajdź {count, plural, ...} z balanced braces
        $startPattern = '{count, plural,';
        $startPos = strpos($text, $startPattern);

        if ($startPos !== false) {
            // Znajdź zamykający } z uwzględnieniem zagnieżdżenia
            $openPos = $startPos; // Pozycja pierwszego {
            $depth = 1;
            $i = $startPos + 1;
            $len = strlen($text);

            while ($i < $len && $depth > 0) {
                if ($text[$i] === '{') {
                    $depth++;
                } elseif ($text[$i] === '}') {
                    $depth--;
                }
                $i++;
            }

            if ($depth === 0) {
                // Znaleziono zamykający nawias
                $fullMatch = substr($text, $startPos, $i - $startPos);
                $rulesStart = $startPos + strlen($startPattern);
                $rulesLength = $i - $rulesStart - 1; // -1 dla zamykającego }
                $rules = substr($text, $rulesStart, $rulesLength);

                // Parsuj reguły
                $parts = $this->parsePluralRules($rules);

                // Znajdź odpowiednią regułę
                $result = '';
                $pluralForm = $this->getPluralForm($count);

                // 1. Sprawdź dokładną liczbę (=0, =1, etc.)
                $exactKey = '=' . $count;
                if (isset($parts[$exactKey])) {
                    $result = $parts[$exactKey];
                }
                // 2. Sprawdź formę (one, few, other)
                elseif (isset($parts[$pluralForm])) {
                    $result = $parts[$pluralForm];
                }
                // 3. Fallback na 'other'
                elseif (isset($parts['other'])) {
                    $result = $parts['other'];
                }

                // Zamień {count} na wartość
                $result = str_replace('{count}', $count, $result);

                // Zastąp całą część pluralizacyjną wynikiem
                $text = substr_replace($text, $result, $startPos, $i - $startPos);
            }
        }

        // Obsługa parametrów numerycznych (jak w get())
        if (!empty($params) && array_keys($params) === range(0, count($params) - 1)) {
            preg_match_all('/{(\w+)}/', $text, $matches);
            if (!empty($matches[1])) {
                $placeholders = array_diff($matches[1], ['count']);
                $placeholders = array_values($placeholders);
                $namedParams = [];
                foreach ($placeholders as $index => $placeholder) {
                    if (isset($params[$index])) {
                        $namedParams[$placeholder] = $params[$index];
                    }
                }
                $params = $namedParams;
            }
        }

        // Podstaw dodatkowe parametry
        foreach ($params as $param => $value) {
            if ($param !== 'count') {
                $text = str_replace("{{$param}}", $value, $text);
            }
        }

        return $text;
    }

    /**
     * Parsuje reguły pluralizacji w formacie: =0{...} one{...} few{...} other{...}
     * Zwraca tablicę: ['=0' => '...', 'one' => '...', 'few' => '...', 'other' => '...']
     */
    private function parsePluralRules($rules) {
        $parts = [];
        $i = 0;
        $len = strlen($rules);

        while ($i < $len) {
            // Pomiń białe znaki
            while ($i < $len && in_array($rules[$i], [' ', "\t", "\n", "\r"])) {
                $i++;
            }

            if ($i >= $len) break;

            // Znajdź klucz (=0, one, few, other, zero)
            $key = '';
            if ($rules[$i] === '=') {
                // Dokładna liczba
                $key .= '=';
                $i++;
                while ($i < $len && ctype_digit($rules[$i])) {
                    $key .= $rules[$i];
                    $i++;
                }
            } else {
                // Słowo kluczowe (one, few, other, many, zero)
                while ($i < $len && ctype_alpha($rules[$i])) {
                    $key .= $rules[$i];
                    $i++;
                }
            }

            // Jeśli nie znaleziono klucza, przerwij
            if (empty($key)) {
                break;
            }

            // Pomiń białe znaki
            while ($i < $len && in_array($rules[$i], [' ', "\t", "\n", "\r"])) {
                $i++;
            }

            // Znajdź wartość w {}
            if ($i < $len && $rules[$i] === '{') {
                $i++; // Pomiń otwierający {
                $value = '';
                $depth = 1;

                while ($i < $len && $depth > 0) {
                    if ($rules[$i] === '{') {
                        $depth++;
                        $value .= $rules[$i];
                    } elseif ($rules[$i] === '}') {
                        $depth--;
                        if ($depth > 0) {
                            $value .= $rules[$i];
                        }
                    } else {
                        $value .= $rules[$i];
                    }
                    $i++;
                }

                $parts[$key] = $value;
            }
        }

        return $parts;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
        $this->load($locale);
    }

    public function getLocale() {
        return $this->locale;
    }
}

/**
 * Tłumaczenie tekstu z parametrami (wyświetla)
 * Użycie: <?php __('welcome') ?>
 * Użycie z parametrami: <?php __('hello', ['name' => 'Jan']) ?>
 */
function __($key, $params = [])
{
    echo Localization::getInstance()->get($key, $params);
}

/**
 * Tłumaczenie z pluralizacją (wyświetla)
 * Użycie: <?php ___('ratingCount', 5) ?>
 * Użycie z parametrami: <?php ___('itemsInCart', 3, ['user' => 'Jan']) ?>
 */
function ___($key, $count, $params = [])
{
    echo Localization::getInstance()->plural($key, $count, $params);
}

/**
 * Tłumaczenie tekstu z parametrami (zwraca string)
 * Użycie: $text = translate('welcome');
 */
function translate($key, $params = []): string
{
    return Localization::getInstance()->get($key, $params);
}

/**
 * Tłumaczenie z pluralizacją (zwraca string)
 * Użycie: $text = translatePlural('ratingCount', 5);
 */
function translatePlural($key, $count, $params = []): string
{
    return Localization::getInstance()->plural($key, $count, $params);
}