<?php

namespace PhpPacker\Parser\Tests\Unit;

use PhpPacker\Parser\Exception\LoaderException;
use PhpPacker\Parser\Psr4Loader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Psr4LoaderTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;
    
    private string $tempDir;
    
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // 创建临时目录用于测试
        $this->tempDir = sys_get_temp_dir() . '/php-packer-parser-' . uniqid();
        mkdir($this->tempDir . '/composer', 0777, true);
    }
    
    protected function tearDown(): void
    {
        // 清理临时文件
        if (file_exists($this->tempDir . '/composer/autoload_psr4.php')) {
            unlink($this->tempDir . '/composer/autoload_psr4.php');
        }
        if (is_dir($this->tempDir . '/composer')) {
            rmdir($this->tempDir . '/composer');
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }
    
    public function testLoadPsr4Map()
    {
        // 创建一个模拟的PSR-4映射文件
        $psr4Map = [
            'PhpPacker\\' => [$this->tempDir . '/src'],
            'PhpPacker\\Tests\\' => [$this->tempDir . '/tests'],
        ];
        
        $psr4Content = '<?php return ' . var_export($psr4Map, true) . ';';
        file_put_contents($this->tempDir . '/composer/autoload_psr4.php', $psr4Content);
        
        // 配置日志记录器期望
        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Loaded PSR-4 autoload map', $this->anything());
        
        // 创建加载器并测试
        $loader = new Psr4Loader($this->tempDir, $this->logger);
        
        // 验证映射
        $loadedMap = $loader->getPsr4Map();
        $this->assertSame($psr4Map, $loadedMap);
    }
    
    public function testMissingAutoloadFile()
    {
        // 配置日志记录器期望
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('PSR-4 autoload map not found', $this->anything());
        
        // 使用不存在的路径创建加载器
        $loader = new Psr4Loader($this->tempDir . '/nonexistent', $this->logger);
        
        // 验证结果是空映射
        $this->assertEmpty($loader->getPsr4Map());
    }
    
    public function testInvalidAutoloadFile()
    {
        // 创建一个无效的PSR-4映射文件
        $invalidContent = '<?php return "not_an_array";';
        file_put_contents($this->tempDir . '/composer/autoload_psr4.php', $invalidContent);
        
        // 期望异常
        $this->expectException(LoaderException::class);
        
        // 创建加载器应该抛出异常
        new Psr4Loader($this->tempDir, $this->logger);
    }
    
    public function testFindPossiblePaths()
    {
        // 创建临时目录结构用于测试
        if (!is_dir($this->tempDir . '/src')) {
            mkdir($this->tempDir . '/src', 0777, true);
        }
        if (!is_dir($this->tempDir . '/lib')) {
            mkdir($this->tempDir . '/lib', 0777, true);
        }
        if (!is_dir($this->tempDir . '/tests')) {
            mkdir($this->tempDir . '/tests', 0777, true);
        }
        
        // 模拟PSR-4映射
        $psr4Map = [
            'PhpPacker\\' => [
                $this->tempDir . '/src',
                $this->tempDir . '/lib'
            ],
            'PhpPacker\\Tests\\' => [$this->tempDir . '/tests'],
        ];
        
        $loader = new Psr4Loader();
        
        // 使用反射设置内部属性
        $reflection = new \ReflectionClass($loader);
        $property = $reflection->getProperty('psr4Map');
        $property->setAccessible(true);
        $property->setValue($loader, $psr4Map);
        
        // 测试查找路径
        $paths = $loader->findPossiblePaths('PhpPacker\\Parser\\CodeParser');
        
        $expected = [
            $this->tempDir . '/src/Parser/CodeParser.php',
            $this->tempDir . '/lib/Parser/CodeParser.php',
        ];
        
        $this->assertEquals($expected, $paths);
        
        // 测试命名空间不匹配
        $paths = $loader->findPossiblePaths('App\\Something');
        $this->assertEmpty($paths);
        
        // 测试子命名空间，确保映射正确
        $paths = $loader->findPossiblePaths('PhpPacker\\Tests\\Unit\\ParserTest');
        $this->assertEquals(
            [$this->tempDir . '/tests/Unit/ParserTest.php'],
            $paths,
            "Tests命名空间应该映射到tests目录，而不是src/Tests"
        );
    }
    
    public function testDefaultConstructor()
    {
        // 测试没有参数的构造函数
        $loader = new Psr4Loader();
        
        // 应该有一个空映射
        $this->assertEmpty($loader->getPsr4Map());
    }
}
