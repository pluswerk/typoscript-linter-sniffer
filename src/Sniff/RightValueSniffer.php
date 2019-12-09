<?php
declare(strict_types=1);

namespace Pluswerk\TypoScriptSniffer\Sniff;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Tokenizer\Token;

final class RightValueSniffer
{
    // Default ignore patterns
    private const CONSTANT_EXPRESSION = '\{\$[a-zA-Z0-9_\.]+\}';
    private const TRUE_VALUE = '1';
    private const FALSE_VALUE = '0';

    /**
     * @var array
     */
    private $knownRightValues = [];

    /**
     * @var array
     */
    private $ignorePatterns = [];

    /**
     * @var bool
     */
    private $ignoreClassNameValues = true;

    /**
     * RightValueSniffer constructor.
     *
     * @param array $ignorePatterns
     * @param bool  $ignoreClassNameValues
     */
    public function __construct(array $ignorePatterns, bool $ignoreClassNameValues)
    {
        // Add constants boolean expression to ignore patterns
        $ignorePatterns[] = self::CONSTANT_EXPRESSION;
        $ignorePatterns[] = self::TRUE_VALUE;
        $ignorePatterns[] = self::FALSE_VALUE;

        $this->ignorePatterns = $ignorePatterns;
        $this->ignoreClassNameValues = $ignoreClassNameValues;
    }

    /**
     * @param Token $token
     *
     * @return Issue|null
     */
    public function sniff(Token $token): ?Issue
    {
        if ($token->getType() !== Token::TYPE_RIGHTVALUE) {
            return null;
        }

        foreach ($this->ignorePatterns as $ignorePattern) {
            if (preg_match('/' . $ignorePattern . '/', $token->getValue())) {
                return null;
            }
        }

        if ($this->ignoreClassNameValues && class_exists($token->getValue())) {
            return null;
        }

        if (in_array($token->getValue(), $this->knownRightValues, true)) {
            return new Issue(
                $token->getLine(),
                null,
                'Duplicated value "' . $token->getValue() . '". Consider extracting it into a constant.',
                Issue::SEVERITY_WARNING,
                __CLASS__
            );
        }

        $this->knownRightValues[] = $token->getValue();

        return null;
    }
}
