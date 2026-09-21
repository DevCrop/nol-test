<?php
namespace Security;

final class HtmlSanitizer
{
    private const TAGS = ['p','br','strong','b','em','i','u','s','ul','ol','li','blockquote','h1','h2','h3','h4','h5','h6','div','span','figure','figcaption','table','thead','tbody','tr','th','td','a','img'];
    private const ATTRS = ['a'=>['href','target','rel','title'],'img'=>['src','alt','title','width','height','loading'],'th'=>['colspan','rowspan','scope'],'td'=>['colspan','rowspan']];

    public static function clean(string $html): string
    {
        if ($html === '') return '';
        $html = htmlspecialchars_decode($html, ENT_QUOTES | ENT_HTML5);
        if (!class_exists('DOMDocument')) return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $previous = libxml_use_internal_errors(true); $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="blue-safe-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors(); libxml_use_internal_errors($previous); $root = $dom->getElementById('blue-safe-root');
        if (!$root) return '';
        self::sanitizeChildren($root);
        $out = ''; foreach ($root->childNodes as $child) $out .= $dom->saveHTML($child); return $out;
    }

    private static function sanitizeChildren(\DOMNode $parent): void
    {
        $children = []; foreach ($parent->childNodes as $child) $children[] = $child;
        foreach ($children as $node) {
            if (!$node instanceof \DOMElement) { self::sanitizeChildren($node); continue; }
            $tag = strtolower($node->tagName);
            if (!in_array($tag, self::TAGS, true)) { $node->parentNode->removeChild($node); continue; }
            $attrs = []; foreach ($node->attributes as $attr) $attrs[] = $attr;
            foreach ($attrs as $attr) {
                $name = strtolower($attr->nodeName); $value = trim((string) $attr->nodeValue);
                if (strpos($name, 'on') === 0 || !in_array($name, self::ATTRS[$tag] ?? [], true)) { $node->removeAttributeNode($attr); continue; }
                if (($name === 'href' || $name === 'src') && !self::safeUrl($value, $name === 'href')) $node->removeAttribute($name);
                if ($name === 'target' && !in_array(strtolower($value), ['_self','_blank'], true)) $node->setAttribute('target', '_self');
            }
            if ($tag === 'a' && strtolower($node->getAttribute('target')) === '_blank') $node->setAttribute('rel', 'noopener noreferrer');
            self::sanitizeChildren($node);
        }
    }

    private static function safeUrl(string $url, bool $links): bool
    {
        $url = preg_replace('/[\x00-\x20\x7f]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '' || preg_match('/^(\/|#|\?)/', $url)) return true;
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, $links ? ['http','https','mailto','tel'] : ['http','https'], true);
    }
}

