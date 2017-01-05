<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class CourseMenuBuilder
 * @package Chamilo\CoreBundle\Menu
 */
class CourseMenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Course menu
     *
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function courseMenu(FactoryInterface $factory, array $options)
    {
        $security = $this->container->get('security.authorization_checker');
        $menu = $factory->createItem('root');
        $translator = $this->container->get('translator');

        if ($security->isGranted('IS_AUTHENTICATED_FULLY')) {

            $menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked');
            $menu->addChild(
                $translator->trans('MyCourses'),
                [
                    'route' => 'userportal',
                    'routeParameters' => ['type' => 'courses'],
                ]
            );

            $menu->addChild(
                $translator->trans('MySessions'),
                [
                    'route' => 'userportal',
                    'routeParameters' => ['type' => 'sessions'],
                ]
            );

            $menu->addChild(
                $translator->trans('CreateCourse'),
                ['route' => 'add_course']
            );

            $menu->addChild(
                $translator->trans('AddSession'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'session/session_add.php'],
                ]
            );

            $link = $this->container->get('router')->generate('web.main');

            $menu->addChild(
                $translator->trans('ManageCourses'),
                [
                    'uri' => $link.'auth/courses.php?action=sortmycourses',
                ]
            );

            /** @var \Knp\Menu\MenuItem $menu */
            $menu->addChild(
                $translator->trans('History'),
                [
                    'route' => 'userportal',
                    'routeParameters' => [
                        'type' => 'sessions',
                        'filter' => 'history',
                    ],
                ]
            );

            $menu->addChild(
                $translator->trans('CourseCatalog'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'auth/courses.php'],
                ]
            );
        }

        return $menu;
    }

    /**
     * Skills menu
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function skillsMenu(FactoryInterface $factory, array $options)
    {
        $security = $this->container->get('security.authorization_checker');
        $translator = $this->container->get('translator');
        $menu = $factory->createItem('root');
        if ($security->isGranted('IS_AUTHENTICATED_FULLY')) {

            $menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked');

            $menu->addChild(
                $translator->trans('MyCertificates'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'gradebook/my_certificates.php'],
                ]
            );

            $menu->addChild(
                $translator->trans('Search'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'gradebook/search.php'],
                ]
            );

            $menu->addChild(
                $translator->trans('MySkills'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'social/my_skills_report.php'],
                ]
            );

            $menu->addChild(
                $translator->trans('ManageSkills'),
                [
                    'route' => 'main',
                    'routeParameters' => ['name' => 'admin/skills_wheel.php'],
                ]
            );
        }

        return $menu;
    }
}
