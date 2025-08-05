<?php

declare(strict_types=1);

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillPlan;

class CharacterController extends Controller
{
    public function skillCheck(CharacterInfo $character): Factory|View
    {
        // Order by priority (1 = highest priority, 2 = second highest, etc.) then by name
        $skillplans = SkillPlan::with('requirements')->orderBy('priority')->orderBy('name')->get();

        $skillCheckResults = [];
        foreach ($skillplans as $skillplan) {
            $skillCheckResults[$skillplan->id] = $skillplan->checkCharacterSkills($character->character_id);
        }

        return view('skillchecker::character.skillcheck', compact('character', 'skillplans', 'skillCheckResults'));
    }
}