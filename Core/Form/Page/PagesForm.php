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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use AlphaLemon\AlphaLemonCmsBundle\Core\Form\ModelChoiceValues\ChoiceValues;
use AlphaLemon\ThemeEngineBundle\Core\ThemesCollection\AlThemesCollection;
use AlphaLemon\ThemeEngineBundle\Core\Theme\AlActiveTheme;

/**
 * Defines the pages form
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class PagesForm extends AbstractType
{
    private $activeTheme;
    private $themes;

    public function __construct(AlActiveTheme $activeTheme, AlThemesCollection $themes)
    {
        $this->activeTheme = $activeTheme;
        $this->themes = $themes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('pageName');
        $builder->add('template', 'choice', array('choices' => ChoiceValues::getTemplates($this->activeTheme, $this->themes)));
        $builder->add('isHome', 'checkbox');
        $builder->add('isPublished', 'checkbox');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'AlphaLemon\AlphaLemonCmsBundle\Core\Form\Page\Page',
        );
    }

    public function getName()
    {
        return 'pages';
    }
}
