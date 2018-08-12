<?php

namespace Horne;

class MetaRepository
{
    /**
     * @var MetaBag[]
     */
    private $metaBags = [];

    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @param string $sourceDir
     */
    public function __construct($sourceDir)
    {
        $this->sourceDir = $sourceDir;
    }

    /**
     * Add a MetaBag to the repository
     *
     * When trying to add a MetaBag with an existing id:
     *
     * - Throw Exception when both MetaBags are loaded from sourceDir.
     * - Otherwise replace the existing MetaBag.
     *
     * Note: The second rule implies that MetaBags from modules may override MetaBags from sourceDir. It is yet to be
     * decided whether this is useful behaviour.
     *
     * @param MetaBag $newMetaBag
     *
     * @throws HorneException
     */
    public function add(MetaBag $newMetaBag)
    {
        foreach ($this->metaBags as $metaBag) {
            if ($metaBag->getId() === $newMetaBag->getId()) {
                if (
                    strpos($metaBag->getSourcePath(), $this->sourceDir) === 0
                    && strpos($newMetaBag->getSourcePath(), $this->sourceDir) === 0
                ) {
                    throw new HorneException(sprintf(
                        'Cannot load MetaBag with id "%s" from file "%s".'
                        . ' A MetaBag with this id has already been processed from file "%s".'
                        . ' You can only override MetaBags from modules',
                        $newMetaBag->getId(),
                        $newMetaBag->getSourcePath(),
                        $metaBag->getSourcePath()
                    ));
                }

                $this->removeById($metaBag->getId());
                break;
            }
        }

        $this->metaBags[] = $newMetaBag;
    }

    /**
     * @return MetaBag[]
     */
    public function getAll()
    {
        return $this->metaBags;
    }

    /**
     * @param string $id
     *
     * @return MetaBag
     * @throws HorneException
     */
    public function getById($id)
    {
        foreach ($this->metaBags as $metaBag) {
            if ($metaBag->getId() === $id) {
                return $metaBag;
            }
        }

        throw new HorneException(sprintf('MetaBag with id "%s" does not exist', $id));
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
        $metaBagsWithType = array_values(
            array_filter(
                $this->metaBags,
                function (MetaBag $metaBag) use ($type) {
                    return $metaBag->getType() === $type;
                }
            )
        );

        if (count($order) !== 0) {
            $orderField     = key($order);
            $orderDirection = strtolower($order[$orderField]);

            if ($orderDirection !== 'desc') {
                $orderDirection = 'asc';
            }

            usort($metaBagsWithType, function (MetaBag $a, MetaBag $b) use ($orderField, $orderDirection) {
                $metaPayloadA = $a->getMetaPayload();
                $metaPayloadB = $b->getMetaPayload();

                if ($orderDirection === 'desc') {
                    return strcmp($metaPayloadB[$orderField], $metaPayloadA[$orderField]);
                }

                return strcmp($metaPayloadA[$orderField], $metaPayloadB[$orderField]);
            });
        }

        $x = ($limitCount !== -1) ? $limitCount : null;

        return array_slice($metaBagsWithType, $limitOffset, $x);
    }

    /**
     * @param string $id
     *
     * @return void
     */
    private function removeById($id)
    {
        $newItems = [];

        foreach ($this->metaBags as $metaBag) {
            if ($metaBag->getId() !== $id) {
                $newItems[] = $metaBag;
            }
        }

        $this->metaBags = $newItems;
    }
}
