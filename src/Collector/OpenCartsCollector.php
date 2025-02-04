<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Koality\ShopwarePlugin\Formatter\Result;

/**
 * Class OpenCartsCollector
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 */
class OpenCartsCollector implements Collector
{
    /**
     * @var array
     */
    private $pluginConfig = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * OpenCartsCollector constructor.
     *
     * @param array $pluginConfig
     * @param Connection $connection
     */
    public function __construct(array $pluginConfig, Connection $connection)
    {
        $this->pluginConfig = $pluginConfig;
        $this->connection = $connection;
    }

    /**
     * @inheritdoc

     * @throws DBALException
     */
    public function getResult(): Result
    {
        $cartCount = $this->getOpenCartCount();

        $maxCartCount = $this->pluginConfig['openCarts'];

        if ($cartCount > $maxCartCount) {
            $cartResult = new Result(Result::STATUS_FAIL, Result::KEY_CARTS_OPEN_TOO_MANY, 'There are too many open carts at the moment.');
        } else {
            $cartResult = new Result(Result::STATUS_PASS, Result::KEY_CARTS_OPEN_TOO_MANY, 'There are not too many open carts at the moment.');
        }

        $cartResult->setLimit($maxCartCount);
        $cartResult->setObservedValue($cartCount);
        $cartResult->setObservedValueUnit('carts');
        $cartResult->setObservedValuePrecision(0);
        $cartResult->setLimitType(Result::LIMIT_TYPE_MAX);
        $cartResult->setType(Result::TYPE_TIME_SERIES_NUMERIC);

        return $cartResult;
    }

    /**
     * Return the number of open carts.
     *
     * @return int
     *
     * @throws DBALException
     */
    private function getOpenCartCount(): int
    {
        // @todo fetch only new carts (created_at < 1h)
        $carts = $this->connection->executeQuery('SELECT * FROM cart');
        return count($carts->fetchAll());
    }

    private function findPlugins() {
        $plugins = $this->repository->search(
            new Criteria(),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
    }
}
