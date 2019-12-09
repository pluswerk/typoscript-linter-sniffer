<?php

namespace Pluswerk\TypoScriptSniffer\Tests\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;
use Pluswerk\TypoScriptSniffer\Sniff\RepeatingRValueSniff;
use Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer;

/**
 * Class RepeatingRValueSniffTest
 * @package Pluswerk\TypoScriptLinter\Tests\Sniff
 * @covers \Pluswerk\TypoScriptSniffer\Sniff\RepeatingRValueSniff
 */
class RepeatingRValueSniffTest extends TestCase
{
    /**
     * @test
     */
    public function forEachTokenIsCheckedIfItsValueIsAlreadyInUse(): void
    {
        $parameters = [
            'ignorePatterns' => [],
            'ignoreClassNameValues' => true
        ];
        $rightValueSniffer = $this->createMock(\Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer::class);
        $file = $this->createMock(File::class);
        $config = $this->createMock(LinterConfiguration::class);

        $repeatingRightRValueSniffer = new RepeatingRValueSniff($parameters, $rightValueSniffer);

        $tokens = [
            new Token(Token::TYPE_RIGHTVALUE, 'test', 15),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 16),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 17),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 18),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 19),
        ];

        $rightValueSniffer->expects($this->exactly(5))
                          ->method('sniff')
                          ->withConsecutive(
                              ...array_map(static function ($value) {
                                  return [$value];
                              }, $tokens)
                          );

        $repeatingRightRValueSniffer->sniff($tokens, $file, $config);
    }

    /**
     * @test
     */
    public function ifAnIssueWasCreatedForATokenItIsAddedToTheFile(): void
    {
        $parameters = [
            'ignorePatterns' => [],
            'ignoreClassNameValues' => true
        ];
        $rightValueSniffer = $this->createMock(\Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer::class);
        $file = $this->createMock(File::class);
        $config = $this->createMock(LinterConfiguration::class);

        $repeatingRightRValueSniffer = new RepeatingRValueSniff($parameters, $rightValueSniffer);

        $tokens = [
            new Token(Token::TYPE_RIGHTVALUE, 'test', 15),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 16)
        ];

        $issues = [
            null,
            new Issue(
                16,
                null,
                'Duplicated value "' . $tokens[1]->getValue() . '". Consider extracting it into a constant.',
                Issue::SEVERITY_WARNING,
                \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer::class
            )
        ];

        $rightValueSniffer->expects($this->exactly(count($tokens)))
                          ->method('sniff')
                          ->withConsecutive(
                              ...array_map(static function ($value) {
                                  return [$value];
                              }, $tokens)
                          )
                          ->willReturnOnConsecutiveCalls(...$issues);

        $file->expects($this->once())->method('addIssue')->with($issues[1]);

        $repeatingRightRValueSniffer->sniff($tokens, $file, $config);
    }
}
