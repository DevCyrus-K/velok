<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MessageText
{
    public static function decode(?string $value): string
    {
        $text = str_replace("\0", '', (string) $value);

        if ($text === '') {
            return '';
        }

        $decoded = @iconv_mime_decode($text, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        if (is_string($decoded) && $decoded !== '') {
            $text = $decoded;
        } else {
            $decoded = @mb_decode_mimeheader($text);

            if (is_string($decoded) && $decoded !== '') {
                $text = $decoded;
            }
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (str_contains($text, '=0A') || str_contains($text, '=0D') || str_contains($text, '=3D')) {
            $text = quoted_printable_decode($text);
        }

        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    public static function plain(?string $value, bool $squish = true): string
    {
        $plain = trim(strip_tags(self::decode($value)));

        return $squish ? (string) Str::of($plain)->squish() : $plain;
    }

    public static function limit(?string $value, int $limit, string $end = '...'): string
    {
        return Str::limit(self::plain($value), $limit, $end);
    }

    public static function preview(?string $body): string
    {
        return self::limit($body, 80);
    }

    public static function subject(?string $subject): string
    {
        return self::limit($subject, 40);
    }

    public static function email(?string $email, int $max = 25): string
    {
        $email = Str::lower(self::plain($email));

        if ($email === '' || mb_strlen($email) <= $max || ! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $domain = '@'.$domain;
        $domainLength = mb_strlen($domain);

        if ($domainLength >= $max - 3) {
            return '...'.mb_substr($domain, -($max - 3));
        }

        $localLength = max(1, $max - $domainLength - 3);

        return mb_substr($local, 0, $localLength).'...'.$domain;
    }

    public static function safeBody(?string $body): HtmlString
    {
        $decoded = self::decode($body);

        if (self::containsHtml($decoded)) {
            return new HtmlString(self::sanitizeHtml($decoded));
        }

        return new HtmlString(nl2br(e($decoded), false));
    }

    public static function containsHtml(?string $value): bool
    {
        $value = (string) $value;

        return $value !== strip_tags($value);
    }

    public static function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link)\b[^>]*>.*?<\/\1>/is', '', $html) ?? '';
        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|textarea|select|meta|link)\b[^>]*\/?>/is', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><a><table><thead><tbody><tr><td><th><hr><pre><code><span><div>');

        if (! class_exists(\DOMDocument::class)) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'td' => ['colspan', 'rowspan'],
            'th' => ['colspan', 'rowspan'],
        ];

        foreach ($document->getElementsByTagName('*') as $node) {
            $tag = strtolower($node->nodeName);

            if (! $node->hasAttributes()) {
                continue;
            }

            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = trim((string) $attribute->nodeValue);
                $allowed = in_array($name, $allowedAttributes[$tag] ?? [], true);

                if (! $allowed || str_starts_with($name, 'on') || self::hasUnsafeUrl($name, $value)) {
                    $node->removeAttribute($name);
                }
            }

            if ($tag === 'a') {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $root = $document->getElementsByTagName('div')->item(0);
        $safe = '';

        if ($root) {
            foreach ($root->childNodes as $child) {
                $safe .= $document->saveHTML($child);
            }
        }

        return $safe !== '' ? $safe : e(self::plain($html, false));
    }

    private static function hasUnsafeUrl(string $attribute, string $value): bool
    {
        if (! in_array($attribute, ['href', 'src'], true)) {
            return false;
        }

        return preg_match('/^\s*(javascript|data|vbscript):/i', $value) === 1;
    }
}
