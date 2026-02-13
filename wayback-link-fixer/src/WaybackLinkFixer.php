<?php

declare(strict_types=1);

namespace WaybackLinkFixer;

use DOMDocument;
use DOMXPath;

final class WaybackLinkFixer
{
    private string $availabilityApi;
    /** @var callable */
    private $httpGet;
    /** @var callable */
    private $isBrokenLink;

    public function __construct(
        ?callable $httpGet = null,
        ?callable $isBrokenLink = null,
        string $availabilityApi = 'https://archive.org/wayback/available?url='
    ) {
        $this->httpGet = $httpGet ?? [$this, 'defaultHttpGet'];
        $this->isBrokenLink = $isBrokenLink ?? [$this, 'defaultIsBrokenLink'];
        $this->availabilityApi = $availabilityApi;
    }

    public function registerHooks(): void
    {
        if (function_exists('add_filter')) {
            add_filter('the_content', [$this, 'rewriteBrokenLinksInHtml'], 20);
            add_filter('comment_text', [$this, 'rewriteBrokenLinksInHtml'], 20);
        }
    }

    public function rewriteBrokenLinksInHtml(string $html): string
    {
        if (trim($html) === '' || stripos($html, '<a ') === false) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if (!$loaded) {
            return $html;
        }

        $xpath = new DOMXPath($dom);
        $anchors = $xpath->query('//a[@href]');
        if ($anchors === false || $anchors->length === 0) {
            return $html;
        }

        foreach ($anchors as $anchor) {
            $href = (string) $anchor->getAttribute('href');
            if (!$this->isExternalHttpLink($href)) {
                continue;
            }

            if (!(($this->isBrokenLink)($href))) {
                continue;
            }

            $snapshotUrl = $this->getWaybackSnapshotUrl($href);
            if ($snapshotUrl !== null) {
                $anchor->setAttribute('href', $snapshotUrl);
                $anchor->setAttribute('data-wayback-fixed', '1');
            }
        }

        return (string) $dom->saveHTML();
    }

    public function getWaybackSnapshotUrl(string $url): ?string
    {
        $apiUrl = $this->availabilityApi . rawurlencode($url);
        $response = (string) (($this->httpGet)($apiUrl));
        if ($response === '') {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        $snapshotUrl = $decoded['archived_snapshots']['closest']['url'] ?? null;
        if (!is_string($snapshotUrl) || $snapshotUrl === '') {
            return null;
        }

        return $snapshotUrl;
    }

    private function isExternalHttpLink(string $href): bool
    {
        $parsed = parse_url($href);
        if (!is_array($parsed)) {
            return false;
        }

        $scheme = $parsed['scheme'] ?? '';
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            return false;
        }

        if (!function_exists('home_url')) {
            return true;
        }

        $siteHost = (string) parse_url((string) home_url('/'), PHP_URL_HOST);
        $hrefHost = (string) ($parsed['host'] ?? '');
        if ($siteHost === '' || $hrefHost === '') {
            return true;
        }

        return strcasecmp($siteHost, $hrefHost) !== 0;
    }

    private function defaultHttpGet(string $url): string
    {
        if (function_exists('wp_remote_get') && function_exists('wp_remote_retrieve_body')) {
            $response = wp_remote_get($url, ['timeout' => 8]);
            if (is_wp_error($response)) {
                return '';
            }

            return (string) wp_remote_retrieve_body($response);
        }

        $context = stream_context_create(['http' => ['timeout' => 8]]);
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return '';
        }

        return $body;
    }

    private function defaultIsBrokenLink(string $url): bool
    {
        if (function_exists('wp_remote_head') && function_exists('wp_remote_retrieve_response_code')) {
            $response = wp_remote_head($url, ['timeout' => 8, 'redirection' => 3]);
            if (is_wp_error($response)) {
                return true;
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            return $code >= 400 || $code === 0;
        }

        $headers = @get_headers($url);
        if (!is_array($headers) || count($headers) === 0) {
            return true;
        }

        if (!preg_match('/\s(\d{3})\s/', $headers[0], $matches)) {
            return true;
        }

        $code = (int) $matches[1];
        return $code >= 400;
    }
}
