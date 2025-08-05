<?php

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillPlan;
use Zenobio93\Seat\SkillChecker\Models\SkillPlanRequirement;
use Zenobio93\Seat\SkillChecker\Http\DataTables\SkillPlanDataTable;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\InvGroup;

class SkillPlanController extends Controller
{
    /**
     * Display a listing of skill plans.
     *
     * @param SkillPlanDataTable $dataTable
     * @return mixed
     */
    public function index(SkillPlanDataTable $dataTable)
    {
        return $dataTable->render('skillchecker::skill-plans.index');
    }

    /**
     * Show the form for creating a new skill plan.
     *
     * @return View
     */
    public function create(): View
    {
        // Get all skill groups for the skill selector
        $skillGroups = InvGroup::whereIn('categoryID', [16]) // Skills category
            ->with(['types' => function ($query) {
                $query->where('published', 1)->orderBy('typeName');
            }])
            ->orderBy('groupName')
            ->get();

        // Check if we have copy data from session
        $copyData = session('copy_data', null);

        return view('skillchecker::skill-plans.create', compact('skillGroups', 'copyData'));
    }

    /**
     * Store a newly created skill plan.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:1',
            'is_required' => 'boolean',
            'skills' => 'required|array|min:1',
            'skills.*.skill_id' => 'required|integer|exists:invTypes,typeID',
            'skills.*.required_level' => 'required|integer|min:1|max:5',
            'skills.*.priority' => 'required|integer|min:1',
            'skills.*.is_required' => 'required|boolean',
        ]);

        // Check for duplicate skills in the request
        $skillIds = collect($request->skills)->pluck('skill_id');
        if ($skillIds->count() !== $skillIds->unique()->count()) {
            return redirect()->back()
                ->withErrors(['skills' => 'Each skill can only be added once per skill plan.'])
                ->withInput();
        }

        $skillPlan = SkillPlan::create([
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_required' => $request->boolean('is_required'),
            'created_by' => auth()->user()->id,
        ]);

        foreach ($request->skills as $skill) {
            SkillPlanRequirement::create([
                'skill_plan_id' => $skillPlan->id,
                'skill_id' => $skill['skill_id'],
                'required_level' => $skill['required_level'],
                'priority' => $skill['priority'],
                'is_required' => $skill['is_required'],
            ]);
        }

        return redirect()->route('skillchecker.skill-plans.index')
            ->with('success', trans('skillchecker::skillchecker.skill_plan_created_successfully'));
    }

    /**
     * Display the specified skill list.
     *
     * @param SkillPlan $skillplan
     * @return View
     */
    public function show(SkillPlan $skillplan): View
    {
        $skillplan->load(['creator', 'requirements.skill']);

        return view('skillchecker::skill-plans.show', compact('skillplan'));
    }

    /**
     * Copy an existing skill plan to create a new one.
     *
     * @param SkillPlan $skillplan
     * @return RedirectResponse
     */
    public function copy(SkillPlan $skillplan): RedirectResponse
    {
        $skillplan->load('requirements.skill');

        // Prepare the skill plan data for copying
        $copyData = [
            'name' => 'Copy of ' . $skillplan->name,
            'description' => $skillplan->description,
            'priority' => $skillplan->priority,
            'is_required' => $skillplan->is_required,
            'skills' => $skillplan->requirements->map(function ($requirement) {
                return [
                    'skill_id' => $requirement->skill_id,
                    'required_level' => $requirement->required_level,
                    'priority' => $requirement->priority,
                    'is_required' => $requirement->is_required,
                ];
            })->toArray(),
        ];

        // Redirect to create form with prefilled data
        return redirect()->route('skillchecker.skill-plans.create')
            ->with('copy_data', $copyData);
    }

    /**
     * Show the form for editing the specified skill list.
     *
     * @param SkillPlan $skillplan
     * @return View
     */
    public function edit(SkillPlan $skillplan): View
    {
        $skillplan->load('requirements.skill');
        
        // Get all skill groups for the skill selector
        $skillGroups = InvGroup::whereIn('categoryID', [16]) // Skills category
            ->with(['types' => function ($query) {
                $query->where('published', 1)->orderBy('typeName');
            }])
            ->orderBy('groupName')
            ->get();

        return view('skillchecker::skill-plans.edit', compact('skillplan', 'skillGroups'));
    }

    /**
     * Update the specified skill list.
     *
     * @param Request $request
     * @param SkillPlan $skillplan
     * @return RedirectResponse
     */
    public function update(Request $request, SkillPlan $skillplan): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:1',
            'is_required' => 'boolean',
            'skills' => 'required|array|min:1',
            'skills.*.skill_id' => 'required|integer|exists:invTypes,typeID',
            'skills.*.required_level' => 'required|integer|min:1|max:5',
            'skills.*.priority' => 'required|integer|min:1',
            'skills.*.is_required' => 'required|boolean',
        ]);

        // Check for duplicate skills in the request
        $skillIds = collect($request->skills)->pluck('skill_id');
        if ($skillIds->count() !== $skillIds->unique()->count()) {
            return redirect()->back()
                ->withErrors(['skills' => 'Each skill can only be added once per skill list.'])
                ->withInput();
        }

        $skillplan->update([
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_required' => $request->boolean('is_required'),
        ]);

        // Remove existing requirements and add new ones
        $skillplan->requirements()->delete();

        foreach ($request->skills as $skill) {
            SkillPlanRequirement::create([
                'skill_plan_id' => $skillplan->id,
                'skill_id' => $skill['skill_id'],
                'required_level' => $skill['required_level'],
                'priority' => $skill['priority'],
                'is_required' => $skill['is_required'],
            ]);
        }

        return redirect()->route('skillchecker.skill-plans.index')
            ->with('success', trans('skillchecker::skillchecker.skill_plan_updated_successfully'));
    }

    /**
     * Remove the specified skill list.
     *
     * @param SkillPlan $skillplan
     * @return RedirectResponse
     */
    public function destroy(SkillPlan $skillplan): RedirectResponse
    {
        $skillplan->delete();

        return redirect()->route('skillchecker.skill-plans.index')
            ->with('success', trans('skillchecker::skillchecker.skill_plan_deleted_successfully'));
    }

    /**
     * Show the import form for skill plans.
     *
     * @return View
     */
    public function import(): View
    {
        return view('skillchecker::skill-plans.import');
    }

    /**
     * Process the imported skill plan data.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:1',
            'is_required' => 'boolean',
            'import_data' => 'required|string',
        ]);

        try {
            // Automatically detect format
            $detectedFormat = $this->detectFormat($request->import_data);
            
            if ($detectedFormat === 'json') {
                $skills = $this->parseJsonFormat($request->import_data);
            } elseif ($detectedFormat === 'localized') {
                $skills = $this->parseLocalizedFormat($request->import_data);
            } else {
                $skills = $this->parseLineByLineFormat($request->import_data);
            }

            if (empty($skills)) {
                return redirect()->back()
                    ->withErrors(['import_data' => 'No valid skills found in the import data.'])
                    ->withInput();
            }

            // Create the skill plan
            $skillPlan = SkillPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'priority' => $request->priority,
                'is_required' => $request->boolean('is_required'),
                'created_by' => auth()->user()->id,
            ]);

            // Add skill requirements
            $priority = 1;
            foreach ($skills as $skillName => $level) {
                $skill = InvType::where('typeName', $skillName)
                    ->whereIn('groupID', function($query) {
                        $query->select('groupID')
                            ->from('invGroups')
                            ->where('categoryID', 16); // Skills category
                    })
                    ->first();

                if ($skill) {
                    SkillPlanRequirement::create([
                        'skill_plan_id' => $skillPlan->id,
                        'skill_id' => $skill->typeID,
                        'required_level' => $level,
                        'priority' => $priority++,
                        'is_required' => true,
                    ]);
                }
            }

            return redirect()->route('skillchecker.skill-plans.index')
                ->with('success', 'Skill plan imported successfully with ' . count($skills) . ' skills.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['import_data' => 'Error processing import data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Parse line-by-line format (e.g., "Science 1", "Science 2", etc.)
     *
     * @param string $data
     * @return array
     */
    private function parseLineByLineFormat(string $data): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $data)));
        $skills = [];

        foreach ($lines as $line) {
            // Match pattern like "Science 1" or "Power Grid Management 3"
            if (preg_match('/^(.+?)\s+(\d+)$/', $line, $matches)) {
                $skillName = trim($matches[1]);
                $level = (int) $matches[2];
                
                if ($level >= 1 && $level <= 5) {
                    // Always use the highest level for each skill
                    if (!isset($skills[$skillName]) || $skills[$skillName] < $level) {
                        $skills[$skillName] = $level;
                    }
                }
            }
        }

        return $skills;
    }

    /**
     * Parse JSON format
     *
     * @param string $data
     * @return array
     */
    private function parseJsonFormat(string $data): array
    {
        $jsonData = json_decode($data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        $skills = [];
        
        foreach ($jsonData as $skillName => $level) {
            $level = (int) $level;
            if ($level >= 1 && $level <= 5) {
                // Always use the highest level for each skill
                if (!isset($skills[$skillName]) || $skills[$skillName] < $level) {
                    $skills[$skillName] = $level;
                }
            }
        }

        return $skills;
    }

    /**
     * Parse localized XML format (e.g., '<localized hint="Gunnery">Gunnery</localized> 1')
     *
     * @param string $data
     * @return array
     */
    private function parseLocalizedFormat(string $data): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $data)));
        $skills = [];

        foreach ($lines as $line) {
            // Match pattern like '<localized hint="Gunnery">Gunnery</localized> 1'
            // or '<localized hint="Gunnery">Gunnery*</localized> 5'
            if (preg_match('/<localized hint="([^"]+)">([^<]*\*?)<\/localized>\s+(\d+)/', $line, $matches)) {
                $skillName = trim($matches[1]); // Use hint attribute as skill name
                $level = (int) $matches[3];
                
                if ($level >= 1 && $level <= 5) {
                    // Always use the highest level for each skill
                    if (!isset($skills[$skillName]) || $skills[$skillName] < $level) {
                        $skills[$skillName] = $level;
                    }
                }
            }
        }

        return $skills;
    }

    /**
     * Show the import form for updating an existing skill plan.
     *
     * @param SkillPlan $skillplan
     * @return View
     */
    public function importUpdate(SkillPlan $skillplan): View
    {
        return view('skillchecker::skill-plans.import-update', compact('skillplan'));
    }

    /**
     * Process the imported skill plan data for updating an existing skill plan.
     *
     * @param Request $request
     * @param SkillPlan $skillplan
     * @return RedirectResponse
     */
    public function processImportUpdate(Request $request, SkillPlan $skillplan): RedirectResponse
    {
        $request->validate([
            'import_data' => 'required|string',
            'update_mode' => 'required|in:replace,merge',
        ]);

        try {
            // Automatically detect format
            $detectedFormat = $this->detectFormat($request->import_data);
            
            if ($detectedFormat === 'json') {
                $skills = $this->parseJsonFormat($request->import_data);
            } elseif ($detectedFormat === 'localized') {
                $skills = $this->parseLocalizedFormat($request->import_data);
            } else {
                $skills = $this->parseLineByLineFormat($request->import_data);
            }

            if (empty($skills)) {
                return redirect()->back()
                    ->withErrors(['import_data' => 'No valid skills found in the import data.'])
                    ->withInput();
            }

            if ($request->update_mode === 'replace') {
                // Remove existing requirements
                $skillplan->requirements()->delete();
                $priority = 1;
            } else {
                // Merge mode: get existing skills and their highest priority
                $existingSkills = $skillplan->requirements()->with('skill')->get();
                $existingSkillMap = [];
                $maxPriority = 0;
                
                foreach ($existingSkills as $requirement) {
                    $skillName = $requirement->skill->typeName ?? '';
                    $existingSkillMap[$skillName] = [
                        'level' => $requirement->required_level,
                        'priority' => $requirement->priority,
                        'is_required' => $requirement->is_required,
                    ];
                    $maxPriority = max($maxPriority, $requirement->priority);
                }
                
                $priority = $maxPriority + 1;
            }

            // Add/update skill requirements
            foreach ($skills as $skillName => $level) {
                $skill = InvType::where('typeName', $skillName)
                    ->whereIn('groupID', function($query) {
                        $query->select('groupID')
                            ->from('invGroups')
                            ->where('categoryID', 16); // Skills category
                    })
                    ->first();

                if ($skill) {
                    if ($request->update_mode === 'merge' && isset($existingSkillMap[$skillName])) {
                        // Update existing skill if new level is higher
                        if ($level > $existingSkillMap[$skillName]['level']) {
                            SkillPlanRequirement::where('skill_plan_id', $skillplan->id)
                                ->where('skill_id', $skill->typeID)
                                ->update(['required_level' => $level]);
                        }
                    } else {
                        // Add new skill requirement
                        SkillPlanRequirement::create([
                            'skill_plan_id' => $skillplan->id,
                            'skill_id' => $skill->typeID,
                            'required_level' => $level,
                            'priority' => $priority++,
                            'is_required' => true,
                        ]);
                    }
                }
            }

            $mode = $request->update_mode === 'replace' ? 'replaced' : 'merged';
            return redirect()->route('skillchecker.skill-plans.show', $skillplan)
                ->with('success', "Skill plan $mode successfully with " . count($skills) . ' skills.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['import_data' => 'Error processing import data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Automatically detect the format of the import data.
     *
     * @param string $data
     * @return string
     */
    private function detectFormat(string $data): string
    {
        $trimmedData = trim($data);
        
        // Handle empty strings
        if (empty($trimmedData)) {
            return 'line_by_line';
        }
        
        // Check if it's valid JSON
        if (($trimmedData[0] === '{' && substr($trimmedData, -1) === '}') || 
            ($trimmedData[0] === '[' && substr($trimmedData, -1) === ']')) {
            
            json_decode($trimmedData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return 'json';
            }
        }
        
        // Check if it contains localized XML format
        if (strpos($trimmedData, '<localized hint=') !== false) {
            return 'localized';
        }
        
        // Default to line-by-line format
        return 'line_by_line';
    }
}