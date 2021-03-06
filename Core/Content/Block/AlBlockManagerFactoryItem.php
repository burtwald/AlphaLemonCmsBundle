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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block;

use AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\ParameterExpectedException;

/**
 * AlBlockManagerFactoryItem saves the block manager, the id used to identify the block
 * manager itself and a description. Optionally accepts the group attribute, to group
 * togheter the blocks that belongs the same group
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class AlBlockManagerFactoryItem
{
    private $id;
    private $type;
    private $blockManager;
    private $description;
    private $group;
    private $requiredAttributes = array('id' => '', 'description' => '');

    /**
     * Constructor
     *
     * @param  AlBlockManagerInterface    $blockManager
     * @param  array                      $attributes
     * @throws ParameterExpectedException
     */
    public function __construct(AlBlockManagerInterface $blockManager, array $attributes)
    {
        $missingAttributes = array_diff_key($this->requiredAttributes, $attributes);
        if (count($missingAttributes) > 0) {
            throw new ParameterExpectedException(sprintf('AlBlockManagerFactoryItem expects the following attributes: "%s". Check the definition of "%s" object in the bundle that implements it', implode(',', array_keys($missingAttributes)), get_class($blockManager)));
        }

        $this->blockManager = $blockManager;
        $this->id = $attributes['id'];
        $this->type = $attributes['type'];
        $this->description = $attributes['description'];
        $this->group = (array_key_exists('group', $attributes)) ? $attributes['group'] : 'none';
    }

    /**
     * Returns the item id
     *
     * @return AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->blockManager;
    }

    /**
     * Returns the item id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the item id
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the item description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the item group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
}
