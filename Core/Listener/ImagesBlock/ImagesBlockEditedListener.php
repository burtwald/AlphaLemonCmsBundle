<?php
/**
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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Listener\ImagesBlock;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditedEvent;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\ImagesBlock\AlBlockManagerImages;

/**
 * Renders the ediotr to manage a serie of images
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class ImagesBlockEditedListener
{
    protected $templateEngine;

    /**
     * Contructor
     *
     * @param EngineInterface $templateEngine
     */
    public function __construct(EngineInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
    * Renders the editor
    *
    * @param BlockEditedEvent $event
    */
    public function onBlockEdited(BlockEditedEvent $event)
    {
        $blockManager = $event->getBlockManager();
        if ($blockManager instanceof AlBlockManagerImages) {
            $template = $this->templateEngine->render('AlphaLemonCmsBundle:Block:images_list.html.twig', array("alContent" => $blockManager));
            $values = array(
                array("key" => "images-list", "value" => $template),
                array("key" => "message", "value" => "The content has been successfully edited"),
                array("key" => "edit-block",
                        "blockName" => "block_" . $blockManager->get()->getId(),
                        "value" => $this->templateEngine->render('AlphaLemonCmsBundle:Cms:render_block.html.twig', array("block" => $blockManager->toArray()))
                    )
                );

            $response = new Response(json_encode($values));
            $response->headers->set('Content-Type', 'application/json');
            $event->setResponse($response);
        }
    }
}