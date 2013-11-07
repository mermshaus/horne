<?php

namespace Horne\Module\Blog;

use Horne\Application;
use Horne\MetaBag;

class Blog
{
    /**
     *
     * @var Application
     */
    protected $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     *
     * @return array
     */
    public function getAllTags()
    {
        $app = $this->application;

        $tags = array();

        foreach ($app->metas->getAll() as $meta) {
            $m = $meta->getMetaPayload();
            if (isset($m['tags'])) {
                foreach ($m['tags'] as $tag) {
                    if (!array_key_exists($tag, $tags)) {
                        $tags[$tag] = 0;
                    }
                    $tags[$tag]++;
                }
            }
        }

        arsort($tags);

        return $tags;
    }

    /**
     *
     */
    public function hookProcessingBefore()
    {
        $app = $this->application;

        // horne-blog-index

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/index.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => 'horne-blog-index',
            'type' => '_script'
        ]));

        // horne-tag-*

        if ($app->getSetting('blog.useTags')) {
            $tags = $this->getAllTags();

            foreach (array_keys($tags) as $tag) {
                $pseudoMeta = array(
                    'id'    => 'horne-blog-tag-' . $tag,
                    'title' => 'All entries tagged with ' . $tag,
                    'type'  => 'page-tag',
                    'path'  => '/blog/tags/' . $tag . '.html',
                    'layout' => $app->config['defaultLayoutPath'],
                    'tag' => $tag
                );

                $app->metas->add(new MetaBag('', $app->config['outputDir'] . $pseudoMeta['path'], $pseudoMeta));
            }
        }

        // horne-blog-archive-index

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/archive/index.phtml', $app->config['outputDir'] . '/blog/archive/index.html', [
            'id' => 'horne-blog-archive-index',
            'title' => 'Archive',
            'type' => 'page',
            'path' => '/blog/archive/index.html'
        ]));

        // Layouts

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/article.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => '/types/article.html',
            'type' => '_default'
        ]));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/page-tag.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => '/types/page-tag.html',
            'type' => '_default'
        ]));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/page.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => '/types/page.html',
            'type' => '_default'
        ]));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/_default.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => '/types/_default.html',
            'type' => '_master'
        ]));
    }
}
