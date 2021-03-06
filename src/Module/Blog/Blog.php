<?php

namespace Horne\Module\Blog;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class Blog extends AbstractModule
{
    /**
     * @return array
     */
    public function getAllTags()
    {
        $app = $this->application;

        $tags = [];

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
     * @param array $settings
     *
     * @return array
     */
    public function hookLoadConfig(array $settings)
    {
        $settings['useTags']            = isset($settings['useTags']) ? $settings['useTags'] : true;
        $settings['showInfoline']       = isset($settings['showInfoline']) ? $settings['showInfoline'] : true;
        $settings['showAuthor']         = isset($settings['showAuthor']) ? $settings['showAuthor'] : true;
        $settings['showArticleCounter'] = isset($settings['showArticleCounter']) ? $settings['showArticleCounter'] : true;

        return $settings;
    }

    /**
     * @return void
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }

    /**
     * @return void
     * @throws \Horne\HorneException
     */
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
                $pseudoMeta = [
                    'id'     => 'horne-blog-tag-' . $tag,
                    'title'  => 'All entries tagged with ' . $tag,
                    'type'   => 'page-tag',
                    'layout' => 'horne-layout-page-tag',
                    'path'   => sprintf($tagsPathTemplate, $tag),
                    'tag'    => $tag,
                ];

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
