<?php

namespace Horne;

class MetaBag
{
    protected $sourcePath;
    protected $destPath;
    protected $metaPayload;

    public function __construct($sourcePath, $destPath, $metaPayload)
    {
        $this->sourcePath = $sourcePath;
        $this->destPath = $destPath;
        $this->metaPayload = $metaPayload;
    }

    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    public function getDestPath()
    {
        return $this->destPath;
    }

    public function getMetaPayload()
    {
        return $this->metaPayload;
    }

    public function getType()
    {
        return $this->metaPayload['type'];
    }

    public function getId()
    {
        return $this->metaPayload['id'];
    }
}
