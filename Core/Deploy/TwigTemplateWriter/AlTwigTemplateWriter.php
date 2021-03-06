<?php

/*
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Deploy\TwigTemplateWriter;

use AlphaLemon\AlphaLemonCmsBundle\Core\PageTree\AlPageTree;
use AlphaLemon\AlphaLemonCmsBundle\Core\UrlManager\AlUrlManagerInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManagerFactoryInterface;

/**
 * AlTwigTemplateWriter generates a twig template from a PageTree object
 *
 * @author AlphaLemon <webmaster@alphalemon.com>
 */
class AlTwigTemplateWriter
{
    protected $pageTree;
    protected $urlManager;
    protected $template;
    protected $twigTemplate;
    protected $templateSection;
    protected $metatagsSection;
    protected $assetsSection;
    protected $contentsSection;
    protected $blockManagerFactory;

    /**
     * Constructor
     *
     * The $replaceImagesPaths contains the backend images' path and the production images path, as follows:
     *
     *      array(
     *          backendPath => '/path/to/backend/images,
     *          prodPath    => '/path/to/prod/images,
     *      )
     *
     * When the page is saving, the images' path is replaced
     *
     * @param AlPageTree            $pageTree
     * @param AlUrlManagerInterface $urlManager
     * @param array                 $replaceImagesPaths
     */
    public function  __construct(AlPageTree $pageTree, AlBlockManagerFactoryInterface $blockManagerFactory, AlUrlManagerInterface $urlManager, array $replaceImagesPaths = array())
    {
        $this->pageTree = $pageTree;
        $this->blockManagerFactory = $blockManagerFactory;
        $this->urlManager = $urlManager;
        $this->replaceImagesPaths = $replaceImagesPaths;
        $this->template = $this->pageTree->getTemplate();
        $this->generateTemplate();
    }

    /**
     * Returns the generated template
     *
     * @return string
     */
    public function getTwigTemplate()
    {
        return $this->twigTemplate;
    }

    /**
     * Returns the template extend directive
     *
     * @return string
     */
    public function getTemplateSection()
    {
        return $this->templateSection;
    }

    /**
     * Returns the metatags section
     *
     * @return string
     */
    public function getMetaTagsSection()
    {
        return $this->metatagsSection;
    }

    /**
     * Returns the assets section
     *
     * @return string
     */
    public function getAssetsSection()
    {
        return $this->assetsSection;
    }

    /**
     * Returns the contents section
     *
     * @return string
     */
    public function getContentsSection()
    {
        return $this->contentsSection;
    }

    /**
     * Writes the template
     *
     * @param  string  $dir
     * @return boolean
     */
    public function writeTemplate($dir)
    {
        // Writes down the file
        $fileDir = $dir . '/' . $this->pageTree->getAlLanguage()->getLanguageName();
        if (!is_dir($fileDir)) {
            mkdir($fileDir);
        }

        return @file_put_contents($fileDir . '/' . $this->pageTree->getAlPage()->getPageName() . '.html.twig', $this->twigTemplate);
    }

    /**
     * Generates the template's subsections and the full template itself
     */
    protected function generateTemplate()
    {
        $this->generateTemplateSection();
        $this->generateMetaTagsSection();
        $this->generateAssetsSection();
        $this->generateContentsSection();

        $this->twigTemplate = $this->templateSection . $this->metatagsSection . $this->assetsSection . $this->contentsSection;
    }

    /**
     * Generates the template extension section
     */
    protected function generateTemplateSection()
    {
        $this->templateSection = sprintf("{%% extends '%s:Theme:%s.html.twig' %%}" . PHP_EOL, $this->template->getThemeName(), $this->template->getTemplateName());
    }

    /**
     * Generates the metatags section
     */
    protected function generateMetaTagsSection()
    {
        $this->metatagsSection = $this->writeComment("Metatags section");
        $this->metatagsSection .= $this->writeBlock('title', $this->pageTree->getMetaTitle());
        $this->metatagsSection .= $this->writeBlock('description', $this->pageTree->getMetaDescription());
        $this->metatagsSection .= $this->writeBlock('keywords', $this->pageTree->getMetaKeywords());
    }

    /**
     * Generates the assets section
     */
    protected function generateAssetsSection()
    {
        $externalStylesheets = $this->pageTree->getExternalStylesheets();
        $externalJavascripts = $this->pageTree->getExternalJavascripts();
        $internalStylesheet = $this->pageTree->getInternalStylesheets();
        $internalJavascript = $this->pageTree->getInternalJavascripts();
        $this->assetsSection = $this->writeComment("Assets section");
        if (!empty($externalStylesheets)) {
            $sectionContent = '<link href="{{ asset_url }}" rel="stylesheet" type="text/css" media="all" />';
            $this->assetsSection .= $this->writeBlock('external_stylesheets', $this->writeAssetic('stylesheets', implode(' ', array_map(function($value){ return '"' . $value . '"'; }, $externalStylesheets )), $sectionContent, '?yui_css,cssrewrite'));
        }

        if (!empty($externalJavascripts)) {
            $sectionContent = '<script src="{{ asset_url }}"></script>';
            $this->assetsSection .= $this->writeBlock('external_javascripts', $this->writeAssetic('javascripts', implode(' ', array_map(function($value){ return '"' . $value . '"'; }, $externalJavascripts )), $sectionContent, '?yui_js'));
        }

        if (!empty($internalStylesheet)) {
            $this->assetsSection .= $this->writeBlock('internal_header_stylesheets', '<style>' . $internalStylesheet . '</style>');
        }

        if (!empty($internalJavascript)) {
            $this->assetsSection .= $this->writeBlock('internal_header_javascripts', '<script>$(document).ready(function(){' . $this->rewriteImagesPathForProduction($internalJavascript) . '});</script>');
        }
    }

    /**
     * Generates the contents section
     */
    protected function generateContentsSection()
    {
        // Writes page contentsSection
        $this->contentsSection = $this->writeComment("Contents section");
        $slots = array_keys($this->template->getSlots());

        $languageName = $this->pageTree->getAlLanguage()->getLanguageName();
        $pageName = $this->pageTree->getAlPage()->getPageName();
        $blocks = $this->pageTree->getPageBlocks()->getBlocks();
        foreach ($blocks as $slotName => $slotBlocks) {
            if (!in_array($slotName, $slots))
                continue;

            $htmlContents = array();
            foreach ($slotBlocks as $block) {
                $content = "";
                $blockManager = $this->blockManagerFactory->createBlockManager($block);
                if (null !== $blockManager) {
                    $content = $blockManager->getHtml();
                    $content = $this->rewriteImagesPathForProduction($content);
                    $content = $this->rewriteLinksForProduction($languageName, $pageName, $content);
                }

                $htmlContents[] = $content;
            }

            $this->contentsSection .= $this->writeBlock($slotName, $this->writeContent($slotName, implode("\n" . PHP_EOL, $htmlContents)));
        }

        $template = $this->pageTree->getTemplate();
        if (null === $template) return;

        $templateSlots = $template->getTemplateSlots();
        $slots = $templateSlots->getSlots();
        $orphanSlots = array_diff_key($slots, $blocks);
        foreach ($orphanSlots as $slot) {
            $slotName = $slot->getSlotName();
            $this->contentsSection .= $this->writeBlock($slotName, $this->writeContent($slotName, ""));
        }
    }

    /**
     * Rewrites the images to be correctly displayed in the production environment
     */
    protected function rewriteImagesPathForProduction($content)
    {
        if (empty($this->replaceImagesPaths) && count(array_diff_key(array('backendPath' => '', 'prodPath' => ''), $this->replaceImagesPaths)) > 0) {
            return $content;
        }

        $cmsAssetsFolder = $this->replaceImagesPaths['backendPath'];
        $deployBundleAssetsFolder = $this->replaceImagesPaths['prodPath'];

        return preg_replace_callback('/([\/]?)(' . str_replace('/', '\/', $cmsAssetsFolder) . ')/s', function($matches) use ($deployBundleAssetsFolder) {return $matches[1].$deployBundleAssetsFolder;}, $content);
    }

    protected function rewriteLinksForProduction($languageName, $pageName, $content)
    {
        $urlManager = $this->urlManager;

        return preg_replace_callback('/(\<a[^\>]+href[="\'\s]+)([^"\'\s]+)?([^\>]+\>)/s', function ($matches) use ($urlManager, $languageName, $pageName) {
            $url = $matches[2];
            $route = $urlManager
                ->fromUrl($url)
                ->getProductionRoute();

           if (null !== $route) {
               $url = sprintf("{{ path('%s') }}", $route);
           }

           return $matches[1] . $url . $matches[3];
        }, $content);

        return $content;
    }

    /**
     * Writes a comment section
     *
     * @param  string $comment
     * @return string
     */
    protected function writeComment($comment)
    {
        $comment = strtoupper($comment);

        return "\n{#--------------  $comment  --------------#}" . PHP_EOL;
    }

    /**
     * Writes a block section
     *
     * @param  string $blockName
     * @param  string $blockContent
     * @return string
     */
    protected function writeBlock($blockName, $blockContent)
    {
        if (empty($blockContent)) {
            return "";
        }

        $block = "{% block $blockName %}" . PHP_EOL;
        $block .= $blockContent . "" . PHP_EOL;
        $block .= "{% endblock %}\n" . PHP_EOL;

        return $block;
    }

    /**
     * Writes an assetc section
     *
     * @param  string $sectionName
     * @param  string $assetsSection
     * @param  string $sectionContent
     * @param  string $filter
     * @param  string $output
     * @return string
     */
    protected function writeAssetic($sectionName, $assetsSection, $sectionContent, $filter = null, $output = null)
    {
        $section = $sectionName . " " . $assetsSection;
        if (null !== $filter)
            $section .= " filter=\"$filter\"";
        if (null !== $output)
            $section .= " output=\"$output\"";
        $block = "  {% $section %}" . PHP_EOL;
        $block .= $this->identateContent($sectionContent) . "" . PHP_EOL;
        $block .= "  {% end$sectionName %}";

        return $block;
    }

    /**
     * Writes a content section
     *
     * @param  string $slotName
     * @param  string $content
     * @return string
     */
    protected function writeContent($slotName, $content)
    {
        $formattedContent = $this->MarkSlotContents($slotName, $content);

        if (!empty($content)) {
            $formattedContent = $this->identateContent($formattedContent) . PHP_EOL;
            $formattedContent .= "  {% else %}" . PHP_EOL;
            $formattedContent .= "    {{ parent() }}" . PHP_EOL;
        }

        $block = "  {% if(slots.$slotName is not defined) %}" . PHP_EOL;
        $block .= $formattedContent;
        $block .= "  {% endif %}";

        return $block;
    }

    /**
     * Marks the contents of the given slot with a Begin/End comment
     *
     * @param  string $slotName
     * @param  string $content
     * @return string
     */
    public static function MarkSlotContents($slotName, $content)
    {
        $commentSkeleton = '<!-- %s %s BLOCK -->';
        $slotName = strtoupper($slotName);

        return PHP_EOL . sprintf($commentSkeleton, "BEGIN", $slotName) . PHP_EOL . $content . PHP_EOL . sprintf($commentSkeleton, "END", $slotName) . PHP_EOL;
    }

    /**
     * Indentates the given content
     *
     * @param  string $content
     * @return string
     */
    protected function identateContent($content)
    {
        $formattedContents = array();
        $tokens = explode(PHP_EOL, $content);
        foreach ($tokens as $token) {
            $token = trim($token);
            if(!empty($token)) $formattedContents[] = "    " . $token;
        }

        return implode(PHP_EOL, $formattedContents);
    }
}
