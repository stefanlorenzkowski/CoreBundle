<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function topBarRightMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $securityContext = $this->container->get('security.context');
        $hasRoleExtension = $this->container->get('claroline.core_bundle.twig.has_role_extension');
        $router = $this->container->get('router');
        $dispatcher = $this->container->get('event_dispatcher');
        $toolManager = $this->container->get('claroline.manager.tool_manager');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'dropdown-menu')
            ->setChildrenAttribute('role', 'menu');

        $menu->addChild($translator->trans('my_profile', array(), 'platform'), array('route' => 'claro_profile_view'))
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-user');
        $menu->addChild(
            $translator->trans('parameters', array(), 'platform'),
            array('uri' => $router->generate('claro_desktop_open_tool', array('toolName' => 'parameters')))
        )->setAttribute('class', 'dropdown')
        ->setAttribute('role', 'presentation')
        ->setExtra('icon', 'fa fa-cog');

        $this->addDivider($menu, '1');

        $user = $securityContext->getToken()->getUser();
        $lockedOrderedTools = $toolManager->getOrderedToolsLockedByAdmin(1);
        $adminTools = array();
        $excludedTools = array();

        foreach ($lockedOrderedTools as $lockedOrderedTool) {
            $lockedTool = $lockedOrderedTool->getTool();

            if ($lockedOrderedTool->isVisibleInDesktop()) {
                $adminTools[] = $lockedTool;
            }
            $excludedTools[] = $lockedTool;
        }
        $desktopTools = $toolManager->getDisplayedDesktopOrderedTools(
            $user,
            1,
            $excludedTools
        );
        $tools = array_merge($adminTools, $desktopTools);

        foreach ($tools as $tool) {
            $toolName = $tool->getName();

            if ($toolName === 'home' || $toolName === 'parameters') {
                continue;
            }
            $event = new ConfigureMenuEvent($factory, $menu, $tool);

            if ($dispatcher->hasListeners('claroline_top_bar_right_menu_configure_desktop_tool_' . $toolName)) {
                $dispatcher->dispatch(
                    'claroline_top_bar_right_menu_configure_desktop_tool_' . $toolName,
                    $event
                );
            } else {
                $dispatcher->dispatch(
                    'claroline_top_bar_right_menu_configure_desktop_tool',
                    $event
                );
            }
        }

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_right_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        $this->addDivider($menu, '2');

        //logout
        if ($hasRoleExtension->isImpersonated()) {
            $route = array(
                'route' => 'claro_desktop_open',
                'routeParameters' => array('_switch' => 'exit')
            );
        } else {
            $route = array('route' => 'claro_security_logout');
        }

        $menu->addChild($translator->trans('logout', array(), 'platform'), $route)
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setAttribute('name', 'logout')
            ->setAttribute('id', 'btn-logout')
            ->setExtra('icon', 'fa fa-power-off');

        return $menu;
    }

    public function topBarLeftMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $securityContext = $this->container->get('security.context');
        $configHandler = $this->container->get('claroline.config.platform_config_handler');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'nav navbar-nav');

         if ($configHandler->getParameter('name') == "" && $configHandler->getParameter('logo') == "") {
             $menu->addChild($translator->trans('home', array(), 'platform'), array('route' => 'claro_index'))
                ->setExtra('icon', 'fa fa-home');
         }

        $menu->addChild($translator->trans('desktop', array(), 'platform'), array('route' => 'claro_desktop_open'))
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-home')
            ->setExtra('title', $translator->trans('desktop', array(), 'platform'));

        $token = $securityContext->getToken();

        if ($token) {
            $user = $token->getUser();
            $roles = $this->container->get('claroline.security.utilities')->getRoles($token);
        } else {
            $roles = array('ROLE_ANONYMOUS');
        }

        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $dispatcher = $this->container->get('event_dispatcher');
            $toolManager = $this->container->get('claroline.manager.tool_manager');
            $lockedOrderedTools = $toolManager->getOrderedToolsLockedByAdmin();
            $adminTools = array();
            $excludedTools = array();

            foreach ($lockedOrderedTools as $lockedOrderedTool) {
                $lockedTool = $lockedOrderedTool->getTool();

                if ($lockedOrderedTool->isVisibleInDesktop()) {
                    $adminTools[] = $lockedTool;
                }
                $excludedTools[] = $lockedTool;
            }

            $desktopTools = $toolManager->getDisplayedDesktopOrderedTools(
                $user,
                0,
                $excludedTools
            );
            $tools = array_merge($adminTools, $desktopTools);

            foreach ($tools as $tool) {
                $toolName = $tool->getName();

                if ($toolName === 'home' || $toolName === 'parameters') {
                    continue;
                }
                $event = new ConfigureMenuEvent($factory, $menu, $tool);

                if ($dispatcher->hasListeners('claroline_top_bar_left_menu_configure_desktop_tool_' . $toolName)) {
                    $dispatcher->dispatch(
                        'claroline_top_bar_left_menu_configure_desktop_tool_' . $toolName,
                        $event
                    );
                } else {
                    $dispatcher->dispatch(
                        'claroline_top_bar_left_menu_configure_desktop_tool',
                        $event
                    );
                }
            }
        }

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_left_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function addDivider($menu, $name)
    {
        $menu->addChild($name)
            ->setAttribute('class', 'divider')
            ->setAttribute('role', 'presentation');
    }
}
