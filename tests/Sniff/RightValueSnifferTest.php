<?php
declare(strict_types=1);

namespace Pluswerk\TypoScriptSniffer\Tests\Sniff;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;
use Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer;

/**
 * Class RightValueSnifferTest
 * @package Pluswerk\TypoScriptLinter\Tests\Sniff
 * @covers \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer
 */
final class RightValueSnifferTest extends TestCase
{
    /**
     * @var \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer
     */
    private $rightValueSniffer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rightValueSniffer = new \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer([], true);
    }

    /**
     * @test
     */
    public function ifTokenIsNotOfTypeRightValueNoIssueIsCreated(): void
    {
        $testValue = 'test-value';
        $token = new Token(Token::TYPE_CONDITION, $testValue, 15);
        $this->assertNull($this->rightValueSniffer->sniff($token));
    }

    /**
     * @test
     */
    public function ifTokenValueIsAlreadyInUseAnIssueIsCreated(): void
    {
        $testValue = 'test-value';
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 20);
        $this->rightValueSniffer->sniff($tokenA);

        $issue = new Issue(
            20,
            null,
            'Duplicated value "' . $tokenB->getValue() . '". Consider extracting it into a constant.',
            Issue::SEVERITY_WARNING,
            \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer::class
        );
        $this->assertEquals($issue, $this->rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     */
    public function ifTokenValueIsNotNotAlreadyInUseNoIssueIsCreated(): void
    {
        $testValueA = 'test-valueA';
        $testValueB = 'test-valueB';
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValueA, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValueB, 20);
        $this->rightValueSniffer->sniff($tokenA);
        $this->assertNull($this->rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     */
    public function ifTokenValueMatchesOneIgnorePatternNoIssueIsCreated(): void
    {
        $testValue = 'test-value';
        $rightValueSniffer = new \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer([$testValue], true);

        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 21);
        $rightValueSniffer->sniff($tokenA);
        $this->assertNull($rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     */
    public function ifTokenValueIsClassNameNoIssueIsCreated(): void
    {
        $testValue = self::class;
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 21);
        $this->rightValueSniffer->sniff($tokenA);
        $this->assertNull($this->rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     * @testdox If token value is class name and ignoreClassNameValues is configured false an issue is created.
     */
    public function ifTokenValueIsClassNameAndIgnoreClassNameValuesIsConfiguredFalseAnIssueIsCreated(): void
    {
        $rightValueSniffer = new \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer([], false);
        $testValue = self::class;
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 21);
        $rightValueSniffer->sniff($tokenA);

        $issue = new Issue(
            21,
            null,
            'Duplicated value "' . $testValue . '". Consider extracting it into a constant.',
            Issue::SEVERITY_WARNING,
            \Pluswerk\TypoScriptSniffer\Sniff\RightValueSniffer::class
        );
        $this->assertEquals($issue, $rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     */
    public function ifTokenValueMatchesConstantsPatternNoIssueIsCreated(): void
    {
        $testValue = '{$constants.value}';
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $this->rightValueSniffer->sniff($tokenA);
        $this->assertNull($this->rightValueSniffer->sniff($tokenB));
    }

    /**
     * @test
     * @dataProvider booleanIntegerProvider
     */
    public function ifTokenValueIsIntExpressionForBooleanNoIssueIsGenerated($testValue): void
    {
        $tokenA = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $tokenB = new Token(Token::TYPE_RIGHTVALUE, $testValue, 15);
        $this->rightValueSniffer->sniff($tokenA);
        $this->assertNull($this->rightValueSniffer->sniff($tokenB));
    }

    public function booleanIntegerProvider()
    {
        return [
            'int expression for true' => [
                'value' => '1'
            ],
            'int expression for false' => [
                'value' => '0'
            ]
        ];
    }
}
