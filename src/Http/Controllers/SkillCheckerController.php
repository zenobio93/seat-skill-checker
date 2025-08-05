<?php

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillPlan;
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
        // No need to load data upfront since autocomplete will fetch it dynamically
        return view('skillchecker::checker.index');
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
            'skill_plan_id' => 'required|integer|exists:skill_plans,id',
        ]);

        $skillplan = SkillPlan::findOrFail($request->skill_plan_id);
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
            $characterResults = $skillplan->checkCharacterSkills($character->character_id);
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
            'skill_plan' => [
                'id' => $skillplan->id,
                'name' => $skillplan->name,
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
            'skill_plan_id' => 'required|integer|exists:skill_plans,id',
        ]);

        $skillplan = SkillPlan::findOrFail($request->skill_plan_id);
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
                $characterResults = $skillplan->checkCharacterSkills($character->character_id);
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

        // Sort grouped results by main character name, then sort characters within each group by character name
        $sortedGroupedResults = array_values($groupedResults);
        
        // Sort by main character name
        usort($sortedGroupedResults, function($a, $b) {
            return strcasecmp($a['main_character']['name'], $b['main_character']['name']);
        });
        
        // Sort characters within each group by character name
        foreach ($sortedGroupedResults as &$group) {
            usort($group['characters'], function($a, $b) {
                return strcasecmp($a['character']['name'], $b['character']['name']);
            });
        }

        return response()->json([
            'results_meta' => [
                'type' => 'squad',
                'id' => $squad->id,
                'name' => $squad->name,
            ],
            'skill_plan' => [
                'id' => $skillplan->id,
                'name' => $skillplan->name,
            ],
            'grouped_characters' => $sortedGroupedResults,
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
            'skill_plan_id' => 'required|integer|exists:skill_plans,id',
        ]);

        $skillplan = SkillPlan::findOrFail($request->skill_plan_id);
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

            $characterResults = $skillplan->checkCharacterSkills($character->character_id);
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

        // Sort grouped results by main character name, then sort characters within each group by character name
        $sortedGroupedResults = array_values($groupedResults);
        
        // Sort by main character name
        usort($sortedGroupedResults, function($a, $b) {
            return strcasecmp($a['main_character']['name'], $b['main_character']['name']);
        });
        
        // Sort characters within each group by character name
        foreach ($sortedGroupedResults as &$group) {
            usort($group['characters'], function($a, $b) {
                return strcasecmp($a['character']['name'], $b['character']['name']);
            });
        }

        return response()->json([
            'results_meta' => [
                'type' => 'corporation',
                'id' => $corporation->corporation_id,
                'name' => $corporation->name,
            ],
            'skill_plan' => [
                'id' => $skillplan->id,
                'name' => $skillplan->name,
            ],
            'grouped_characters' => $sortedGroupedResults,
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

    /**
     * Lookup users for autocomplete.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookupUsers(Request $request): JsonResponse
    {
        $users = User::has('characters')
            ->with('main_character')
            ->where('name', 'LIKE', '%'.$request->query('q', '').'%')
            ->take(10)
            ->get()
            ->map(fn($user): array => [
                'id' => $user->id,
                'text' => $user->name,
            ]);

        return response()->json(['results' => $users]);
    }

    /**
     * Lookup squads for autocomplete.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookupSquads(Request $request): JsonResponse
    {
        $squads = Squad::where('name', 'LIKE', '%'.$request->query('q', '').'%')
            ->take(10)
            ->get()
            ->map(fn($squad): array => [
                'id' => $squad->id,
                'text' => $squad->name,
            ]);

        return response()->json(['results' => $squads]);
    }

    /**
     * Lookup corporations for autocomplete.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookupCorporations(Request $request): JsonResponse
    {
        $corporations = CorporationInfo::where('name', 'LIKE', '%'.$request->query('q', '').'%')
            ->take(10)
            ->get()
            ->map(fn($corporation): array => [
                'id' => $corporation->corporation_id,
                'text' => $corporation->name,
            ]);

        return response()->json(['results' => $corporations]);
    }

    /**
     * Lookup skill plans for autocomplete.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookupSkillPlans(Request $request): JsonResponse
    {
        $skillplans = SkillPlan::where('name', 'LIKE', '%'.$request->query('q', '').'%')
            ->take(10)
            ->get()
            ->map(fn($skillplan): array => [
                'id' => $skillplan->id,
                'text' => $skillplan->name,
            ]);

        return response()->json(['results' => $skillplans]);
    }
}