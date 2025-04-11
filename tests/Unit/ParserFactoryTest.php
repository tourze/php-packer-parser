<?php

namespace PhpPacker\Parser\Tests\Unit;

use PhpPacker\Analysis\Dependency\DependencyAnalyzerInterface;
use PhpPacker\Ast\AstManagerInterface;
use PhpPacker\Ast\CodeParserInterface as AstCodeParserInterface;
use PhpPacker\Parser\CodeParser;
use PhpPacker\Parser\CodeParserInterface;
use PhpPacker\Parser\Config\ParserConfig;
use PhpPacker\Parser\ParserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ParserFactoryTest extends TestCase
{
    /** @var string */
    private $tempDir;
    
    protected function setUp(): void
    {
        // 创建临时目录用于测试
        $this->tempDir = sys_get_temp_dir() . '/php-packer-factory-test-' . uniqid();
        mkdir($this->tempDir . '/vendor/composer', 0777, true);
        
        // 创建一个模拟的自动加载文件
        $psr4Content = '<?php return [];';
        file_put_contents($this->tempDir . '/vendor/composer/autoload_psr4.php', $psr4Content);
    }
    
    protected function tearDown(): void
    {
        if (file_exists($this->tempDir . '/vendor/composer/autoload_psr4.php')) {
            unlink($this->tempDir . '/vendor/composer/autoload_psr4.php');
        }
        
        $this->removeDir($this->tempDir);
    }
    
    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    public function testCreateFactory()
    {
        // 准备测试数据
        $entryFile = $this->tempDir . '/test.php';
        $excludePatterns = ['*vendor*', '*tests*'];
        $config = new ParserConfig();
        
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        
        // 创建解析器
        $parser = ParserFactory::create(
            $entryFile,
            $excludePatterns,
            $config,
            $logger
        );
        
        // 验证结果
        $this->assertInstanceOf(CodeParserInterface::class, $parser);
        $this->assertInstanceOf(CodeParser::class, $parser);
    }
    
    public function testCreateWithDefaultValues()
    {
        // 使用最少的参数创建
        $entryFile = $this->tempDir . '/test.php';
        
        // 创建解析器
        $parser = ParserFactory::create($entryFile);
        
        // 验证结果
        $this->assertInstanceOf(CodeParserInterface::class, $parser);
    }
    
    public function testCreateWithDependencies()
    {
        // 准备模拟对象
        /** @var AstManagerInterface&MockObject $astManager */
        $astManager = $this->createMock(AstManagerInterface::class);
        
        /** @var DependencyAnalyzerInterface&MockObject $dependencyAnalyzer */
        $dependencyAnalyzer = $this->createMock(DependencyAnalyzerInterface::class);
        
        /** @var AstCodeParserInterface&MockObject $astCodeParser */
        $astCodeParser = $this->createMock(AstCodeParserInterface::class);
        
        $config = new ParserConfig();
        $logger = new NullLogger();
        
        // 创建解析器
        $parser = ParserFactory::createWithDependencies(
            $astManager,
            $dependencyAnalyzer,
            $astCodeParser,
            $config,
            $logger
        );
        
        // 验证结果
        $this->assertInstanceOf(CodeParserInterface::class, $parser);
        $this->assertInstanceOf(CodeParser::class, $parser);
        
        // 使用反射验证内部属性
        $reflection = new \ReflectionClass($parser);
        
        $astManagerProp = $reflection->getProperty('astManager');
        $astManagerProp->setAccessible(true);
        $this->assertSame($astManager, $astManagerProp->getValue($parser));
        
        $dependencyAnalyzerProp = $reflection->getProperty('dependencyAnalyzer');
        $dependencyAnalyzerProp->setAccessible(true);
        $this->assertSame($dependencyAnalyzer, $dependencyAnalyzerProp->getValue($parser));
    }
    
    public function testCreateWithDependenciesDefaultValues()
    {
        // 只提供必需的参数
        /** @var AstManagerInterface&MockObject $astManager */
        $astManager = $this->createMock(AstManagerInterface::class);
        
        /** @var DependencyAnalyzerInterface&MockObject $dependencyAnalyzer */
        $dependencyAnalyzer = $this->createMock(DependencyAnalyzerInterface::class);
        
        // 创建解析器
        $parser = ParserFactory::createWithDependencies(
            $astManager,
            $dependencyAnalyzer
        );
        
        // 验证结果
        $this->assertInstanceOf(CodeParserInterface::class, $parser);
    }
}
