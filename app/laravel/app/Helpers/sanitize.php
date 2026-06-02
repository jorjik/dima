<?php

/**
 * Sanitize HTML output: strip script tags, dangerous tags, on* event handlers,
 * and javascript:/vbscript: URLs.
 * Defense-in-depth for XSS prevention in body_markdown output.
 */
if (! function_exists('sanitizeHtml')) {
    function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<\s*(iframe|object|embed|form|meta|link|svg)\b[^>]*>/i', '', $html);
        $html = preg_replace('/<\s*\/(iframe|object|embed|form|meta|link|svg)\s*>/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $html = preg_replace('/vbscript\s*:/i', '', $html);
        return $html;
    }
}

/**
 * Add loading="lazy" to all <img> tags that don't already have loading attribute.
 * RichEditor attachments and markdown images load lazily to save bandwidth.
 */
if (! function_exists('addLazyLoading')) {
    function addLazyLoading(string $html): string
    {
        return preg_replace('/<img\s+(?![^>]*\bloading=)/i', '<img loading="lazy" ', $html);
    }
}
