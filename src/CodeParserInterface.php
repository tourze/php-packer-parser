<?php

namespace PhpPacker\Parser;

use PhpPacker\Ast\AstManagerInterface;

/**
 * 高级代码解析器接口
 */
interface CodeParserInterface
{
    /**
     * 解析指定文件及其所有依赖
     *
     * @param string $file 入口文件路径
     * @return void
     */
    public function parse(string $file): void;
    
    /**
     * 获取所有已处理的文件列表
     *
     * @return array<string> 文件路径列表
     */
    public function getProcessedFiles(): array;
    
    /**
     * 获取文件依赖关系映射
     *
     * @return array<string, array<string>> 文件依赖关系数组，格式为[文件路径 => [依赖文件路径, ...]]
     */
    public function getDependencies(): array;
    
    /**
     * 获取AST管理器实例
     *
     * @return AstManagerInterface AST管理器
     */
    public function getAstManager(): AstManagerInterface;
    
    /**
     * 检查文件是否已处理
     *
     * @param string $file 文件路径
     * @return bool 如果已处理返回true，否则返回false
     */
    public function isFileProcessed(string $file): bool;
} 