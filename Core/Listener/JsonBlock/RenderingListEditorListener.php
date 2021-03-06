<?php
/**
 * This file is part of the BusinessCarouselBundle and it is distributed
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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Listener\JsonBlock;

use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent;

/**
 * Manipulates the block's editor response when the editor has been rendered
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
abstract class RenderingListEditorListener extends BaseRenderingEditorListener
{
    protected $alBlockManager = null;

    protected function renderEditor(BlockEditorRenderingEvent $event, array $params)
    {
        try {
            $this->alBlockManager = $event->getBlockManager();
            if ($this->alBlockManager instanceof $params['blockClass']) {
                $editor = $this->doRenderEditor($event);
                $event->setEditor($editor);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function doRenderEditor(BlockEditorRenderingEvent $event)
    {
        $block = $this->alBlockManager->get();
        $className =$block->getType();
        $items = json_decode($block->getContent(), true);
        $template = sprintf('%sBundle:Block:%s_list.html.twig', $className, strtolower($className));

        return $event->getContainer()->get('templating')->render($template, array("items" => $items, "block_id" => $block->getId()));
    }
}
