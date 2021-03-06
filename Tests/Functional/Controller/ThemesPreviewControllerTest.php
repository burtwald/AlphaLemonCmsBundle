<?php
/**
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license infpageRepositoryation, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Functional\Controller;

use AlphaLemon\AlphaLemonCmsBundle\Tests\WebTestCaseFunctional;

/**
 * ThemesControllerTest
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class ThemesPreviewControllerTest extends WebTestCaseFunctional
{
    public static function setUpBeforeClass()
    {
        self::$languages = array(array('LanguageName'      => 'en',));

        self::$pages = array(
            array(
                'PageName'      => 'index',
                'TemplateName'  => 'home',
                'IsHome'        => '1',
                'Permalink'     => 'this is a website fake page',
                'MetaTitle'         => 'page title',
                'MetaDescription'   => 'page description',
                'MetaKeywords'      => 'key'
            ),
            array(
                'PageName'      => 'page1',
                'TemplateName'  => 'fullpage',
                'Permalink'     => 'page-1',
                'MetaTitle'         => 'page 1 title',
                'MetaDescription'   => 'page 1 description',
                'MetaKeywords'      => ''
            ),
            array(
                'PageName'      => 'page2',
                'TemplateName'  => 'rightcolumn',
                'Permalink'     => 'page-2',
                'MetaTitle'         => 'page 1 title',
                'MetaDescription'   => 'page 1 description',
                'MetaKeywords'      => ''
            ),
            array(
                'PageName'      => 'page3',
                'TemplateName'  => 'sixboxes',
                'Permalink'     => 'page-3',
                'MetaTitle'         => 'page 1 title',
                'MetaDescription'   => 'page 1 description',
                'MetaKeywords'      => ''
            ),
        );
        self::populateDb();
    }
    
    public function testThemePreview()
    {
        $crawler = $this->client->request('GET', '/backend/en/al_previewTheme/en/index/BusinessWebsiteThemeBundle');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        
        $this->assertEquals(1, $crawler->filter('#al_toolbar')->count());    
        $this->assertEquals(1, $crawler->filter('#al_current_theme')->count());
        $this->assertEquals('BusinessWebsiteThemeBundle', $crawler->filter('#al_current_theme')->text());  
        $this->assertEquals(1, $crawler->filter('#al_current_template')->count());
        $this->assertEquals('home', $crawler->filter('#al_current_template')->text());  
        $this->assertEquals(4, $crawler->filter('.al_caption')->count());
        $this->assertEquals(1, $crawler->filter('#al_templates_selector')->count());
        $this->assertEquals('homefullpagerightcolumnsixboxes', $crawler->filter('#al_templates_selector')->text());
        $this->assertEquals(1, $crawler->filter('#al_save')->count());
        $this->assertEquals('Save and activate', $crawler->filter('#al_save')->text());
        $this->assertEquals(1, $crawler->filter('#al_back')->count());
        $this->assertEquals('Return to CMS', $crawler->filter('#al_back')->text());
        $this->assertEquals(1, $crawler->filter('#al_referal_language')->count());
        $this->assertEquals('en', $crawler->filter('#al_referal_language')->text());
        $this->assertEquals(1, $crawler->filter('#al_referal_page')->count());
        $this->assertEquals('index', $crawler->filter('#al_referal_page')->text());
        $this->assertEquals(1, $crawler->filter('#al_active_theme')->count());
        $this->assertEquals(1, $crawler->filter('.al_active_templates_caption')->count());
        $this->assertEquals(1, $crawler->filter('#al_slots')->count());
        $this->assertEquals(1, $crawler->filter('#al_active_template_selector')->count());
        $this->assertEquals('homefullpagerightcolumnsixboxesrepeated_slots', $crawler->filter('#al_active_template_selector')->text());        
        $this->assertEquals(1, $crawler->filter('#al_map_home')->count());   
        $this->assertEquals(1, $crawler->filter('#al_map_fullpage')->count());   
        $this->assertEquals(1, $crawler->filter('#al_map_rightcolumn')->count());   
        $this->assertEquals(1, $crawler->filter('#al_map_sixboxes')->count());
        $this->assertEquals(1, $crawler->filter('#al_map_logo')->count());        
        $this->assertEquals(31, $crawler->filter('.al_slot')->count());
        
        // Checks one slot per page
        $this->assertEquals(1, $crawler->filter('#al_slot_repeated_slots_logo')->count()); 
        $this->assertEquals(1, $crawler->filter('#al_locker_repeated_slots_logo')->count());    
        $this->assertEquals(1, $crawler->filter('#al_slot_home_slider_box')->count());           
        $this->assertEquals(1, $crawler->filter('#al_locker_home_slider_box')->count());
        $this->assertEquals(1, $crawler->filter('#al_slot_fullpage_page_content')->count());
        $this->assertEquals(1, $crawler->filter('#al_locker_fullpage_page_content')->count());   
        $this->assertEquals(1, $crawler->filter('#al_slot_rightcolumn_middle_sidebar')->count()); 
        $this->assertEquals(1, $crawler->filter('#al_locker_rightcolumn_middle_sidebar')->count());
        $this->assertEquals(1, $crawler->filter('#al_slot_sixboxes_content_1')->count());
        $this->assertEquals(1, $crawler->filter('#al_locker_sixboxes_content_1')->count());
    }
    
    public function testScriptBlocksAreNotDisplayed()
    {
        $this->blockRepository = new \AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel();
        $blocks = $this->blockRepository->retrieveContents(2, 2, 'top_section_1');
        $block = $blocks[0]; //->getId();exit;
        $block->setContent('<script>doSomething();</script>');
        $block->save();
        
        $crawler = $this->client->request('GET', '/backend/en/al_previewTheme/en/index/BusinessWebsiteThemeBundle');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('This block contains a script block and it is not renderable in preview mode', $crawler->filter('#home_top_section_1')->text()); 
    }
}