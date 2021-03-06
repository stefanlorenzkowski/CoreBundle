<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040800 extends Updater
{
    private $container;
    private $toolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->toolManager = $container->get('claroline.manager.tool_manager');
    }

    public function postUpdate()
    {
        $this->createMessageDesktopTool();
        $this->updateHomeTabsAdminTool();
        $this->updateWorkspaceMaxUsers();
    }

    public function preUpdate()
    {
        $this->deleteDuplicatedOrderedTools();
    }

    private function createMessageDesktopTool()
    {
        $this->log('Creating message tool...');
        $tool = $this->toolManager->getOneToolByName('message');

        if (is_null($tool)) {
            $tool = new Tool();
            $tool->setName('message');
            $tool->setClass('envelope');
            $tool->setDisplayableInWorkspace(false);
            $tool->setDisplayableInDesktop(true);
            $this->toolManager->create($tool);
            $this->createMessageDesktopOrderedTools($tool);
        }
    }

    private function createMessageDesktopOrderedTools(Tool $tool)
    {
        $this->log('Creating message ordered tools for all users...');
        $this->toolManager->createOrderedToolByToolForAllUsers($tool);
    }

    private function updateHomeTabsAdminTool()
    {
        $this->log('Updating home tabs admin tool...');
        $homeTabAdminTool = $this->toolManager->getAdminToolByName('home_tabs');
        $desktopAdminTool = $this->toolManager
            ->getAdminToolByName('desktop_and_home');

        if (!is_null($homeTabAdminTool) && is_null($desktopAdminTool)) {
            $homeTabAdminTool->setName('desktop_and_home');
            $homeTabAdminTool->setClass('home');
            $this->toolManager->persistAdminTool($homeTabAdminTool);
        }
    }

    private function deleteDuplicatedOrderedTools()
    {
        $this->log('Deleting duplicated ordered tools...');
        $this->toolManager->deleteDuplicatedOldOrderedTools();
    }
    
    private function updateWorkspaceMaxUsers()
    {
        $this->log('Updating workspace users limit...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $wsRepo = $em->getRepository('ClarolineCoreBundle:Workspace');
        $workspaces = $wsRepo->findAll();
        $i = 0;
        
        foreach ($worspaces as $workspace) {
            $workspace->setMaxUsers(10000);
            $em->persist($workspace);
            
            if ($i % 200 === 0) {
                $i = 0;
                $em->flush();
            }
            
            $i++;
        }
    }
}
