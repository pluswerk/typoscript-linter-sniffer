<?php
declare(strict_types=1);

namespace Pluswerk\TypoScriptSniffer\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * Class BypassFinalHook
 * @package Pluswerk\TypoScriptLinter\Hook
 * @codeCoverageIgnore
 */
final class BypassFinalHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
