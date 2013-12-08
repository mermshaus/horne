<?php

namespace Horne;

use Horne\MetaBag;

/**
 *
 */
class MetaRepository
{
    /**
     *
     * @var array
     */
    public $items = array();

    /**
     *
     * @var string
     */
    private $sourceDir;

    protected $cacheGetById = array();

    protected $cacheGetByType = array();

    /**
     *
     * @param string $sourceDir
     */
    public function __construct($sourceDir)
    {
        $this->sourceDir = $sourceDir;
    }

    /**
     *
     * @param MetaBag $metaBag
     */
    public function add(MetaBag $metaBag)
    {
        foreach ($this->items as $item) {
            /* @var $item MetaBag */
            if ($item->getId() === $metaBag->getId()) {
                // If both in sourceDir, throw Exception (in other words:
                // files from sourceDir can override files added by modules)
                if (
                    0 === strpos($item->getSourcePath(), $this->sourceDir)
                    && 0 === strpos($metaBag->getSourcePath(), $this->sourceDir)
                ) {
                    throw new HorneException(sprintf(
                        'Meta with id %s does already exist. Error is in %s',
                        $metaBag->getId(),
                        $metaBag->getSourcePath()
                    ));
                } else {
                    $this->removeById($item->getId());
                    break;
                }
            }
        }

        $this->items[] = $metaBag;
    }

    /**
     *
     * @return array
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     *
     * @param string $id
     * @return MetaBag
     * @throws HorneException
     */
    public function getById($id)
    {
        if (!array_key_exists($id, $this->cacheGetById)) {
            foreach ($this->items as $item) {
                /* @var $item MetaBag */
                $payload = $item->getMetaPayload();
                if ($payload['id'] === $id) {
                    $this->cacheGetById[$id] = $item;
                    break;
                }
            }
        }

        if (!array_key_exists($id, $this->cacheGetById)) {
            throw new HorneException(sprintf(
                'MetaBag with id %s doesn\'t exist',
                $id
            ));
        }

        return $this->cacheGetById[$id];
    }

    /**
     *
     * @param string $type
     * @return array
     */
    public function getByType($type, array $order = array(), $limitCount = -1, $limitOffset = 0)
    {
        $hashParts = array();
        $hashParts[] = $type;

        if (count($order) !== 0) {
            $hashParts[] = key($order) . ',' . current($order);
        }

        $hashParts[] = $limitCount;
        $hashParts[] = $limitOffset;

        $hash = implode("\x00", $hashParts);

        if (!array_key_exists($hash, $this->cacheGetByType)) {
            $metas = array();

            foreach ($this->items as $item) {
                $payload = $item->getMetaPayload();
                if ($payload['type'] === $type) {
                    $metas[] = $item;
                }
            }

            if (count($order) !== 0) {
                $orderField     = key($order);
                $orderDirection = $order[$orderField];

                if ($orderDirection !== 'desc') {
                    $orderDirection = 'asc';
                }

                usort($metas, function ($a, $b) use ($orderField, $orderDirection) {
                    $a2 = $a->getMetaPayload();
                    $b2 = $b->getMetaPayload();

                    if ($orderDirection === 'desc') {
                        $tmp = $a2;
                        $a2 = $b2;
                        $b2 = $tmp;
                    }

                    return strcmp($a2[$orderField], $b2[$orderField]);
                });
            }

            $x = ($limitCount !== -1) ? $limitCount : null;

            $this->cacheGetByType[$hash] = array_slice($metas, $limitOffset, $x);
        }

        return $this->cacheGetByType[$hash];
    }

    /**
     *
     * @param string $id
     */
    private function removeById($id)
    {
        $newItems = array();

        foreach ($this->items as $item) {
            if ($item->getId() !== $id) {
                $newItems[] = $item;
            }
        }

        $this->items = $newItems;
    }
}
