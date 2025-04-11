<?php

namespace PhpPacker\Parser;

use PhpPacker\Parser\Exception\LoaderException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * PSR-4自动加载器
 */
class Psr4Loader
{
    /**
     * PSR-4命名空间映射
     * 
     * @var array<string, array<string>>
     */
    private array $psr4Map = [];
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * @param string|null $vendorPath composer vendor目录路径
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(
        ?string $vendorPath = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        
        if ($vendorPath !== null) {
            $this->loadPsr4Map($vendorPath);
        }
    }
    
    /**
     * 从指定的vendor目录加载PSR-4映射
     *
     * @param string $vendorPath composer vendor目录路径
     * @return void
     * @throws LoaderException 加载失败时抛出异常
     */
    public function loadPsr4Map(string $vendorPath): void
    {
        $autoloadFile = rtrim($vendorPath, '/') . '/composer/autoload_psr4.php';
        
        if (!file_exists($autoloadFile)) {
            $this->logger->warning('PSR-4 autoload map not found', ['file' => $autoloadFile]);
            return;
        }
        
        $map = require $autoloadFile;
        
        if (!is_array($map)) {
            throw new LoaderException("Invalid PSR-4 map in $autoloadFile");
        }
        
        $this->psr4Map = $map;
        $this->logger->debug('Loaded PSR-4 autoload map', [
            'namespaces' => array_keys($this->psr4Map),
            'count' => count($this->psr4Map)
        ]);
    }
    
    /**
     * 获取PSR-4命名空间映射
     *
     * @return array<string, array<string>> PSR-4映射，格式为[命名空间前缀 => [目录路径, ...]]
     */
    public function getPsr4Map(): array
    {
        return $this->psr4Map;
    }
    
    /**
     * 根据命名空间查找可能的文件路径
     *
     * @param string $namespace 完全限定的命名空间或类名
     * @return array<string> 可能的文件路径列表
     */
    public function findPossiblePaths(string $namespace): array
    {
        $possiblePaths = [];
        
        // 按照命名空间前缀长度降序排序，确保最长的前缀匹配优先
        $prefixes = array_keys($this->psr4Map);
        usort($prefixes, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($prefixes as $prefix) {
            if (strpos($namespace, $prefix) === 0) {
                $relPath = str_replace('\\', '/', substr($namespace, strlen($prefix)));
                $relPath = ltrim($relPath, '/');
                
                foreach ($this->psr4Map[$prefix] as $dir) {
                    $possiblePaths[] = $dir . '/' . $relPath . '.php';
                }
                
                // 找到匹配的前缀后就停止查找，避免子命名空间被较短的前缀匹配
                break;
            }
        }
        
        return $possiblePaths;
    }
}
