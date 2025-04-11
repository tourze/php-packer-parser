<?php

namespace PhpPacker\Parser\Tests\Unit\Exception;

use PhpPacker\Parser\Exception\LoaderException;
use PhpPacker\Parser\Exception\ParserException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testLoaderException()
    {
        // 测试基本异常
        $exception = new LoaderException('测试加载器异常');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('测试加载器异常', $exception->getMessage());
        
        // 测试带代码和前一个异常的情况
        $previous = new \Exception('前一个异常');
        $exception = new LoaderException('测试加载器异常', 123, $previous);
        
        $this->assertEquals('测试加载器异常', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
    
    public function testParserException()
    {
        // 测试基本异常
        $exception = new ParserException('测试解析器异常');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('测试解析器异常', $exception->getMessage());

        // 测试带代码和前一个异常的情况
        $previous = new \Exception('前一个异常');
        $exception = new ParserException('测试解析器异常', 456, $previous);

        $this->assertEquals('测试解析器异常', $exception->getMessage());
        $this->assertEquals(456, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
