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
}