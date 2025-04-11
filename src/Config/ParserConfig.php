<?php

namespace PhpPacker\Parser\Config;

/**
 * 解析器配置
 */
class ParserConfig
{
    /**
     * 是否启用性能计时
     */
    private bool $enableStopwatch = true;
    
    /**
     * 是否缓存已解析的AST
     */
    private bool $cacheAst = true;
    
    /**
     * 最大递归深度
     */
    private int $maxRecursionDepth = 100;
    
    /**
     * 设置是否启用性能计时
     *
     * @param bool $enable 是否启用
     * @return self
     */
    public function setEnableStopwatch(bool $enable): self
    {
        $this->enableStopwatch = $enable;
        return $this;
    }
    
    /**
     * 获取是否启用性能计时
     *
     * @return bool
     */
    public function isStopwatchEnabled(): bool
    {
        return $this->enableStopwatch;
    }
    
    /**
     * 设置是否缓存已解析的AST
     *
     * @param bool $cache 是否缓存
     * @return self
     */
    public function setCacheAst(bool $cache): self
    {
        $this->cacheAst = $cache;
        return $this;
    }
    
    /**
     * 获取是否缓存已解析的AST
     *
     * @return bool
     */
    public function isCacheAstEnabled(): bool
    {
        return $this->cacheAst;
    }
    
    /**
     * 设置最大递归深度
     *
     * @param int $depth 最大深度
     * @return self
     */
    public function setMaxRecursionDepth(int $depth): self
    {
        $this->maxRecursionDepth = max(1, $depth);
        return $this;
    }
    
    /**
     * 获取最大递归深度
     *
     * @return int
     */
    public function getMaxRecursionDepth(): int
    {
        return $this->maxRecursionDepth;
    }
} 