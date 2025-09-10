<?php

namespace Tests\Unit;

use App\Util\Lexer\Bearcap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BearcapTest extends TestCase
{
    #[Test]
    public function validTest()
    {
        $str = 'bear:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&u=https://negarin.test/stories/admin/337892163734081536';
        $expected = [
            'token' => 'LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2',
            'url' => 'https://negarin.test/stories/admin/337892163734081536',
        ];
        $actual = Bearcap::decode($str);
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function invalidTokenParameterName()
    {
        $str = 'bear:?token=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&u=https://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function invalidUrlParameterName()
    {
        $str = 'bear:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&url=https://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function invalidScheme()
    {
        $str = 'bearcap:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&url=https://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function missingToken()
    {
        $str = 'bear:?u=https://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function missingUrl()
    {
        $str = 'bear:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function invalidHttpUrl()
    {
        $str = 'bear:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&u=http://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }

    #[Test]
    public function invalidUrlSchema()
    {
        $str = 'bear:?t=LpVypnEUdHhwwgXE9tTqEwrtPvmLjqYaPexqyXnVo1flSfJy5AYMCdRPiFRmqld2&u=phar://negarin.test/stories/admin/337892163734081536';
        $actual = Bearcap::decode($str);
        $this->assertFalse($actual);
    }
}







