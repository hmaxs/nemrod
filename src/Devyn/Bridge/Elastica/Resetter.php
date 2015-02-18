<?php

namespace Devyn\Bridge\Elastica;

/**
 * Class Resetter: resets all
 * @package Devyn\Bridge\Elastica
 */
class Resetter
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var MappingBuilder
     */
    private $mappingBuilder;

    public function __construct($configManager, $mappingBuilder)
    {
        $this->configManager = $configManager;
        $this->mappingBuilder = $mappingBuilder;
    }

} 