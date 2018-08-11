<?php

namespace Horne;

class MetaBag
{
    protected $sourcePath;
    protected $destPath;
    protected $metaPayload;

    /**
     * @param string $sourcePath
     * @param string $destPath
     * @param array  $metaPayload
     */
    public function __construct($sourcePath, $destPath, array $metaPayload)
    {
        $this->sourcePath  = $sourcePath;
        $this->destPath    = $destPath;
        $this->metaPayload = $metaPayload;
    }

    /**
     * @param string $newPath
     */
    public function setDestPath($newPath)
    {
        $this->destPath = $newPath;
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * @return string
     */
    public function getDestPath()
    {
        return $this->destPath;
    }

    /**
     * @return array
     */
    public function getMetaPayload()
    {
        return $this->metaPayload;
    }

    /**
     * @param array $data
     */
    public function setMetaPayload(array $data)
    {
        $this->metaPayload = $data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->metaPayload['type'];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->metaPayload['id'];
    }

    /**
     * @return string|null
     */
    public function getLayout()
    {
        return isset($this->metaPayload['layout']) ? $this->metaPayload['layout'] : null;
    }
}
