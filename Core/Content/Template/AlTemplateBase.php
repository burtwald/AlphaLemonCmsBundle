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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Content\Template;

use AlphaLemon\AlphaLemonCmsBundle\Core\EventsHandler\AlEventsHandlerInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManagerFactoryInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManagerFactory;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Base\AlContentManagerBase;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Validator\AlParametersValidatorInterface;

/**
 * Implements the base object that defines a template
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
abstract class AlTemplateBase extends AlContentManagerBase
{
    protected $blockManagerFactory;

    /**
     * Contructor
     *
     * @param AlEventsHandlerInterface       $eventsHandler
     * @param AlBlockManagerFactoryInterface $blockManagerFactory
     * @param AlParametersValidatorInterface $validator
     */
    public function __construct(AlEventsHandlerInterface $eventsHandler, AlBlockManagerFactoryInterface $blockManagerFactory, AlParametersValidatorInterface $validator = null)
    {
        parent::__construct($eventsHandler, $validator);

        $this->blockManagerFactory = $blockManagerFactory; //(null === $blockManagerFactory) ? new AlBlockManagerFactory() : $blockManagerFactory;
    }

    /**
     * Sets the blockManager factory object
     *
     * @param  AlBlockManagerFactoryInterface                                       $blockManagerFactory
     * @return \AlphaLemon\AlphaLemonCmsBundle\Core\Content\Template\AlTemplateBase (for fluent API)
     */
    public function setBlockManagerFactory(AlBlockManagerFactoryInterface $blockManagerFactory)
    {
        $this->blockManagerFactory = $blockManagerFactory;

        return $this;
    }

    /**
     * Returns the blockManager factory object
     *
     * @return AlBlockManagerFactoryInterface
     */
    public function getBlockManagerFactory()
    {
        return $this->blockManagerFactory;
    }
}
