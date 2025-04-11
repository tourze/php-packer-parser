<?php

namespace PhpPacker\Parser\Tests\Unit\Config;

use PhpPacker\Parser\Config\ParserConfig;
use PHPUnit\Framework\TestCase;

class ParserConfigTest extends TestCase
{
    public function testDefaultValues()
    {
        $config = new ParserConfig();
        
        // 测试默认值
        $this->assertTrue($config->isStopwatchEnabled());
        $this->assertTrue($config->isCacheAstEnabled());
        $this->assertSame(100, $config->getMaxRecursionDepth());
    }
    
    public function testSetEnableStopwatch()
    {
        $config = new ParserConfig();
        
        // 默认为true
        $this->assertTrue($config->isStopwatchEnabled());
        
        // 设置为false
        $returnValue = $config->setEnableStopwatch(false);
        
        // 验证返回值是对象本身（链式调用）
        $this->assertSame($config, $returnValue);
        
        // 验证值已更改
        $this->assertFalse($config->isStopwatchEnabled());
    }
    
    public function testSetCacheAst()
    {
        $config = new ParserConfig();
        
        // 默认为true
        $this->assertTrue($config->isCacheAstEnabled());
        
        // 设置为false
        $returnValue = $config->setCacheAst(false);
        
        // 验证返回值是对象本身（链式调用）
        $this->assertSame($config, $returnValue);
        
        // 验证值已更改
        $this->assertFalse($config->isCacheAstEnabled());
    }
    
    public function testSetMaxRecursionDepth()
    {
        $config = new ParserConfig();
        
        // 默认为100
        $this->assertSame(100, $config->getMaxRecursionDepth());
        
        // 设置为50
        $returnValue = $config->setMaxRecursionDepth(50);
        
        // 验证返回值是对象本身（链式调用）
        $this->assertSame($config, $returnValue);
        
        // 验证值已更改
        $this->assertSame(50, $config->getMaxRecursionDepth());
    }
    
    public function testMaxRecursionDepthMinimumValue()
    {
        $config = new ParserConfig();
        
        // 尝试设置为0或负数
        $config->setMaxRecursionDepth(0);
        
        // 应该被设置为最小值1
        $this->assertSame(1, $config->getMaxRecursionDepth());
        
        $config->setMaxRecursionDepth(-10);
        
        // 也应该被设置为最小值1
        $this->assertSame(1, $config->getMaxRecursionDepth());
    }
    
    public function testFluentInterface()
    {
        $config = new ParserConfig();
        
        // 测试链式调用
        $result = $config
            ->setEnableStopwatch(false)
            ->setCacheAst(false)
            ->setMaxRecursionDepth(25);
        
        // 验证返回对象是同一个实例
        $this->assertSame($config, $result);
        
        // 验证所有值都已正确设置
        $this->assertFalse($config->isStopwatchEnabled());
        $this->assertFalse($config->isCacheAstEnabled());
        $this->assertSame(25, $config->getMaxRecursionDepth());
    }
}
