<?php

return [
    'skillchecker' => [
        'name'          => 'Skill Checker',
        'icon'          => 'fas fa-book',
        'route_segment' => 'skillchecker',
        'entries'       => [
            [
                'name'       => 'Skill Plans',
                'label'      => 'skillchecker::sidebar.skill_plans',
                'icon'       => 'fas fa-list',
                'route'      => 'skillchecker.skill-plans.index',
                'permission' => 'skillchecker.manage_skill_plans',
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