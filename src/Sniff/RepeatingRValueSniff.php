<?php
declare(strict_types=1);

namespace Pluswerk\TypoScriptSniffer\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\TokenStreamSniffInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer;

final class RepeatingRValueSniff implements TokenStreamSniffInterface
{
    /**
     * @var RightValueSniffer
     */
    private $rightValueSniffer;

    /**
     * @param array                  $parameters
     * @param RightValueSniffer|null $rightValueSniffer
     */
    public function __construct(array $parameters, RightValueSniffer $rightValueSniffer = null)
    {
        $this->rightValueSniffer = $rightValueSniffer ?? new RightValueSniffer(
            $parameters['ignorePatterns'] ?? [],
            $parameters['ignoreClassNameValues'] ?? false
        );
    }

    /**
     * @param TokenInterface[]    $tokens
     * @param File                $file
     * @param LinterConfiguration $configuration
     *
     * @return void
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration): void
    {
        foreach ($tokens as $token) {
            $issue = $this->rightValueSniffer->sniff($token);
            if ($issue instanceof Issue) {
                $file->addIssue($issue);
            }
        }
    }
}
