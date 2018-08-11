<?php

namespace Horne;

class MetaRepository
{
    /**
     * @var MetaBag[]
     */
    public $items = [];

    /**
     * @var string
     */
    private $sourceDir;

    protected $cacheGetById = [];

    protected $cacheGetByType = [];

    /**
     * @param string $sourceDir
     */
    public function __construct($sourceDir)
    {
        $this->sourceDir = $sourceDir;
    }

    /**
     * @param MetaBag $metaBag
     *
     * @throws HorneException
     */
    public function add(MetaBag $metaBag)
    {
        foreach ($this->items as $item) {
            /* @var MetaBag $item */
            if ($item->getId() === $metaBag->getId()) {
                // If both in sourceDir, throw Exception (in other words:
                // files from sourceDir can override files added by modules)
                if (
                    strpos($item->getSourcePath(), $this->sourceDir) === 0
                    && strpos($metaBag->getSourcePath(), $this->sourceDir) === 0
                ) {
                    throw new HorneException(sprintf(
                        'Meta with id %s does already exist. Error is in %s',
                        $metaBag->getId(),
                        $metaBag->getSourcePath()
                    ));
                }

                $this->removeById($item->getId());
                break;
            }
        }

        $this->items[] = $metaBag;
    }

    /**
     * @return MetaBag[]
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * @param string $id
     *
     * @return MetaBag
     * @throws HorneException
     */
    public function getById($id)
    {
        if (!array_key_exists($id, $this->cacheGetById)) {
            foreach ($this->items as $item) {
                /* @var MetaBag $item */
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
     * @param string $type
     * @param array  $order
     * @param int    $limitCount
     * @param int    $limitOffset
     *
     * @return MetaBag[]
     */
    public function getByType($type, array $order = [], $limitCount = -1, $limitOffset = 0)
    {
        $hashParts   = [];
        $hashParts[] = $type;

        if (count($order) !== 0) {
            $hashParts[] = key($order) . ',' . current($order);
        }

        $hashParts[] = $limitCount;
        $hashParts[] = $limitOffset;

        $hash = implode("\x00", $hashParts);

        if (!array_key_exists($hash, $this->cacheGetByType)) {
            $metas = [];

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

                usort($metas, function (MetaBag $a, MetaBag $b) use ($orderField, $orderDirection) {
                    $a2 = $a->getMetaPayload();
                    $b2 = $b->getMetaPayload();

                    if ($orderDirection === 'desc') {
                        $tmp = $a2;
                        $a2  = $b2;
                        $b2  = $tmp;
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
     * @param string $id
     *
     * @return void
     */
    private function removeById($id)
    {
        $newItems = [];

        foreach ($this->items as $item) {
            if ($item->getId() !== $id) {
                $newItems[] = $item;
            }
        }

        $this->items = $newItems;
    }
}
