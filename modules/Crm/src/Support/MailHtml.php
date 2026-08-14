<?php

namespace Codovision\Crm\Support;

class MailHtml
{
    public const ALLOWED_TAGS = '<p><br><br/><div><span><a><b><strong><i><em><u><s><strike><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><td><th><img><hr><font>';

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);

        // Drop risky attributes / protocols while keeping basic formatting.
        $clean = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;
        $clean = preg_replace('/data\s*:/i', '', $clean) ?? $clean;

        return $clean;
    }

    public static function toText(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    public static function isEmpty(?string $html): bool
    {
        return self::toText($html) === '';
    }
}
