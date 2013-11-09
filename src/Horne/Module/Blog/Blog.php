<?php

namespace Horne\Module\Blog;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class Blog extends AbstractModule
{
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
                    'layout' => 'horne-layout-page-tag',
                    'path'  => '/blog/tags/' . $tag . '.html',
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
            'layout' => 'horne-layout-page',
            'path' => '/blog/archive/index.html'
        ]));

        // Layouts

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/article.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => 'horne-layout-article',
            'type' => 'layout',
            'layout' => 'horne-layout-default'
        ]));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/page-tag.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => 'horne-layout-page-tag',
            'type' => 'layout',
            'layout' => 'horne-layout-default'
        ]));
    }
}
