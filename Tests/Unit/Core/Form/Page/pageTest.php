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

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Unit\Core\Form\Language;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Core\Form\Page\Page;

/**
 * PageTest
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class PageTest extends TestCase
{
    private $page = null;
    
    protected function setUp()
    {
        parent::setUp();

        $this->page = new Page();
    }

    public function testPageName()
    {
        $page = 'index';
        $this->page->setPageName($page);
        $this->assertEquals($page, $this->page->getPageName());
    }
    
    public function testTemplate()
    {
        $template = 'home';
        $this->page->setTemplate($template);
        $this->assertEquals($template, $this->page->getTemplate());
    }

    public function testIsHome()
    {
        $this->page->setIsHome(true);
        $this->assertTrue($this->page->getIsHome());
    }
    
    public function testIsPublished()
    {
        $this->page->setIsPublished(true);
        $this->assertTrue($this->page->getIsPublished());
    }
}