<?php

declare(strict_types=1);

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillList;

class CharacterController extends Controller
{
    public function skillCheck(CharacterInfo $character): Factory|View
    {
        $skillLists = SkillList::with('requirements')->orderBy('name')->get();

        $skillCheckResults = [];
        foreach ($skillLists as $skillList) {
            $skillCheckResults[$skillList->id] = $skillList->checkCharacterSkills($character->character_id);
        }

        return view('skillchecker::character.skillcheck', compact('character', 'skillLists', 'skillCheckResults'));
    }
}