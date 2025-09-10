<?php

namespace Tests\Unit;

use App\Util\Lexer\Nickname;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebfingerTest extends TestCase
{
    #[Test]
    public function webfingerTest()
    {
        $expected = [
            'domain' => 'negarin.org',
            'username' => 'dansup',
        ];
        $actual = Nickname::normalizeProfileUrl('acct:dansup@negarin.org');
        $this->assertEquals($expected, $actual);

        $expected = [
            'domain' => 'negarin.org',
            'username' => 'dansup_',
        ];
        $actual = Nickname::normalizeProfileUrl('acct:dansup@negarin.org');
        $this->assertNotEquals($expected, $actual);

        $expected = [
            'domain' => 'negarin.org',
            'username' => 'dansup',
        ];
        $actual = Nickname::normalizeProfileUrl('acct:@dansup@negarin.org');
        $this->assertEquals($expected, $actual);

        $expected = [
            'domain' => 'negarin.org',
            'username' => 'dansup',
        ];
        $actual = Nickname::normalizeProfileUrl('dansup@negarin.org');
        $this->assertEquals($expected, $actual);

        $expected = [
            'domain' => 'negarin.org',
            'username' => 'dansup',
        ];
        $actual = Nickname::normalizeProfileUrl('@dansup@negarin.org');
        $this->assertEquals($expected, $actual);
    }
}







