<?php

namespace PhpPacker\Parser\Tests\Unit;

use PhpPacker\Analysis\Dependency\DependencyAnalyzerInterface;
use PhpPacker\Ast\AstManagerInterface;
use PhpPacker\Ast\CodeParserInterface as AstCodeParserInterface;
use PhpPacker\Parser\CodeParser;
use PhpPacker\Parser\Exception\ParserException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CodeParserTest extends TestCase
{
    /** @var DependencyAnalyzerInterface|MockObject */
    private $dependencyAnalyzer;
    
    /** @var AstManagerInterface|MockObject */
    private $astManager;
    
    /** @var AstCodeParserInterface|MockObject */
    private $astCodeParser;
    
    /** @var LoggerInterface|MockObject */
    private $logger;
    
    protected function setUp(): void
    {
        // 创建模拟对象
        $this->dependencyAnalyzer = $this->createMock(DependencyAnalyzerInterface::class);
        $this->astManager = $this->createMock(AstManagerInterface::class);
        $this->astCodeParser = $this->createMock(AstCodeParserInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }
    
    public function testParseFile()
    {
        // 测试文件路径
        $testFile = '/path/to/test.php';
        $dependencyFile = '/path/to/dependency.php';
        
        // 模拟AST结果
        $ast = ['node1', 'node2'];
        
        // 配置模拟对象行为
        $this->astCodeParser->expects($this->exactly(2))
            ->method('parseFile')
            ->willReturnMap([
                [$testFile, $ast],
                [$dependencyFile, $ast]
            ]);
        
        // 模拟依赖分析器行为
        $this->dependencyAnalyzer->expects($this->exactly(2))
            ->method('findDependencies')
            ->willReturnCallback(function ($file, $nodes) use ($testFile, $dependencyFile) {
                if ($file === $testFile) {
                    return new \ArrayIterator([$dependencyFile]);
                }
                return new \ArrayIterator([]);
            });
        
        // 创建被测对象
        $parser = new CodeParser(
            $this->dependencyAnalyzer,
            $this->astManager,
            $this->astCodeParser,
            null,
            $this->logger,
            false // 禁用计时器
        );
        
        // 执行测试
        $parser->parse($testFile);
        
        // 验证结果
        $this->assertTrue($parser->isFileProcessed($testFile));
        $this->assertTrue($parser->isFileProcessed($dependencyFile));
        $this->assertSame([$testFile, $dependencyFile], $parser->getProcessedFiles());
        $this->assertSame([$dependencyFile], $parser->getDependencies()[$testFile]);
        $this->assertSame([], $parser->getDependencies()[$dependencyFile]);
        $this->assertSame($this->astManager, $parser->getAstManager());
    }
    
    public function testParseFileWithError()
    {
        // 测试文件路径
        $testFile = '/path/to/test.php';
        
        // 配置模拟对象抛出异常
        $this->astCodeParser->expects($this->once())
            ->method('parseFile')
            ->with($testFile)
            ->willThrowException(new \RuntimeException('Test parse error'));
        
        // 创建被测对象
        $parser = new CodeParser(
            $this->dependencyAnalyzer,
            $this->astManager,
            $this->astCodeParser,
            null,
            $this->logger,
            false // 禁用计时器
        );
        
        // 期望异常被抛出
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('解析文件失败: /path/to/test.php');
        
        // 执行测试
        $parser->parse($testFile);
    }
    
    public function testSkipAlreadyProcessedFile()
    {
        // 测试文件路径
        $testFile = '/path/to/test.php';
        
        // 模拟AST结果
        $ast = ['node1', 'node2'];
        
        // 配置模拟对象行为 - 只应该被调用一次
        $this->astCodeParser->expects($this->once())
            ->method('parseFile')
            ->with($testFile)
            ->willReturn($ast);
        
        $this->dependencyAnalyzer->expects($this->once())
            ->method('findDependencies')
            ->willReturn(new \ArrayIterator([]));
        
        // 创建被测对象
        $parser = new CodeParser(
            $this->dependencyAnalyzer,
            $this->astManager,
            $this->astCodeParser,
            null,
            $this->logger,
            false
        );
        
        // 执行测试 - 调用两次parse，但处理应该只发生一次
        $parser->parse($testFile);
        $parser->parse($testFile);
        
        // 验证结果
        $this->assertTrue($parser->isFileProcessed($testFile));
        $this->assertCount(1, $parser->getProcessedFiles());
    }
    
    public function testParseWithCircularDependencies()
    {
        // 测试循环依赖
        $fileA = '/path/to/fileA.php';
        $fileB = '/path/to/fileB.php';
        $fileC = '/path/to/fileC.php';
        
        // 模拟AST结果
        $ast = ['node1', 'node2'];
        
        // 配置AST解析器
        $this->astCodeParser->expects($this->exactly(3))
            ->method('parseFile')
            ->willReturnMap([
                [$fileA, $ast],
                [$fileB, $ast],
                [$fileC, $ast],
            ]);
        
        // 模拟依赖分析器 - 创建循环依赖: A -> B -> C -> A
        $this->dependencyAnalyzer->expects($this->exactly(3))
            ->method('findDependencies')
            ->willReturnCallback(function ($file, $nodes) use ($fileA, $fileB, $fileC) {
                if ($file === $fileA) {
                    return new \ArrayIterator([$fileB]);
                } elseif ($file === $fileB) {
                    return new \ArrayIterator([$fileC]);
                } else { // fileC
                    return new \ArrayIterator([$fileA]);
                }
            });
        
        // 记录日志调用
        $this->logger->expects($this->atLeastOnce())
            ->method('debug');
        
        // 创建被测对象
        $parser = new CodeParser(
            $this->dependencyAnalyzer,
            $this->astManager,
            $this->astCodeParser,
            null,
            $this->logger,
            false
        );
        
        // 执行测试 - 期望能够处理循环依赖而不是无限递归
        $parser->parse($fileA);
        
        // 验证结果 - 所有文件都应该被处理
        $this->assertTrue($parser->isFileProcessed($fileA));
        $this->assertTrue($parser->isFileProcessed($fileB));
        $this->assertTrue($parser->isFileProcessed($fileC));
        $this->assertCount(3, $parser->getProcessedFiles());
    }
}
