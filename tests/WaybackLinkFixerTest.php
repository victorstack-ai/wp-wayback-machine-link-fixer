<?php

declare(strict_types=1);

namespace WaybackLinkFixer\Tests;

use PHPUnit\Framework\TestCase;
use WaybackLinkFixer\WaybackLinkFixer;

final class WaybackLinkFixerTest extends TestCase
{
    public function testItRewritesBrokenExternalLinksUsingWaybackSnapshot(): void
    {
        $httpGet = function (string $url): string {
            $this->assertStringContainsString(rawurlencode('https://dead.example.com/article'), $url);
            return '{"archived_snapshots":{"closest":{"url":"'
                . 'https://web.archive.org/web/20260101000000/https://dead.example.com/article'
                . '"}}}';
        };

        $isBroken = function (string $url): bool {
            return $url === 'https://dead.example.com/article';
        };

        $fixer = new WaybackLinkFixer($httpGet, $isBroken);
        $html = '<p>Read <a href="https://dead.example.com/article">this article</a>.</p>';
        $fixed = $fixer->rewriteBrokenLinksInHtml($html);

        $this->assertStringContainsString(
            'https://web.archive.org/web/20260101000000/https://dead.example.com/article',
            $fixed
        );
        $this->assertStringContainsString('data-wayback-fixed="1"', $fixed);
    }

    public function testItLeavesHealthyLinksUnchanged(): void
    {
        $httpGet = static function (string $url): string {
            return '';
        };

        $isBroken = static function (string $url): bool {
            return false;
        };

        $fixer = new WaybackLinkFixer($httpGet, $isBroken);
        $html = '<p><a href="https://alive.example.com/post">Alive</a></p>';

        $fixed = $fixer->rewriteBrokenLinksInHtml($html);
        $this->assertStringContainsString('https://alive.example.com/post', $fixed);
        $this->assertStringNotContainsString('data-wayback-fixed="1"', $fixed);
    }

    public function testItIgnoresNonHttpLinks(): void
    {
        $httpGet = static function (string $url): string {
            return '{"archived_snapshots":{"closest":{"url":"'
                . 'https://web.archive.org/web/20260101000000/mailto:test@example.com'
                . '"}}}';
        };

        $isBroken = static function (string $url): bool {
            return true;
        };

        $fixer = new WaybackLinkFixer($httpGet, $isBroken);
        $html = '<a href="mailto:test@example.com">Email</a>';

        $fixed = $fixer->rewriteBrokenLinksInHtml($html);
        $this->assertStringContainsString('mailto:test@example.com', $fixed);
        $this->assertStringNotContainsString('data-wayback-fixed="1"', $fixed);
    }
}
