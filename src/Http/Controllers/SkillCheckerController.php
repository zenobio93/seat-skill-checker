<?php

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillList;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Models\Squads\Squad;
use Seat\Web\Models\User;

class SkillCheckerController extends Controller
{
    /**
     * Display the skill checker main page.
     *
     * @return View
     */
    public function index(): View
    {
        $skillLists = SkillList::with('requirements')->orderBy('name')->get();
        $squads = Squad::orderBy('name')->get();
        $corporations = CorporationInfo::orderBy('name')->get();
        $users = User::has('characters')->with('main_character')->orderBy('name')->get();

        return view('skillchecker::checker.index', compact('skillLists', 'squads', 'corporations', 'users'));
    }

    /**
     * Check all characters for a user against a skill list.
     * Results are grouped by main character for better organization.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkCharacter(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'skill_list_id' => 'required|integer|exists:skill_lists,id',
        ]);

        $skillList = SkillList::findOrFail($request->skill_list_id);
        $user = User::with('characters', 'main_character')->findOrFail($request->user_id);
        
        $groupedResults = [];
        $allResults = [];
        
        $mainCharacter = $user->main_character;
        
        if (!$mainCharacter) {
            return response()->json([
                'error' => 'User has no main character set',
            ], 400);
        }
        
        $mainCharacterId = $mainCharacter->character_id;
        $mainCharacterName = $mainCharacter->name;
        
        // Initialize group
        $groupedResults[$mainCharacterId] = [
            'main_character' => [
                'id' => $mainCharacterId,
                'name' => $mainCharacterName,
            ],
            'characters' => [],
        ];
        
        // Check all characters for this user
        foreach ($user->characters as $character) {
            $characterResults = $skillList->checkCharacterSkills($character->character_id);
            $characterData = [
                'character' => [
                    'id' => $character->character_id,
                    'name' => $character->name,
                ],
                'results' => $characterResults,
            ];
            
            $groupedResults[$mainCharacterId]['characters'][] = $characterData;
            $allResults[] = $characterData; // For summary calculation
        }
        
        return response()->json([
            'results_meta' => [
                'type' => 'user',
                'id' => $user->id,
                'name' => $user->name,
            ],
            'skill_list' => [
                'id' => $skillList->id,
                'name' => $skillList->name,
            ],
            'grouped_characters' => array_values($groupedResults),
            'summary' => $this->calculateSummary($allResults),
        ]);
    }

    /**
     * Check multiple characters from a squad against a skill list.
     * This checks all characters assigned to the main character associated to the user.
     * Results are grouped by main character for better organization.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSquad(Request $request): JsonResponse
    {
        $request->validate([
            'squad_id' => 'required|integer|exists:squads,id',
            'skill_list_id' => 'required|integer|exists:skill_lists,id',
        ]);

        $skillList = SkillList::findOrFail($request->skill_list_id);
        $squad = Squad::with('members')->findOrFail($request->squad_id);
        
        $groupedResults = [];
        $allResults = [];
        
        foreach ($squad->members as $member) {
            $userCharacters = $member->all_characters();
            $mainCharacter = $member->main_character;
            
            if (!$mainCharacter) {
                continue; // Skip if no main character
            }
            
            $mainCharacterId = $mainCharacter->character_id;
            $mainCharacterName = $mainCharacter->name;
            
            // Initialize group if not exists
            if (!isset($groupedResults[$mainCharacterId])) {
                $groupedResults[$mainCharacterId] = [
                    'main_character' => [
                        'id' => $mainCharacterId,
                        'name' => $mainCharacterName,
                    ],
                    'characters' => [],
                ];
            }

            foreach ($userCharacters as $character) {
                $characterResults = $skillList->checkCharacterSkills($character->character_id);
                $characterData = [
                    'character' => [
                        'id' => $character->character_id,
                        'name' => $character->name,
                    ],
                    'results' => $characterResults,
                ];
                
                $groupedResults[$mainCharacterId]['characters'][] = $characterData;
                $allResults[] = $characterData; // For summary calculation
            }
        }

        return response()->json([
            'results_meta' => [
                'type' => 'squad',
                'id' => $squad->id,
                'name' => $squad->name,
            ],
            'skill_list' => [
                'id' => $skillList->id,
                'name' => $skillList->name,
            ],
            'grouped_characters' => array_values($groupedResults),
            'summary' => $this->calculateSummary($allResults),
        ]);
    }

    /**
     * Check multiple characters from a corporation against a skill list.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkCorporation(Request $request): JsonResponse
    {
        $request->validate([
            'corporation_id' => 'required|integer|exists:corporation_infos,corporation_id',
            'skill_list_id' => 'required|integer|exists:skill_lists,id',
        ]);

        $skillList = SkillList::findOrFail($request->skill_list_id);
        $corporation = CorporationInfo::findOrFail($request->corporation_id);

        $groupedResults = [];
        $allResults = [];
        
        foreach ($corporation->characters as $character) {
            $user = $character->user;
            $mainCharacter = $user->main_character;

            if (!$user || !$mainCharacter) {
                continue;
            }

            $mainCharacterId = $mainCharacter->character_id;
            $mainCharacterName = $mainCharacter->name;

            // Initialize group if not exists
            if (!isset($groupedResults[$mainCharacterId])) {
                $groupedResults[$mainCharacterId] = [
                    'main_character' => [
                        'id' => $mainCharacterId,
                        'name' => $mainCharacterName,
                    ],
                    'characters' => [],
                ];
            }

            $characterResults = $skillList->checkCharacterSkills($character->character_id);
            $characterData = [
                'character' => [
                    'id' => $character->character_id,
                    'name' => $character->name,
                ],
                'results' => $characterResults,
            ];

            $groupedResults[$mainCharacterId]['characters'][] = $characterData;
            $allResults[] = $characterData; // For summary calculation
        }

        return response()->json([
            'results_meta' => [
                'type' => 'corporation',
                'id' => $corporation->corporation_id,
                'name' => $corporation->name,
            ],
            'skill_list' => [
                'id' => $skillList->id,
                'name' => $skillList->name,
            ],
            'grouped_characters' => array_values($groupedResults),
            'summary' => $this->calculateSummary($allResults),
        ]);
    }

    /**
     * Calculate summary statistics for multiple character results.
     *
     * @param array $results
     * @return array
     */
    private function calculateSummary(array $results): array
    {
        if (empty($results)) {
            return [
                'total_characters' => 0,
                'characters_meeting_all' => 0,
                'average_completion' => 0,
            ];
        }

        $totalCharacters = count($results);
        $charactersMeetingAll = 0;
        $totalCompletion = 0;

        foreach ($results as $result) {
            if ($result['results']['all_met']) {
                $charactersMeetingAll++;
            }
            $totalCompletion += $result['results']['percentage'];
        }

        return [
            'total_characters' => $totalCharacters,
            'characters_meeting_all' => $charactersMeetingAll,
            'percentage_meeting_all' => $totalCharacters > 0 ? round(($charactersMeetingAll / $totalCharacters) * 100, 2) : 0,
            'average_completion' => $totalCharacters > 0 ? round($totalCompletion / $totalCharacters, 2) : 0,
        ];
    }
}