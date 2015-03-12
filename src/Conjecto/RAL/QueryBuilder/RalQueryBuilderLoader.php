<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 26/01/2015
 * Time: 11:02
 */

namespace Conjecto\RAL\QueryBuilder;

use Conjecto\RAL\ResourceManager\Manager\Manager;
use EasyRdf\Graph;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RalQueryBuilderLoader
{
    /**
     * @var string
     */
    protected $class;

    /**
     * Construct an ORM Query Builder Loader
     *
     * @param QueryBuilder|\Closure $queryBuilder
     * @param Manager               $manager
     * @param string                $class
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($queryBuilder, $manager = null, $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Conjecto\RAL\QueryBuilder\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            if (!$manager instanceof Manager) {
                throw new UnexpectedTypeException($manager, 'Conjecto\RAL\ResourceManager\Manager\Manager');
            }

            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Conjecto\RAL\QueryBuilder\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;
        $this->class = $class;
    }

    /**
     * @param null $hydratation
     * @param array $options
     * @return Graph|\EasyRdf\Sparql\Result
     */
    public function getResources($hydratation = null, $options = array())
    {
        return $this->queryBuilder->getQuery()->execute($hydratation, $options);
    }
}