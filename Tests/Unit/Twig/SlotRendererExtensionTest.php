<?php

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Unit\Twig;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Twig\SlotRendererExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * AlphaLemonCmsExtensionTest
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class SlotRendererExtensionTest extends TestCase
{
    private $container;

    protected function setUp()
    {
        $this->pageTree = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\PageTree\AlPageTree')
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->slotRenderer = new SlotRendererExtension($this->container);
    }

    public function testTwigFunctions()
    {
        $functions = array(
            "renderSlot",
            "renderBlock",
        );
        $this->assertEquals($functions, array_keys($this->slotRenderer->getFunctions()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage renderSlot function requires a valid slot name to render the contents
     */
    public function testAnExceptionIsThrownWhenSlotNameIsNull()
    {
        $this->slotRenderer->renderSlot();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage renderSlot function requires a string as argument to identify the slot name
     */
    public function testAnExceptionIsThrownWhenSlotNameIsNotAString()
    {
        $this->slotRenderer->renderSlot(array());
    }

    public function testRenderTheEmptySlot()
    {
        $this->setUpContainer();
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->returnValue(array()));

        $this->assertEquals($this->renderEmptySlot(), $this->slotRenderer->renderSlot('logo'));
    }

    public function testAnEmptySlotIsRenderedWhenAllBlockManagersAreNull()
    {
        $this->setUpContainer();
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->returnValue(array(null)));

        $this->assertEquals($this->renderEmptySlot(), $this->slotRenderer->renderSlot('logo'));
    }

    public function testSlotHasBeenRendereda()
    {
        $this->setUpContainer();
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->throwException(new \RuntimeException('Impossibile to do something')));

        $expectedValue = '<div class="al_logo">Something was wrong rendering the logo slot. This is the returned error: Impossibile to do something</div>';
        $this->assertEquals($expectedValue, $this->slotRenderer->renderSlot('logo'));
    }

    public function testSlotHasBeenRendered()
    {
        $this->setUpContainer();
        $value = array(
            "Block" => array(
                "Id" => "10",
                "SlotName" => "logo",
                "Type" => "Text",
            ),
            "Content" => "my awesome content",
            "EditorWidth" => "800",
        );

        $blockManagers = array($this->setUpBlockManager($value));
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->returnValue($blockManagers));

        $expectedValue = '<div class="al_logo">' . PHP_EOL;
        $expectedValue .= '<!-- BEGIN LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '<div id="block_10" class=" al_editable {id: \'10\', slotName: \'logo\', type: \'text\', editorWidth: \'800\'}"><div>my awesome content</div></div>' . PHP_EOL;
        $expectedValue .= '<!-- END LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '</div>';
        $this->assertEquals($expectedValue, $this->slotRenderer->renderSlot('logo'));
    }

    public function testASlotHideInEditModeHasBeenRendered()
    {
        $this->setUpContainer();
        $value = array(
            "Block" => array(
                "Id" => "10",
                "SlotName" => "logo",
                "Type" => "Text",
            ),
            "Content" => "my awesome content",
            "HideInEditMode" => "true",
            "EditorWidth" => "800",
        );

        $blockManagers = array($this->setUpBlockManager($value));
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->returnValue($blockManagers));

        $expectedValue = '<div class="al_logo">' . PHP_EOL;
        $expectedValue .= '<!-- BEGIN LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '<div id="block_10" class="al_hide_edit_mode al_editable {id: \'10\', slotName: \'logo\', type: \'text\', editorWidth: \'800\'}"><div>my awesome content</div></div>' . PHP_EOL;
        $expectedValue .= '<!-- END LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '</div>';
        $this->assertEquals($expectedValue, $this->slotRenderer->renderSlot('logo'));
    }

    public function testSlotHasBeenRenderedFromATwigTemplate()
    {
        $value = array(
            "Block" => array(
                "Id" => "10",
                "SlotName" => "logo",
                "Type" => "Text",
            ),
            "Content" => "my awesome replaced content",
            "EditorWidth" => "800",
            "RenderView" => array(
                "view" => "AlphaLemonWebSite:Template:my_template.twig.html",
                "params" => array(),
            )
        );

        $blockManagers = array($this->setUpBlockManager($value));
        $this->pageTree->expects($this->once())
            ->method('getBlockManagers')
            ->will($this->returnValue($blockManagers));

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())
                        ->method('render')
                        ->will($this->returnValue('<p>This content has been rendered from a twig template</p>'));

        $this->container->expects($this->at(0))
                        ->method('get')
                        ->with('alpha_lemon_cms.page_tree')
                        ->will($this->returnValue($this->pageTree));

        $this->container->expects($this->at(1))
                        ->method('get')
                        ->with('templating')
                        ->will($this->returnValue($templating));

        $expectedValue = '<div class="al_logo">' . PHP_EOL;
        $expectedValue .= '<!-- BEGIN LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '<div id="block_10" class=" al_editable {id: \'10\', slotName: \'logo\', type: \'text\', editorWidth: \'800\'}"><div><p>This content has been rendered from a twig template</p></div></div>' . PHP_EOL;
        $expectedValue .= '<!-- END LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '</div>';
        $this->assertEquals($expectedValue, $this->slotRenderer->renderSlot('logo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage renderBlock function requires an array to render its contents. A null block argument has given
     */
    public function testAnExceptionIsThrownWhenBlockArgumentIsNull()
    {
        $this->slotRenderer->renderBlock();
    }

    public function testBlockIsRenderedAsNewBlock()
    {
        $value = array(
            "Block" => array(
                "Id" => "10",
                "SlotName" => "logo",
                "Type" => "Text",
            ),
            "Content" => "my awesome content",
            "EditorWidth" => "800",
        );

        $expectedValue = '<div id="block_10" class=" al_editable {id: \'10\', slotName: \'logo\', type: \'text\', editorWidth: \'800\'}"><div>my awesome content</div></div>';
        $this->assertEquals($expectedValue, $this->slotRenderer->renderBlock($value, true));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAnExceptionHasThrownBackWhenSomethingThrowsAnException()
    {
        $value = array(
            "Block" => array(
                "Id" => "10",
                "SlotName" => "logo",
                "Type" => "Text",
            ),
            "Content" => "my awesome replaced content",
            "RenderView" => array(
                "view" => "AlphaLemonWebSite:Template:my_template.twig.html",
                "params" => array(),
            )
        );

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())
                        ->method('render')
                        ->will($this->throwException(new \RuntimeException()));

        $this->container->expects($this->once())
                        ->method('get')
                        ->with('templating')
                        ->will($this->returnValue($templating));

        $this->slotRenderer->renderBlock($value);
    }

    private function setUpBlockManager(array $value)
    {
        $blockManager = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $blockManager->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($value));

        return $blockManager;
    }

    private function setUpContainer()
    {
        $this->pageTree->expects($this->any())
                       ->method('isCmsMode')
                       ->will($this->returnValue(true));

        $this->container->expects($this->once())
                        ->method('get')
                        ->with('alpha_lemon_cms.page_tree')
                        ->will($this->returnValue($this->pageTree));
    }

    private function renderEmptySlot()
    {
        $expectedValue = '<div class="al_logo">' . PHP_EOL;
        $expectedValue .= '<!-- BEGIN LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '<div class="al_editable {id: \'0\', slotName: \'logo\'}">This slot has any content inside. Use the contextual menu to add a new one</div>' . PHP_EOL;
        $expectedValue .= '<!-- END LOGO BLOCK -->' . PHP_EOL;
        $expectedValue .= '</div>';

        return $expectedValue;
    }
}
