<?php

return [
    'skillchecker' => [
        'name'          => 'Skill Checker',
        'icon'          => 'fas fa-book',
        'route_segment' => 'skillchecker',
        'entries'       => [
            [
                'name'       => 'Skill Lists',
                'label'      => 'skillchecker::sidebar.skill_lists',
                'icon'       => 'fas fa-list',
                'route'      => 'skillchecker.skill-lists.index',
                'permission' => 'skillchecker.manage_skill_lists',
            ],
            [
                'name'       => 'Skill Checker',
                'label'      => 'skillchecker::sidebar.skill_checker',
                'icon'       => 'fas fa-search',
                'route'      => 'skillchecker.checker.index',
                'permission' => 'skillchecker.check_skills',
            ],
        ],
        'permission'    => 'skillchecker.check_skills',
    ],
];