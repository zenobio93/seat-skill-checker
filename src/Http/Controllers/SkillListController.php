<?php

namespace Zenobio93\Seat\SkillChecker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Seat\Web\Http\Controllers\Controller;
use Zenobio93\Seat\SkillChecker\Models\SkillList;
use Zenobio93\Seat\SkillChecker\Models\SkillListRequirement;
use Zenobio93\Seat\SkillChecker\Http\DataTables\SkillListDataTable;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\InvGroup;

class SkillListController extends Controller
{
    /**
     * Display a listing of skill lists.
     *
     * @param SkillListDataTable $dataTable
     * @return mixed
     */
    public function index(SkillListDataTable $dataTable)
    {
        return $dataTable->render('skillchecker::skill-lists.index');
    }

    /**
     * Show the form for creating a new skill list.
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

        return view('skillchecker::skill-lists.create', compact('skillGroups'));
    }

    /**
     * Store a newly created skill list.
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
                ->withErrors(['skills' => 'Each skill can only be added once per skill list.'])
                ->withInput();
        }

        $skillList = SkillList::create([
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_required' => $request->boolean('is_required'),
            'created_by' => auth()->user()->id,
        ]);

        foreach ($request->skills as $skill) {
            SkillListRequirement::create([
                'skill_list_id' => $skillList->id,
                'skill_id' => $skill['skill_id'],
                'required_level' => $skill['required_level'],
                'priority' => $skill['priority'],
                'is_required' => $skill['is_required'],
            ]);
        }

        return redirect()->route('skillchecker.skill-lists.index')
            ->with('success', trans('skillchecker::skillchecker.skill_list_created_successfully'));
    }

    /**
     * Display the specified skill list.
     *
     * @param SkillList $skillList
     * @return View
     */
    public function show(SkillList $skillList): View
    {
        $skillList->load(['creator', 'requirements.skill']);

        return view('skillchecker::skill-lists.show', compact('skillList'));
    }

    /**
     * Show the form for editing the specified skill list.
     *
     * @param SkillList $skillList
     * @return View
     */
    public function edit(SkillList $skillList): View
    {
        $skillList->load('requirements.skill');
        
        // Get all skill groups for the skill selector
        $skillGroups = InvGroup::whereIn('categoryID', [16]) // Skills category
            ->with(['types' => function ($query) {
                $query->where('published', 1)->orderBy('typeName');
            }])
            ->orderBy('groupName')
            ->get();

        return view('skillchecker::skill-lists.edit', compact('skillList', 'skillGroups'));
    }

    /**
     * Update the specified skill list.
     *
     * @param Request $request
     * @param SkillList $skillList
     * @return RedirectResponse
     */
    public function update(Request $request, SkillList $skillList): RedirectResponse
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

        $skillList->update([
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_required' => $request->boolean('is_required'),
        ]);

        // Remove existing requirements and add new ones
        $skillList->requirements()->delete();

        foreach ($request->skills as $skill) {
            SkillListRequirement::create([
                'skill_list_id' => $skillList->id,
                'skill_id' => $skill['skill_id'],
                'required_level' => $skill['required_level'],
                'priority' => $skill['priority'],
                'is_required' => $skill['is_required'],
            ]);
        }

        return redirect()->route('skillchecker.skill-lists.index')
            ->with('success', trans('skillchecker::skillchecker.skill_list_updated_successfully'));
    }

    /**
     * Remove the specified skill list.
     *
     * @param SkillList $skillList
     * @return RedirectResponse
     */
    public function destroy(SkillList $skillList): RedirectResponse
    {
        $skillList->delete();

        return redirect()->route('skillchecker.skill-lists.index')
            ->with('success', trans('skillchecker::skillchecker.skill_list_deleted_successfully'));
    }
}