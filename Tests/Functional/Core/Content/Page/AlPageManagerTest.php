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

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Functional\Core\Content\Page;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Page\AlPageManager;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguage;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlBlockQuery;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlPageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Core\Model\AlPageAttributeQuery;
use \AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General;

class AlPageManagerTest extends TestCase
{   
    private $dispatcher;
    private $translator;
    private $pageManager;
    private $templateManager;
      
    protected function setUp() 
    {
        parent::setUp();
        
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        
        $this->validator = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Validator\AlParametersValidatorPageManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->templateManager = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Template\AlTemplateManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        
        $this->pageModel = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Model\Propel\AlPageModel')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        
        $this->pageManager = new AlPageManager($this->dispatcher, $this->translator, $this->validator, $this->templateManager, $this->pageModel);
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\InvalidParameterTypeException
     */
    public function testSetFailsWhenANotValidPropelObjectIsGiven()
    {
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlBlock');
        $this->pageModel->expects($this->once())
            ->method('setModelObject')
            ->will($this->throwException(new General\InvalidParameterTypeException()));
        
        $this->pageManager->set($page);
    }
    
    public function testSetANullAlPageObject()
    {
        $this->pageModel->expects($this->once())
            ->method('setModelObject')
            ->with(null)
            ->will($this->returnValue(null));
        
        $this->pageManager->set(null);
        $this->assertNull($this->pageManager->get());
    }
    
    
    private function valorizePageModelSetAndGet($page)
    {
        $this->pageModel->expects($this->any())
            ->method('setModelObject')
            ->with($page)
            ->will($this->returnSelf());
        
        $this->pageModel->expects($this->any())
            ->method('getModelObject')
            ->will($this->returnValue($page));
    }
    
    public function testSetAlPageObject()
    {
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->valorizePageModelSetAndGet($page);
        $this->assertEquals($page, $this->pageManager->get());
    }
    
    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\EmptyParametersException
     */
    public function testAddFailsWhenAnyParamIsGiven()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
                
        $this->validator->expects($this->once())
            ->method('checkEmptyParams')
            ->will($this->throwException(new General\EmptyParametersException()));
        
        $values = array();
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $this->pageManager->save($values); 
    }
    
    /**
     * @expectedException \AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\ParameterExpectedException
     */
    public function testAddFailsWhenAnyExpectedParamIsGiven()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
                
        $this->validator->expects($this->once())
            ->method('checkRequiredParamsExists')
            ->will($this->throwException(new General\ParameterExpectedException()));
                
        $values = array('fake' => 'value');
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $this->pageManager->save($values); 
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\ParameterIsEmptyException
     */
    public function testAddFailsWhenExpectedPageNameParamIsMissing()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $params = array('template'      => 'home', 
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $this->pageManager->save($params); 
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\ParameterIsEmptyException
     */
    public function testAddFailsWhenExpectedTemplateParamIsMissing()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->translator->expects($this->once())
            ->method('trans');
        
        $params = array('pageName'      => 'fake page', 
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $this->pageManager->save($params); 
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\Page\AnyLanguageExistsException
     */
    public function testAddFailsWhenAnyLanguageHasBeenAddedAndTryingToAddPage()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->translator->expects($this->once())
            ->method('trans');
        
        $this->validator->expects($this->once())
            ->method('hasLanguages')
            ->will($this->returnValue(false));
        
        $params = array('pageName'      => 'fake page', 
                        'template'      => 'home',
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $this->pageManager->save($params); 
    }
        
    public function testAddNewPageFailsBecauseSaveFailsAtLast()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('rollback');
        
        $this->validator->expects($this->once())
            ->method('hasLanguages')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        
        $params = array('pageName'      => 'fake page', 
                        'template'      => 'home',
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        //$page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        //$this->pageManager->set($page);
        $res = $this->pageManager->save($params); 
        $this->assertFalse($res);
    }
        
    public function testAddNewPageFailsBecauseResetHomeFail()
    {
        $this->dispatcher->expects($this->once(1))
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('hasPages')
            ->will($this->returnValue(true));
        
        $this->validator->expects($this->once())
            ->method('hasLanguages')
            ->will($this->returnValue(true));
        
        $homepage = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->pageModel->expects($this->once())
            ->method('homePage')
            ->will($this->returnValue($homepage));
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('rollback');
        
        $params = array('pageName'      => 'fake page',
                        'isHome'        => '1', 
                        'template'      => 'home',
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->valorizePageModelSetAndGet($page);      
        
        $res = $this->pageManager->save($params); 
        $this->assertFalse($res);
    }
    
    public function testAddNewHomePage()
    {
        $this->dispatcher->expects($this->exactly(3))
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('hasPages')
            ->will($this->returnValue(true));
        
        $this->validator->expects($this->once())
            ->method('hasLanguages')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValue(true));
        
        $homepage = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->pageModel->expects($this->once())
            ->method('homePage')
            ->will($this->returnValue($homepage));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('commit');
        
        $params = array('pageName'      => 'fake page',
                        'isHome'        => '1', 
                        'template'      => 'home',
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->valorizePageModelSetAndGet($page);      
        
        $res = $this->pageManager->save($params); 
        $this->assertTrue($res);
    }
    
    public function testAddNewPage()
    {
        $this->dispatcher->expects($this->exactly(3))
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('hasLanguages')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('commit');
        
        $params = array('pageName'      => 'fake page', 
                        'template'      => 'home',
                        'permalink'     => 'this is a website fake page',
                        'title'         => 'page title',
                        'description'   => 'page description',
                        'keywords'      => '');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->valorizePageModelSetAndGet($page);      
        
        $res = $this->pageManager->save($params); 
        $this->assertTrue($res);
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\EmptyParametersException
     */
    public function testEditFailsWhenAnyParamIsGiven()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('checkEmptyParams')
            ->will($this->throwException(new General\EmptyParametersException()));
        
        /*
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->never())
            ->method('save');*/
        
        $this->pageModel->expects($this->never())
            ->method('save');
        
        $params = array();
        //$this->pageManager->set($page);
        $this->pageManager->save($params); 
    }
    
    public function testEditFailsBecauseSaveFailsAtLast()
    {
        $this->dispatcher->expects($this->once(1))
            ->method('dispatch');        
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        
        $page->expects($this->any())
            ->method('getPageName')
            ->will($this->returnValue('fake-page'));
        
        $this->valorizePageModelSetAndGet($page);      
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('rollback');
        
        $params = array('pageName' => 'fake page');
        //$this->pageManager->set($page);
        $res = $this->pageManager->save($params); 
        $this->assertFalse($res);
    }
    
    public function testEditPageName()
    {
        $this->dispatcher->expects($this->exactly(3))
            ->method('dispatch');        
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        
        $page->expects($this->any())
            ->method('getPageName')
            ->will($this->returnValue('fake-page'));
        
        $this->valorizePageModelSetAndGet($page);      
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('commit');
        
        $params = array('pageName' => 'fake page');
        //$this->pageManager->set($page);
        $res = $this->pageManager->save($params); 
        $this->assertTrue($res);
        $this->assertEquals('fake-page', $this->pageManager->get()->getPageName());
    }
    
    public function testEditHomePageBecauseResetHomeFails()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('hasPages')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));
        
        $homepage = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->pageModel->expects($this->once())
            ->method('homePage')
            ->will($this->returnValue($homepage));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('rollback');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        
        $this->valorizePageModelSetAndGet($page);      
        
        $params = array('isHome' => 1);
        $res = $this->pageManager->save($params); 
        $this->assertFalse($res);
    }
    
    public function testEditHomePage()
    {
        $this->dispatcher->expects($this->exactly(3))
            ->method('dispatch');
        
        $this->validator->expects($this->once())
            ->method('hasPages')
            ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValue(true));
        
        $homepage = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $this->pageModel->expects($this->once())
            ->method('homePage')
            ->will($this->returnValue($homepage));
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
            ->method('commit');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        
        $this->valorizePageModelSetAndGet($page);      
        
        $params = array('isHome' => 1);
        $res = $this->pageManager->save($params); 
        $this->assertTrue($res);
    }
    
    public function testEditTemplate()
    {
        $this->markTestSkipped();
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\ParameterIsEmptyException
     */
    public function testDeleteFailsWhenTheManagedPageIsNull()
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');
        
        $this->pageManager->set(null);
        $this->pageManager->delete(); 
    }
    
    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\Page\RemoveHomePageException
     */
    public function testDeleteFailsWhenTryingToRemoveTheHomePage()
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->once())
                ->method('getIsHome')
                ->will($this->returnValue(1));
        
        //$this->pageManager->set($page);
        $this->valorizePageModelSetAndGet($page);        
        $this->pageManager->delete(); 
    }
    
    public function testDeleteFailsBecauseSaveFailsAtLast()
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
                ->method('delete')
                ->will($this->returnValue(false));
        
        $this->pageModel->expects($this->once())
            ->method('rollback');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->any())
                ->method('getIsHome')
                ->will($this->returnValue(0));
        
        $this->valorizePageModelSetAndGet($page);     
        $res = $this->pageManager->delete(); 
        $this->assertFalse($res);
    }
    
    public function testDelete()
    {
        $this->dispatcher->expects($this->exactly(3))
            ->method('dispatch');
        
        $this->pageModel->expects($this->once())
            ->method('startTransaction');
        
        $this->pageModel->expects($this->once())
                ->method('delete')
                ->will($this->returnValue(true));
        
        $this->pageModel->expects($this->once())
            ->method('commit');
        
        $page = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlPage');
        $page->expects($this->any())
                ->method('getIsHome')
                ->will($this->returnValue(0));
        
        $this->valorizePageModelSetAndGet($page);     
        $res = $this->pageManager->delete(); 
        $this->assertTrue($res);
    }
}