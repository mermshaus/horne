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
     * @param array $settings
     */
    public function hookLoadConfig(array $settings)
    {
        $settings['useTags'] = (isset($settings['useTags']))
                ? $settings['useTags']
                : true;

        $settings['showInfoline'] = (isset($settings['showInfoline']))
                ? $settings['showInfoline']
                : true;

        $settings['showAuthor'] = (isset($settings['showAuthor']))
                ? $settings['showAuthor']
                : true;

        return $settings;
    }

    /**
     *
     */
    public function hookProcessingBefore()
    {
        $app = $this->application;

        // Sub templates

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/index.phtml', $app->getSetting('outputDir') . '/nothing', array(
            'id'   => 'horne-blog-index',
            'type' => '_script'
        )));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/sub/article-list.phtml', $app->getSetting('outputDir') . '/nothing', array(
            'id'   => 'horne-blog-sub-article-list',
            'type' => '_script'
        )));

        // horne-blog-archive-index

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/archive/index.phtml', $app->getSetting('outputDir') . '/blog/archive/index.html', array(
            'id'     => 'horne-blog-archive-index',
            'title'  => 'Archive',
            'type'   => 'page',
            'layout' => 'horne-layout-page',
            'path'   => '/blog/archive/index.html'
        )));

        // Layouts

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/article.phtml', $app->getSetting('outputDir') . '/nothing', array(
            'id'     => 'horne-layout-article',
            'type'   => 'layout',
            'layout' => 'horne-layout-default'
        )));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/page-tag.phtml', $app->getSetting('outputDir') . '/nothing', array(
            'id'     => 'horne-layout-page-tag',
            'type'   => 'layout',
            'layout' => 'horne-layout-default'
        )));
    }

    public function hookProcessingBefore2()
    {
        $app = $this->application;

        $tagsPathTemplate = $app->getSetting('blog.tagsPathTemplate');
        $tagsSlugTemplate = $app->getSetting('blog.tagsSlugTemplate');

        if ($tagsPathTemplate === null) {
            $tagsPathTemplate = '/blog/tags/%s.html';
        }

        // horne-blog-tag-*

        if ($app->getSetting('blog.useTags')) {
            $tags = $this->getAllTags();

            foreach (array_keys($tags) as $tag) {
                $pseudoMeta = array(
                    'id'     => 'horne-blog-tag-' . $tag,
                    'title'  => 'All entries tagged with ' . $tag,
                    'type'   => 'page-tag',
                    'layout' => 'horne-layout-page-tag',
                    'path'   => sprintf($tagsPathTemplate, $tag),
                    'tag'    => $tag
                );

                if ($tagsSlugTemplate !== null) {
                    $pseudoMeta['slug'] = sprintf($tagsSlugTemplate, $tag);
                }

                $app->metas->add(new MetaBag(
                    '',
                    $app->getSetting('outputDir') . $pseudoMeta['path'],
                    $pseudoMeta
                ));
            }
        }
    }
}
